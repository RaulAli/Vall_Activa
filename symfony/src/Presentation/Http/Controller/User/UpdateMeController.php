<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\User;

use App\Application\User\Command\UpdateMeCommand;
use App\Application\User\Handler\UpdateMeHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateMeController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/user/me', name: 'user_me_update', methods: ['PATCH'])]
    public function __invoke(Request $request, UpdateMeHandler $handler): JsonResponse
    {
        $userId = $this->extractUserId($request);

        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $handler(new UpdateMeCommand(
                userId: $userId,
                data: $data,
            ));
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }

        return $this->json(['message' => 'profile_updated']);
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
