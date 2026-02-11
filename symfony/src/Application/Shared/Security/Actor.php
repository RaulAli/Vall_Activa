<?php
declare(strict_types=1);

namespace App\Application\Shared\Security;

use App\Domain\Identity\ValueObject\Role;
use App\Domain\Identity\ValueObject\UserId;

final class Actor
{
    /** @param list<Role> $roles */
    public function __construct(
        public readonly UserId $userId,
        public readonly array $roles
    ) {}

    public function isAdmin(): bool
    {
        foreach ($this->roles as $role) {
            if ($role === Role::ADMIN) {
                return true;
            }
        }
        return false;
    }

    public function has(Role $role): bool
    {
        foreach ($this->roles as $r) {
            if ($r === $role) {
                return true;
            }
        }
        return false;
    }
}
