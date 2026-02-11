<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Shared\Security\Actor;
use App\Application\Shared\Security\CurrentUserProviderInterface;
use App\Domain\Identity\ValueObject\Role;
use App\Domain\Identity\ValueObject\UserId;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineUserReadRepository;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class DoctrineCurrentUserProvider implements CurrentUserProviderInterface
{
    public function __construct(private readonly DoctrineUserReadRepository $users)
    {
    }

    public function actorFromUserId(UserId $userId): Actor
    {
        $orm = $this->users->find($userId->value());

        if ($orm === null || $orm->isActive === false) {
            throw new UnauthorizedHttpException('Bearer', 'User not found or inactive.');
        }

        $roles = [];
        foreach (($orm->roles ?? []) as $r) {
            $role = Role::tryFrom((string) $r);
            if ($role !== null) {
                $roles[] = $role;
            }
        }

        // Si no viene ningún rol, por seguridad lo dejamos sin permisos
        return new Actor($userId, $roles);
    }
}
