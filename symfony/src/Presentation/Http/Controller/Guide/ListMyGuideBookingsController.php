<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Guide;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\AthleteProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\GuideRouteBookingOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListMyGuideBookingsController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/guide/me/bookings', name: 'guide_me_bookings_list', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $guideUserId = $this->extractUserId($request);
        if ($guideUserId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $guide = $em->find(GuideProfileOrm::class, $guideUserId);
        if ($guide === null || !$guide->isActive) {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $rows = $em->createQueryBuilder()
            ->select('b.id, b.status, b.notes, b.scheduledFor, b.createdAt, r.id AS routeId, r.slug AS routeSlug, r.title AS routeTitle, a.userId AS athleteUserId, a.name AS athleteName, a.slug AS athleteSlug, a.avatar AS athleteAvatar')
            ->from(GuideRouteBookingOrm::class, 'b')
            ->leftJoin(RouteOrm::class, 'r', 'WITH', 'r.id = b.routeId')
            ->leftJoin(AthleteProfileOrm::class, 'a', 'WITH', 'a.userId = b.athleteUserId')
            ->andWhere('b.guideUserId = :guideUserId')
            ->setParameter('guideUserId', $guideUserId)
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

            return [
                'id' => (string) $row['id'],
                'status' => (string) $row['status'],
                'notes' => isset($row['notes']) ? (string) $row['notes'] : null,
                'scheduledFor' => $scheduledFor,
                'createdAt' => $createdAt,
                'routeId' => isset($row['routeId']) ? (string) $row['routeId'] : null,
                'routeSlug' => isset($row['routeSlug']) ? (string) $row['routeSlug'] : null,
                'routeTitle' => isset($row['routeTitle']) ? (string) $row['routeTitle'] : null,
                'athleteUserId' => isset($row['athleteUserId']) ? (string) $row['athleteUserId'] : null,
                'athleteName' => isset($row['athleteName']) ? (string) $row['athleteName'] : null,
                'athleteSlug' => isset($row['athleteSlug']) ? (string) $row['athleteSlug'] : null,
                'athleteAvatar' => isset($row['athleteAvatar']) ? (string) $row['athleteAvatar'] : null,
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
