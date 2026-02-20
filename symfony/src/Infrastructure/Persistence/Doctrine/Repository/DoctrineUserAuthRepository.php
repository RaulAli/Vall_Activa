<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Auth\DTO\UserAuthData;
use App\Application\Auth\Port\UserAuthRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserAuthRepository implements UserAuthRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function findByEmail(string $email): ?UserAuthData
    {
        $orm = $this->em->getRepository(UserOrm::class)->findOneBy(['email' => $email]);
        return $orm ? $this->toDto($orm) : null;
    }

    public function findById(string $id): ?UserAuthData
    {
        $orm = $this->em->find(UserOrm::class, $id);
        return $orm ? $this->toDto($orm) : null;
    }

    public function existsByEmail(string $email): bool
    {
        return $this->em->getRepository(UserOrm::class)->count(['email' => $email]) > 0;
    }

    public function createUser(string $id, string $email, string $hashedPassword, string $role): void
    {
        $now = new \DateTimeImmutable();

        $orm = new UserOrm();
        $orm->id = $id;
        $orm->email = $email;
        $orm->password = $hashedPassword;
        $orm->role = $role;
        $orm->isActive = true;
        $orm->createdAt = $now;
        $orm->updatedAt = $now;

        $this->em->persist($orm);
        $this->em->flush();
    }

    private function toDto(UserOrm $orm): UserAuthData
    {
        return new UserAuthData(
            id: $orm->id,
            email: $orm->email,
            hashedPassword: $orm->password,
            isActive: $orm->isActive,
        );
    }
}
