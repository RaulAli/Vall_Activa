<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Profile;

use App\Application\Profile\Command\FollowProfileCommand;
use App\Application\Profile\Handler\FollowProfileHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class FollowProfileController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/profile/{slug}/follow', name: 'profile_follow', methods: ['POST'])]
    public function __invoke(string $slug, Request $request, FollowProfileHandler $handler): JsonResponse
    {
        $followerId = $this->extractUserId($request);

        if ($followerId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        try {
            $handler(new FollowProfileCommand(
                followerId: $followerId,
                slug: $slug,
            ));
        } catch (\DomainException $e) {
            $code = match ($e->getMessage()) {
                'profile_not_found' => 404,
                'cannot_follow_yourself' => 422,
                default => 400,
            };
            return $this->json(['error' => $e->getMessage()], $code);
        }

        return $this->json(null, 201);
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
