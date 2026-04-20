<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Guide;

use App\Application\Points\AutoMissionService;
use App\Application\Points\PointsEngine;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\GuideRouteBookingOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateMyGuideBookingStatusController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly PointsEngine $pointsEngine,
        private readonly AutoMissionService $autoMissionService,
    ) {
    }

    #[Route('/api/guide/me/bookings/{id}', name: 'guide_me_booking_status_update', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $guideUserId = $this->extractUserId($request);
        if ($guideUserId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $guide = $em->find(GuideProfileOrm::class, $guideUserId);
        if ($guide === null || !$guide->isActive) {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $booking = $em->find(GuideRouteBookingOrm::class, $id);
        if ($booking === null || $booking->guideUserId !== $guideUserId) {
            return $this->json(['error' => 'booking_not_found'], 404);
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $status = isset($payload['status']) && is_string($payload['status'])
            ? strtoupper(trim($payload['status']))
            : '';

        if (!in_array($status, ['CONFIRMED', 'REJECTED', 'COMPLETED'], true)) {
            return $this->json(['error' => 'invalid_status'], 422);
        }

        if (!in_array($booking->status, ['REQUESTED', 'CONFIRMED', 'REJECTED', 'COMPLETED'], true)) {
            return $this->json(['error' => 'invalid_transition'], 409);
        }

        if ($booking->status === $status) {
            return $this->json([
                'id' => $booking->id,
                'status' => $booking->status,
            ]);
        }

        if ($status === 'CONFIRMED') {
            if ($booking->status !== 'REQUESTED') {
                return $this->json(['error' => 'invalid_transition'], 409);
            }

            $alreadyConfirmed = $em->createQueryBuilder()
                ->select('b2.id')
                ->from(GuideRouteBookingOrm::class, 'b2')
                ->andWhere('b2.guideUserId = :guideUserId')
                ->andWhere('b2.scheduledFor < :endsAt')
                ->andWhere('b2.endsAt > :startsAt')
                ->andWhere('b2.status = :statusConfirmed')
                ->andWhere('b2.id != :bookingId')
                ->setParameter('guideUserId', $guideUserId)
                ->setParameter('startsAt', $booking->scheduledFor)
                ->setParameter('endsAt', $booking->endsAt)
                ->setParameter('statusConfirmed', 'CONFIRMED')
                ->setParameter('bookingId', $booking->id)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($alreadyConfirmed !== null) {
                return $this->json(['error' => 'slot_already_booked'], 409);
            }
        } elseif ($status === 'REJECTED') {
            if ($booking->status !== 'REQUESTED') {
                return $this->json(['error' => 'invalid_transition'], 409);
            }
        } elseif ($status === 'COMPLETED') {
            if ($booking->status !== 'CONFIRMED') {
                return $this->json(['error' => 'invalid_transition'], 409);
            }

            if ($booking->paymentStatus !== 'PAID') {
                return $this->json(['error' => 'booking_payment_required'], 409);
            }
        }

        $booking->status = $status;
        $booking->updatedAt = new \DateTimeImmutable();

        $pointsPayload = null;
        if ($status === 'COMPLETED') {
            $athlete = $em->find(UserOrm::class, $booking->athleteUserId);
            $route = $em->find(RouteOrm::class, $booking->routeId);

            if ($athlete instanceof UserOrm && $athlete->role === 'ROLE_ATHLETE' && $route instanceof RouteOrm) {
                $settings = $this->pointsEngine->getSettings();
                $distanceKm = max(0.0, round($route->distanceM / 1000, 2));
                $basePoints = max(0, (int) round($distanceKm * $settings->pointsPerKm));
                $result = $this->pointsEngine->award(
                    user: $athlete,
                    basePoints: $basePoints,
                    sourceType: 'ROUTE_KM',
                    sourceRef: $booking->id,
                );

                $pointsPayload = [
                    'awarded' => $result['awarded'],
                    'requested' => $result['requested'],
                    'today' => $result['today'],
                    'cap' => $result['cap'],
                    'multiplier' => $result['multiplier'],
                    'balance' => $athlete->pointsBalance,
                    'distanceKm' => $distanceKm,
                    'pointsPerKm' => $settings->pointsPerKm,
                    'alreadyAwarded' => $result['alreadyAwarded'],
                ];

                // Auto-mission: complete 10km in one day (accumulated)
                $this->autoMissionService->syncDaily10Km($athlete);
            }
        }

        $em->flush();

        return $this->json([
            'id' => $booking->id,
            'status' => $booking->status,
            'points' => $pointsPayload,
        ]);
    }

    private function extractUserId(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        try {
            $payload = $this->jwtManager->parse(substr($authHeader, 7));
            return isset($payload['userId']) ? (string) $payload['userId'] : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
