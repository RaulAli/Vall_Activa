<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Profile;

use App\Application\Profile\Port\FollowRepositoryInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetMyFollowersController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/me/followers', name: 'me_followers', methods: ['GET'])]
    public function __invoke(Request $request, FollowRepositoryInterface $follows): JsonResponse
    {
        $userId = $this->extractUserId($request);

        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $list = $follows->listFollowers($userId);

        return $this->json($list);
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
