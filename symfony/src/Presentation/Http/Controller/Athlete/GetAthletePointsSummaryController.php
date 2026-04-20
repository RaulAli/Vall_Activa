<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Athlete;

use App\Application\Points\PointsEngine;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetAthletePointsSummaryController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/athlete/me/points/summary', name: 'athlete_points_summary_get', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em, PointsEngine $pointsEngine): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $user = $em->find(UserOrm::class, $userId);
        if (!$user instanceof UserOrm || !$user->isActive || $user->role !== 'ROLE_ATHLETE') {
            return $this->json(['error' => 'forbidden_role'], 403);
        }

        $settings = $pointsEngine->getSettings();
        $today = $pointsEngine->getTodayEarned($user->id);
        $cap = $pointsEngine->getDailyCap($user);

        return $this->json([
            'balance' => $user->pointsBalance,
            'todayEarned' => $today,
            'dailyCap' => $cap,
            'remainingToday' => max(0, $cap - $today),
            'pointsPerKm' => $settings->pointsPerKm,
            'vipMultiplier' => $pointsEngine->getMultiplier($user),
            'isVipForPoints' => $pointsEngine->isVipForPoints($user),
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
