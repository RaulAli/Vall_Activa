<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\User;

use App\Application\User\Command\ChangePasswordCommand;
use App\Application\User\Handler\ChangePasswordHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ChangePasswordController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/user/me/password', name: 'user_me_password', methods: ['PATCH'])]
    public function __invoke(Request $request, ChangePasswordHandler $handler): JsonResponse
    {
        $userId = $this->extractUserId($request);

        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        $currentPassword = isset($data['currentPassword']) ? (string) $data['currentPassword'] : '';
        $newPassword = isset($data['newPassword']) ? (string) $data['newPassword'] : '';

        if ($currentPassword === '' || $newPassword === '') {
            return $this->json(['error' => 'currentPassword and newPassword are required'], 422);
        }

        if (strlen($newPassword) < 8) {
            return $this->json(['error' => 'new_password_too_short'], 422);
        }

        try {
            $handler(new ChangePasswordCommand(
                userId: $userId,
                currentPassword: $currentPassword,
                newPassword: $newPassword,
            ));
        } catch (\DomainException $e) {
            $status = $e->getMessage() === 'invalid_current_password' ? 403 : 422;
            return $this->json(['error' => $e->getMessage()], $status);
        }

        return $this->json(['message' => 'password_changed']);
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
