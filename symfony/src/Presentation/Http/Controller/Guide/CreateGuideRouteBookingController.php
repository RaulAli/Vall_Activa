<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Guide;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideAvailabilityOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\GuideRouteBookingOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use App\Domain\Shared\ValueObject\Uuid;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateGuideRouteBookingController extends AbstractController
{
    private const DAY_KEYS = [
        1 => 'MONDAY',
        2 => 'TUESDAY',
        3 => 'WEDNESDAY',
        4 => 'THURSDAY',
        5 => 'FRIDAY',
        6 => 'SATURDAY',
        7 => 'SUNDAY',
    ];

    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/guide/bookings', name: 'guide_booking_create', methods: ['POST'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $athleteUserId = $this->extractUserId($request);
        if ($athleteUserId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $athlete = $em->find(UserOrm::class, $athleteUserId);
        if ($athlete === null || !$athlete->isActive) {
            return $this->json(['error' => 'unauthorized'], 401);
        }
        if ($athlete->role !== 'ROLE_ATHLETE') {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $routeId = isset($payload['routeId']) && is_string($payload['routeId']) ? trim($payload['routeId']) : '';
        $startsAtRaw = isset($payload['startsAt']) && is_string($payload['startsAt']) ? trim($payload['startsAt']) : '';
        $notes = isset($payload['notes']) && is_string($payload['notes']) ? trim($payload['notes']) : null;

        if ($routeId === '' || $startsAtRaw === '') {
            return $this->json(['error' => 'missing_fields'], 400);
        }

        try {
            $startsAtUtc = (new \DateTimeImmutable($startsAtRaw))->setTimezone(new \DateTimeZone('UTC'));
        } catch (\Throwable) {
            return $this->json(['error' => 'invalid_starts_at'], 400);
        }

        if ($startsAtUtc <= new \DateTimeImmutable('now', new \DateTimeZone('UTC'))) {
            return $this->json(['error' => 'slot_in_past'], 409);
        }

        $route = $em->find(RouteOrm::class, $routeId);
        if (
            $route === null
            || !$route->isActive
            || $route->adminDisabled
            || $route->visibility !== 'PUBLIC'
            || $route->status !== 'PUBLISHED'
        ) {
            return $this->json(['error' => 'route_not_bookable'], 404);
        }

        $guide = $em->find(GuideProfileOrm::class, $route->createdByUserId);
        if ($guide === null || !$guide->isActive) {
            return $this->json(['error' => 'route_not_guide'], 409);
        }
        if ($guide->userId === $athleteUserId) {
            return $this->json(['error' => 'cannot_book_self'], 409);
        }

        $availability = $em->find(GuideAvailabilityOrm::class, $guide->userId);
        if ($availability === null) {
            return $this->json(['error' => 'slot_not_available'], 409);
        }

        if (!$this->isSlotAvailableInWeek($availability, $startsAtUtc)) {
            return $this->json(['error' => 'slot_not_available'], 409);
        }

        $activeBooking = $em->createQueryBuilder()
            ->select('b.id')
            ->from(GuideRouteBookingOrm::class, 'b')
            ->andWhere('b.guideUserId = :guideUserId')
            ->andWhere('b.scheduledFor = :scheduledFor')
            ->andWhere('b.status IN (:activeStatuses)')
            ->setParameter('guideUserId', $guide->userId)
            ->setParameter('scheduledFor', $startsAtUtc)
            ->setParameter('activeStatuses', ['REQUESTED', 'CONFIRMED'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        if ($activeBooking !== null) {
            return $this->json(['error' => 'slot_already_booked'], 409);
        }

        $booking = new GuideRouteBookingOrm();
        $booking->id = Uuid::v4()->value();
        $booking->routeId = $route->id;
        $booking->guideUserId = $guide->userId;
        $booking->athleteUserId = $athleteUserId;
        $booking->scheduledFor = $startsAtUtc;
        $booking->status = 'REQUESTED';
        $booking->notes = $notes !== null && $notes !== '' ? mb_substr($notes, 0, 1000) : null;
        $booking->createdAt = new \DateTimeImmutable();
        $booking->updatedAt = $booking->createdAt;

        try {
            $em->persist($booking);
            $em->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['error' => 'slot_already_booked'], 409);
        }

        return $this->json([
            'id' => $booking->id,
            'routeId' => $booking->routeId,
            'guideUserId' => $booking->guideUserId,
            'athleteUserId' => $booking->athleteUserId,
            'startsAt' => $booking->scheduledFor->format(DATE_ATOM),
            'status' => $booking->status,
            'notes' => $booking->notes,
            'createdAt' => $booking->createdAt->format(DATE_ATOM),
        ], 201);
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

    private function isSlotAvailableInWeek(GuideAvailabilityOrm $availability, \DateTimeImmutable $startsAtUtc): bool
    {
        $tz = new \DateTimeZone($availability->timezone ?: 'UTC');
        $local = $startsAtUtc->setTimezone($tz);
        $dayKey = self::DAY_KEYS[(int) $local->format('N')] ?? null;
        if ($dayKey === null) {
            return false;
        }

        $slot = $local->format('H:i');
        foreach ($availability->week as $dayCfg) {
            if (!is_array($dayCfg)) {
                continue;
            }
            if (($dayCfg['day'] ?? null) !== $dayKey) {
                continue;
            }
            if (!(bool) ($dayCfg['enabled'] ?? false)) {
                return false;
            }

            $slots = is_array($dayCfg['slots'] ?? null) ? $dayCfg['slots'] : [];
            foreach ($slots as $s) {
                if (is_string($s) && $s === $slot) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
}
