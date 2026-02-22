<?php
declare(strict_types=1);

namespace App\Application\Profile\Handler;

use App\Application\Profile\Command\UnfollowProfileCommand;
use App\Application\Profile\Port\FollowRepositoryInterface;
use App\Application\Profile\Port\PublicProfileRepositoryInterface;

final class UnfollowProfileHandler
{
    public function __construct(
        private readonly PublicProfileRepositoryInterface $publicProfiles,
        private readonly FollowRepositoryInterface $follows,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(UnfollowProfileCommand $cmd): void
    {
        $data = $this->publicProfiles->findBySlug($cmd->slug);

        if ($data === null) {
            throw new \DomainException('profile_not_found');
        }

        $this->follows->unfollow($cmd->followerId, $data['userId']);
    }
}
