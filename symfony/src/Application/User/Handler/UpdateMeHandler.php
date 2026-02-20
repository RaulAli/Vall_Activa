<?php
declare(strict_types=1);

namespace App\Application\User\Handler;

use App\Application\User\Command\UpdateMeCommand;
use App\Application\User\Port\UserReadRepositoryInterface;
use App\Application\User\Port\UserWriteRepositoryInterface;

final class UpdateMeHandler
{
    public function __construct(
        private readonly UserReadRepositoryInterface $readUsers,
        private readonly UserWriteRepositoryInterface $writeUsers,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(UpdateMeCommand $cmd): void
    {
        $user = $this->readUsers->findById($cmd->userId);

        if ($user === null) {
            throw new \DomainException('user_not_found');
        }

        $role = $user->role;

        if (!in_array($role, ['ROLE_BUSINESS', 'ROLE_ATHLETE', 'ROLE_GUIDE'], true)) {
            throw new \DomainException('no_profile_for_role');
        }

        $data = $cmd->data;

        // Normalize slug
        if (isset($data['slug']) && $data['slug'] !== '') {
            $data['slug'] = mb_strtolower(trim((string) $data['slug']));
            if ($this->writeUsers->existsBySlug($data['slug'], $role, $cmd->userId)) {
                throw new \DomainException('slug_already_taken');
            }
        }

        $this->writeUsers->updateProfile($cmd->userId, $role, $data);
    }
}
