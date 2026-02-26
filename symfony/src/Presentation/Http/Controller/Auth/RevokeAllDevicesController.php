<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Auth;

use App\Application\Auth\Command\RevokeAllDevicesCommand;
use App\Application\Auth\Handler\RevokeAllDevicesHandler;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class RevokeAllDevicesController extends AbstractController
{
    private const COOKIE_NAME = 'refresh_token';

    #[Route('/api/auth/revoke-devices', name: 'auth_revoke_devices', methods: ['POST'])]
    public function __invoke(
        Request $request,
        RevokeAllDevicesHandler $handler,
    ): JsonResponse {
        /** @var UserOrm $user */
        $user = $this->getUser();

        $handler(new RevokeAllDevicesCommand(userId: $user->id));

        $response = $this->json(['message' => 'all_devices_revoked']);
        $response->headers->clearCookie(self::COOKIE_NAME, '/api/auth');

        return $response;
    }
}
