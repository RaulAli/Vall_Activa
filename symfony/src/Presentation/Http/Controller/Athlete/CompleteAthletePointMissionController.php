<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Athlete;

use App\Application\Points\AutoMissionService;
use App\Application\Points\PointsEngine;
use App\Domain\Shared\ValueObject\Uuid;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionCompletionOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CompleteAthletePointMissionController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/points/missions/{id}/complete', name: 'athlete_point_missions_complete', methods: ['POST'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em, PointsEngine $pointsEngine): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $user = $em->find(UserOrm::class, $userId);
        if (!$user instanceof UserOrm || !$user->isActive || $user->role !== 'ROLE_ATHLETE') {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $mission = $em->find(PointMissionOrm::class, $id);
        if (!$mission instanceof PointMissionOrm || !$mission->isActive) {
            return $this->json(['error' => 'mission_not_found'], 404);
        }

        if (
            in_array($mission->code, [
                AutoMissionService::CODE_DAILY_10KM,
                AutoMissionService::CODE_FIRST_ROUTE_UPLOAD_DAILY,
            ], true)
        ) {
            return $this->json(['error' => 'mission_is_automatic'], 409);
        }

        $today = new \DateTimeImmutable('today');

        $already = $em->getRepository(PointMissionCompletionOrm::class)->findOneBy([
            'userId' => $user->id,
            'missionId' => $mission->id,
            'dayKey' => $today,
        ]);

        if ($already instanceof PointMissionCompletionOrm) {
            return $this->json(['error' => 'mission_already_completed_today'], 409);
        }

        $result = $pointsEngine->award(
            user: $user,
            basePoints: $mission->pointsReward,
            sourceType: 'MISSION',
            sourceRef: $mission->id . ':' . $today->format('Y-m-d'),
        );

        $completion = new PointMissionCompletionOrm();
        $completion->id = Uuid::v4()->value();
        $completion->userId = $user->id;
        $completion->missionId = $mission->id;
        $completion->dayKey = $today;
        $completion->awardedPoints = $result['awarded'];
        $completion->createdAt = new \DateTimeImmutable();

        $em->persist($completion);
        $em->flush();

        return $this->json([
            'missionId' => $mission->id,
            'awarded' => $result['awarded'],
            'requested' => $result['requested'],
            'today' => $result['today'],
            'cap' => $result['cap'],
            'balance' => $user->pointsBalance,
            'multiplier' => $result['multiplier'],
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
