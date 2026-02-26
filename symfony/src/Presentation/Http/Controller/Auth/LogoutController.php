<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Auth;

use App\Application\Auth\Command\LogoutCommand;
use App\Application\Auth\Handler\LogoutHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class LogoutController extends AbstractController
{
    private const COOKIE_NAME = 'refresh_token';

    #[Route('/api/auth/logout', name: 'auth_logout', methods: ['POST'])]
    public function __invoke(
        Request $request,
        LogoutHandler $handler,
    ): JsonResponse {
        $rawToken = $request->cookies->get(self::COOKIE_NAME);

        if ($rawToken !== null && $rawToken !== '') {
            $handler(new LogoutCommand(rawRefreshToken: $rawToken));
        }

        $response = $this->json(['message' => 'logged_out']);
        $response->headers->clearCookie(self::COOKIE_NAME, '/api/auth');

        return $response;
    }
}
