<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Shared\Security\Actor;
use App\Application\Shared\Security\AuthorizationServiceInterface;
use App\Domain\Identity\ValueObject\Role;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class AuthorizationService implements AuthorizationServiceInterface
{
    public function requireRole(Actor $actor, Role $role): void
    {
        if ($actor->has($role) || $actor->isAdmin()) {
            return;
        }
        throw new AccessDeniedHttpException('Forbidden.');
    }
}
