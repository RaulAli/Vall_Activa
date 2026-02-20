<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Auth\Port\PasswordHasherInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class SymfonyPasswordHasher implements PasswordHasherInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher) {}

    public function hash(string $plainPassword): string
    {
        $dummy = new UserOrm();
        return $this->hasher->hashPassword($dummy, $plainPassword);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        $dummy           = new UserOrm();
        $dummy->password = $hashedPassword;
        return $this->hasher->isPasswordValid($dummy, $plainPassword);
    }
}
