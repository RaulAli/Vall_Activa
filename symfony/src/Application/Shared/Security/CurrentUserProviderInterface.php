<?php
declare(strict_types=1);

namespace App\Application\Shared\Security;

use App\Domain\Identity\ValueObject\UserId;

interface CurrentUserProviderInterface
{
    /**
     * Devuelve el Actor (userId + roles) o lanza excepción si no existe.
     */
    public function actorFromUserId(UserId $userId): Actor;
}
