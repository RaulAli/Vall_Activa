<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Auth;

use App\Application\Auth\Command\RevokeAllDevicesCommand;
use App\Application\Auth\Handler\RevokeAllDevicesHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RevokeAllDevicesController extends AbstractController
{
    private const COOKIE_NAME = 'refresh_token';

    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * Requires a valid JWT in Authorization: Bearer <token>.
     * Revokes ALL sessions for the authenticated user and increments
     * session_version so any still-valid access tokens are rejected immediately.
     */
    #[Route('/api/auth/revoke-devices', name: 'auth_revoke_devices', methods: ['POST'])]
    public function __invoke(
        Request $request,
        RevokeAllDevicesHandler $handler,
    ): JsonResponse {
        $authHeader = $request->headers->get('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return $this->json(['error' => 'missing_token'], 401);
        }

        try {
            $payload = $this->jwtManager->parse(substr($authHeader, 7));
            $userId = $payload['userId'] ?? null;
        } catch (\Throwable) {
            return $this->json(['error' => 'invalid_token'], 401);
        }

        if ($userId === null) {
            return $this->json(['error' => 'invalid_token'], 401);
        }

        $handler(new RevokeAllDevicesCommand(userId: (string) $userId));

        $response = $this->json(['message' => 'all_devices_revoked']);
        $response->headers->clearCookie(self::COOKIE_NAME, '/api/auth');

        return $response;
    }
}
