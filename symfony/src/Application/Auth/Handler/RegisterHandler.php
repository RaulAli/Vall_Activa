<?php
declare(strict_types=1);

namespace App\Application\Auth\Handler;

use App\Application\Auth\Command\RegisterCommand;
use App\Application\Auth\Port\PasswordHasherInterface;
use App\Application\Auth\Port\UserAuthRepositoryInterface;
use App\Domain\Shared\ValueObject\Uuid;

final class RegisterHandler
{
    public function __construct(
        private readonly UserAuthRepositoryInterface $users,
        private readonly PasswordHasherInterface $hasher,
    ) {}

    /** @throws \DomainException */
    public function __invoke(RegisterCommand $cmd): string
    {
        $email = mb_strtolower(trim($cmd->email));
        $slug  = mb_strtolower(trim($cmd->slug));

        if ($this->users->existsByEmail($email)) {
            throw new \DomainException('email_already_taken');
        }

        if ($this->users->existsBySlug($slug)) {
            throw new \DomainException('slug_already_taken');
        }

        $id     = Uuid::v4()->value();
        $hashed = $this->hasher->hash($cmd->plainPassword);

        $this->users->createUser(
            id: $id,
            email: $email,
            hashedPassword: $hashed,
            slug: $slug,
        );

        return $id;
    }
}
