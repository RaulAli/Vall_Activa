<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\User;

use App\Application\User\Handler\GetMeHandler;
use App\Application\User\Query\GetMeQuery;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetMeController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/user/me', name: 'user_me_get', methods: ['GET'])]
    public function __invoke(Request $request, GetMeHandler $handler): JsonResponse
    {
        $userId = $this->extractUserId($request);

        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        try {
            $profile = $handler(new GetMeQuery(userId: $userId));
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }

        $base = [
            'id' => $profile->id,
            'email' => $profile->email,
            'role' => $profile->role,
            'createdAt' => $profile->createdAt,
            'slug' => $profile->slug,
            'name' => $profile->name,
            'avatar' => $profile->avatar,
        ];

        $extra = match ($profile->role) {
            'ROLE_BUSINESS' => [
                'lat' => $profile->lat,
                'lng' => $profile->lng,
            ],
            'ROLE_ATHLETE' => [
                'city' => $profile->city,
                'birthDate' => $profile->birthDate,
            ],
            'ROLE_GUIDE' => [
                'bio' => $profile->bio,
                'city' => $profile->city,
                'lat' => $profile->lat,
                'lng' => $profile->lng,
                'sports' => $profile->sports,
            ],
            default => [],
        };

        return $this->json(array_merge($base, $extra));
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
