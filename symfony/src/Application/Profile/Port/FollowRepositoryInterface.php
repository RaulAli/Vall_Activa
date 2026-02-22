<?php
declare(strict_types=1);

namespace App\Application\Profile\Port;

interface FollowRepositoryInterface
{
    public function follow(string $followerId, string $followeeId): void;

    public function unfollow(string $followerId, string $followeeId): void;

    public function isFollowing(string $followerId, string $followeeId): bool;

    public function countFollowers(string $followeeId): int;

    public function countFollowing(string $followerId): int;

    /** @return array<int, array{slug:string, name:string, avatar:string|null, role:string}> */
    public function listFollowers(string $followeeId): array;

    /** @return array<int, array{slug:string, name:string, avatar:string|null, role:string}> */
    public function listFollowing(string $followerId): array;
}
