<?php
declare(strict_types=1);

namespace App\Application\Profile\Handler;

use App\Application\Profile\Command\FollowProfileCommand;
use App\Application\Profile\Port\FollowRepositoryInterface;
use App\Application\Profile\Port\PublicProfileRepositoryInterface;

final class FollowProfileHandler
{
    public function __construct(
        private readonly PublicProfileRepositoryInterface $publicProfiles,
        private readonly FollowRepositoryInterface $follows,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(FollowProfileCommand $cmd): void
    {
        $data = $this->publicProfiles->findBySlug($cmd->slug);

        if ($data === null) {
            throw new \DomainException('profile_not_found');
        }

        $followeeId = $data['userId'];

        if ($cmd->followerId === $followeeId) {
            throw new \DomainException('cannot_follow_yourself');
        }

        if ($this->follows->isFollowing($cmd->followerId, $followeeId)) {
            // Idempotent — already following
            return;
        }

        $this->follows->follow($cmd->followerId, $followeeId);
    }
}
