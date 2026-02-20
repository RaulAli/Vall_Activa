<?php
declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\DTO\UserProfileDto;
use App\Application\User\Port\UserReadRepositoryInterface;
use App\Application\User\Query\GetMeQuery;

final class GetMeHandler
{
    public function __construct(
        private readonly UserReadRepositoryInterface $users,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(GetMeQuery $query): UserProfileDto
    {
        $user = $this->users->findById($query->userId);

        if ($user === null) {
            throw new \DomainException('user_not_found');
        }

        return $user;
    }
}
