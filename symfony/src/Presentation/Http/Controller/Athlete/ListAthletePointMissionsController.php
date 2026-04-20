<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Athlete;

use App\Application\Points\AutoMissionService;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionCompletionOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListAthletePointMissionsController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/points/missions', name: 'athlete_point_missions_list', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em, AutoMissionService $autoMissionService): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $user = $em->find(UserOrm::class, $userId);
        if (!$user instanceof UserOrm || !$user->isActive || $user->role !== 'ROLE_ATHLETE') {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $today = new \DateTimeImmutable('today');

        $dailyKm = $autoMissionService->syncDaily10Km($user);
        $firstUpload = $autoMissionService->syncFirstRouteUploadDaily($user);
        $em->flush();

        /** @var PointMissionOrm[] $missions */
        $missions = $em->createQueryBuilder()
            ->select('m')
            ->from(PointMissionOrm::class, 'm')
            ->andWhere('m.isActive = true')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $completedMissionIds = $em->createQueryBuilder()
            ->select('c.missionId AS missionId')
            ->from(PointMissionCompletionOrm::class, 'c')
            ->andWhere('c.userId = :userId')
            ->andWhere('c.dayKey = :today')
            ->setParameter('userId', $user->id)
            ->setParameter('today', $today)
            ->getQuery()
            ->getScalarResult();

        $completedMap = [];
        foreach ($completedMissionIds as $row) {
            if (is_array($row)) {
                $value = $row['missionId'] ?? null;
                if (is_string($value) && $value !== '') {
                    $completedMap[$value] = true;
                }
            }
        }

        $payload = array_map(static function (PointMissionOrm $m) use ($completedMap, $dailyKm, $firstUpload): array {
            $isAuto = in_array($m->code, [
                AutoMissionService::CODE_DAILY_10KM,
                AutoMissionService::CODE_FIRST_ROUTE_UPLOAD_DAILY,
            ], true);

            $progress = null;
            if ($m->code === AutoMissionService::CODE_DAILY_10KM) {
                $progress = [
                    'current' => $dailyKm['currentKm'],
                    'target' => $dailyKm['targetKm'],
                    'unit' => 'KM',
                ];
            }

            if ($m->code === AutoMissionService::CODE_FIRST_ROUTE_UPLOAD_DAILY) {
                $progress = [
                    'current' => $firstUpload['currentCount'],
                    'target' => $firstUpload['targetCount'],
                    'unit' => 'COUNT',
                ];
            }

            return [
                'id' => $m->id,
                'code' => $m->code,
                'title' => $m->title,
                'description' => $m->description,
                'pointsReward' => $m->pointsReward,
                'completedToday' => isset($completedMap[$m->id]),
                'auto' => $isAuto,
                'progress' => $progress,
            ];
        }, $missions);

        return $this->json($payload);
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
