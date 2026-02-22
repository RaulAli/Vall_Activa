<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Profile;

use App\Application\Profile\Handler\GetPublicProfileHandler;
use App\Application\Profile\Query\GetPublicProfileQuery;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetPublicProfileController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/profile/{slug}', name: 'profile_get', methods: ['GET'])]
    public function __invoke(string $slug, Request $request, GetPublicProfileHandler $handler): JsonResponse
    {
        $requestingUserId = $this->extractUserIdOptional($request);

        try {
            $profile = $handler(new GetPublicProfileQuery(
                slug: $slug,
                requestingUserId: $requestingUserId,
            ));
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }

        $base = [
            'slug' => $profile->slug,
            'name' => $profile->name,
            'avatar' => $profile->avatar,
            'role' => $profile->role,
            'followersCount' => $profile->followersCount,
            'isFollowedByMe' => $profile->isFollowedByMe,
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

    private function extractUserIdOptional(Request $request): ?string
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
