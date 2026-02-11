<?php
declare(strict_types=1);

namespace App\Domain\Identity\Entity;

use App\Domain\Identity\ValueObject\Role;
use App\Domain\Identity\ValueObject\UserId;

final class User
{
    /** @var array<string, Role> */
    private array $roles = [];

    public function __construct(
        private UserId $id,
        private string $email,
        Role ...$roles
    ) {
        foreach ($roles as $role) {
            $this->roles[$role->value] = $role;
        }
    }

    public function id(): UserId { return $this->id; }
    public function email(): string { return $this->email; }

    /** @return list<Role> */
    public function roles(): array { return array_values($this->roles); }

    public function hasRole(Role $role): bool
    {
        return isset($this->roles[$role->value]);
    }
}
