<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Profile;

use App\Application\Profile\Command\UnfollowProfileCommand;
use App\Application\Profile\Handler\UnfollowProfileHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UnfollowProfileController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/profile/{slug}/follow', name: 'profile_unfollow', methods: ['DELETE'])]
    public function __invoke(string $slug, Request $request, UnfollowProfileHandler $handler): JsonResponse
    {
        $followerId = $this->extractUserId($request);

        if ($followerId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        try {
            $handler(new UnfollowProfileCommand(
                followerId: $followerId,
                slug: $slug,
            ));
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }

        return $this->json(null, 204);
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
