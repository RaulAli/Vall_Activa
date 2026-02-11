<?php
declare(strict_types=1);

namespace App\Application\Shared\Security;

use App\Domain\Identity\ValueObject\Role;

interface AuthorizationServiceInterface
{
    public function requireRole(Actor $actor, Role $role): void;
}
