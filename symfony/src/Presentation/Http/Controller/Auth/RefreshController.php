<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Auth;

use App\Application\Auth\Command\RefreshTokenCommand;
use App\Application\Auth\Handler\RefreshTokenHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RefreshController extends AbstractController
{
    private const COOKIE_NAME = 'refresh_token';

    public function __construct(private readonly bool $cookieSecure)
    {
    }

    #[Route('/api/auth/refresh', name: 'auth_refresh', methods: ['POST'])]
    public function __invoke(
        Request $request,
        RefreshTokenHandler $handler,
    ): JsonResponse {
        $rawToken = $request->cookies->get(self::COOKIE_NAME);

        if ($rawToken === null || $rawToken === '') {
            return $this->json(['error' => 'missing_refresh_token'], 401);
        }

        try {
            $tokens = $handler(new RefreshTokenCommand(rawRefreshToken: $rawToken));
        } catch (\DomainException $e) {
            // Clear the stale cookie
            $response = $this->json(['error' => $e->getMessage()], 401);
            $response->headers->clearCookie(self::COOKIE_NAME, '/api/auth');
            return $response;
        }

        $response = $this->json([
            'accessToken' => $tokens->accessToken,
            'userId' => $tokens->userId,
            'email' => $tokens->email,
        ]);

        $response->headers->setCookie(
            new Cookie(
                name: self::COOKIE_NAME,
                value: $tokens->rawRefreshToken,
                expire: time() + $tokens->refreshTtl,
                path: '/api/auth',
                domain: null,
                secure: $this->cookieSecure,
                httpOnly: true,
                raw: false,
                sameSite: Cookie::SAMESITE_STRICT,
            )
        );

        return $response;
    }
}
