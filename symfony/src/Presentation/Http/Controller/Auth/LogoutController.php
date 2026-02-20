<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Auth;

use App\Application\Auth\Command\LogoutCommand;
use App\Application\Auth\Handler\LogoutHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class LogoutController extends AbstractController
{
    private const COOKIE_NAME = 'refresh_token';

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {}

    #[Route('/api/auth/logout', name: 'auth_logout', methods: ['POST'])]
    public function __invoke(
        Request $request,
        LogoutHandler $handler,
    ): JsonResponse {
        $rawToken = $request->cookies->get(self::COOKIE_NAME);

        // Extract userId from the access token in the Authorization header
        $userId = null;
        $authHeader = $request->headers->get('Authorization', '');
        if (str_starts_with($authHeader, 'Bearer ')) {
            try {
                $token   = substr($authHeader, 7);
                $payload = $this->jwtManager->parse($token);
                $userId  = $payload['userId'] ?? null;
            } catch (\Throwable) {
                // ignore — still proceed to clear cookie
            }
        }

        if ($rawToken !== null && $rawToken !== '' && $userId !== null) {
            $handler(new LogoutCommand(rawRefreshToken: $rawToken, userId: $userId));
        }

        $response = $this->json(['message' => 'logged_out']);
        $response->headers->clearCookie(self::COOKIE_NAME, '/api/auth');

        return $response;
    }
}
