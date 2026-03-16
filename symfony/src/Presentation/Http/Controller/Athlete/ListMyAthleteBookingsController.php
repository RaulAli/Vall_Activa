<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Athlete;

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

final class ListMyAthleteBookingsController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/bookings', name: 'athlete_me_bookings_list', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $athleteUserId = $this->extractUserId($request);
        if ($athleteUserId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $user = $em->find(UserOrm::class, $athleteUserId);
        if ($user === null || !$user->isActive || $user->role !== 'ROLE_ATHLETE') {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $rows = $em->createQueryBuilder()
            ->select('b.id, b.status, b.paymentStatus, b.paymentAmountCents, b.paymentCurrency, b.paidAt, b.notes, b.scheduledFor, b.endsAt, b.createdAt, r.id AS routeId, r.slug AS routeSlug, r.title AS routeTitle, g.userId AS guideUserId, g.name AS guideName, g.slug AS guideSlug, g.avatar AS guideAvatar, g.isVerified AS guideIsVerified')
            ->from(GuideRouteBookingOrm::class, 'b')
            ->leftJoin(RouteOrm::class, 'r', 'WITH', 'r.id = b.routeId')
            ->leftJoin(GuideProfileOrm::class, 'g', 'WITH', 'g.userId = b.guideUserId')
            ->andWhere('b.athleteUserId = :athleteUserId')
            ->setParameter('athleteUserId', $athleteUserId)
            ->orderBy('b.scheduledFor', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $items = array_map(static function (array $row): array {
            $scheduledFor = $row['scheduledFor'] instanceof \DateTimeInterface
                ? $row['scheduledFor']->format(DATE_ATOM)
                : (string) $row['scheduledFor'];
            $createdAt = $row['createdAt'] instanceof \DateTimeInterface
                ? $row['createdAt']->format(DATE_ATOM)
                : (string) $row['createdAt'];
            $paidAt = $row['paidAt'] instanceof \DateTimeInterface
                ? $row['paidAt']->format(DATE_ATOM)
                : null;
            $endsAt = $row['endsAt'] instanceof \DateTimeInterface
                ? $row['endsAt']->format(DATE_ATOM)
                : null;

            return [
                'id' => (string) $row['id'],
                'status' => (string) $row['status'],
                'paymentStatus' => isset($row['paymentStatus']) ? (string) $row['paymentStatus'] : 'UNPAID',
                'endsAt' => $endsAt,
                'paymentAmountCents' => isset($row['paymentAmountCents']) ? (int) $row['paymentAmountCents'] : 0,
                'paymentCurrency' => isset($row['paymentCurrency']) ? (string) $row['paymentCurrency'] : 'EUR',
                'paidAt' => $paidAt,
                'notes' => isset($row['notes']) ? (string) $row['notes'] : null,
                'scheduledFor' => $scheduledFor,
                'createdAt' => $createdAt,
                'routeId' => isset($row['routeId']) ? (string) $row['routeId'] : null,
                'routeSlug' => isset($row['routeSlug']) ? (string) $row['routeSlug'] : null,
                'routeTitle' => isset($row['routeTitle']) ? (string) $row['routeTitle'] : null,
                'guideUserId' => isset($row['guideUserId']) ? (string) $row['guideUserId'] : null,
                'guideName' => isset($row['guideName']) ? (string) $row['guideName'] : null,
                'guideSlug' => isset($row['guideSlug']) ? (string) $row['guideSlug'] : null,
                'guideAvatar' => isset($row['guideAvatar']) ? (string) $row['guideAvatar'] : null,
                'guideIsVerified' => isset($row['guideIsVerified']) ? (bool) $row['guideIsVerified'] : null,
            ];
        }, $rows);

        return $this->json($items);
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
