<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\User\DTO\UserProfileDto;
use App\Application\User\Port\UserReadRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\AthleteProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserReadRepository implements UserReadRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /** Used internally (DoctrineCurrentUserProvider) — returns raw ORM. */
    public function find(string $id): ?UserOrm
    {
        return $this->em->find(UserOrm::class, $id);
    }

    public function findById(string $id): ?UserProfileDto
    {
        $orm = $this->em->find(UserOrm::class, $id);
        if ($orm === null) {
            return null;
        }

        $role = $orm->role;

        // Fetch profile based on role
        $slug = $name = $avatar = $city = $birthDate = $bio = null;
        $lat = $lng = null;
        $sports = null;

        if ($role === 'ROLE_BUSINESS') {
            $p = $this->em->find(BusinessProfileOrm::class, $id);
            if ($p !== null) {
                $slug = $p->slug;
                $name = $p->name;
                $avatar = $p->profileIcon;
                $lat = $p->lat;
                $lng = $p->lng;
            }
        } elseif ($role === 'ROLE_ATHLETE') {
            $p = $this->em->find(AthleteProfileOrm::class, $id);
            if ($p !== null) {
                $slug = $p->slug;
                $name = $p->name;
                $avatar = $p->avatar;
                $city = $p->city;
                $birthDate = $p->birthDate?->format('Y-m-d');
            }
        } elseif ($role === 'ROLE_GUIDE') {
            $p = $this->em->find(GuideProfileOrm::class, $id);
            if ($p !== null) {
                $slug = $p->slug;
                $name = $p->name;
                $avatar = $p->avatar;
                $bio = $p->bio;
                $city = $p->city;
                $lat = $p->lat;
                $lng = $p->lng;
                $sports = $p->sports ?? [];
            }
        }

        return new UserProfileDto(
            id: $orm->id,
            email: $orm->email,
            role: $role,
            createdAt: $orm->createdAt->format(\DateTimeInterface::ATOM),
            slug: $slug,
            name: $name,
            avatar: $avatar,
            lat: $lat,
            lng: $lng,
            city: $city,
            birthDate: $birthDate,
            bio: $bio,
            sports: $sports,
        );
    }

    public function findHashedPassword(string $id): ?string
    {
        $orm = $this->em->find(UserOrm::class, $id);
        return $orm?->password;
    }
}
