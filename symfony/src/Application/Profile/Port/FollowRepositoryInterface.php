<?php
declare(strict_types=1);

namespace App\Application\Profile\Port;

interface FollowRepositoryInterface
{
    public function follow(string $followerId, string $followeeId): void;

    public function unfollow(string $followerId, string $followeeId): void;

    public function isFollowing(string $followerId, string $followeeId): bool;

    public function countFollowers(string $followeeId): int;
}
