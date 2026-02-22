<?php
declare(strict_types=1);

namespace App\Application\Profile\Handler;

use App\Application\Profile\DTO\PublicProfileDto;
use App\Application\Profile\Port\FollowRepositoryInterface;
use App\Application\Profile\Port\PublicProfileRepositoryInterface;
use App\Application\Profile\Query\GetPublicProfileQuery;

final class GetPublicProfileHandler
{
    public function __construct(
        private readonly PublicProfileRepositoryInterface $publicProfiles,
        private readonly FollowRepositoryInterface $follows,
    ) {
    }

    /** @throws \DomainException */
    public function __invoke(GetPublicProfileQuery $query): PublicProfileDto
    {
        $data = $this->publicProfiles->findBySlug($query->slug);

        if ($data === null) {
            throw new \DomainException('profile_not_found');
        }

        $followersCount = $this->follows->countFollowers($data['userId']);
        $followingCount = $this->follows->countFollowing($data['userId']);

        $isFollowedByMe = false;
        if ($query->requestingUserId !== null) {
            $isFollowedByMe = $this->follows->isFollowing($query->requestingUserId, $data['userId']);
        }

        return new PublicProfileDto(
            userId: $data['userId'],
            slug: $data['slug'],
            name: $data['name'],
            avatar: $data['avatar'] ?? null,
            role: $data['role'],
            followersCount: $followersCount,
            followingCount: $followingCount,
            isFollowedByMe: $isFollowedByMe,
            lat: $data['lat'] ?? null,
            lng: $data['lng'] ?? null,
            city: $data['city'] ?? null,
            birthDate: $data['birthDate'] ?? null,
            bio: $data['bio'] ?? null,
            sports: $data['sports'] ?? null,
        );
    }
}
