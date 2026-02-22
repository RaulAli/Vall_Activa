<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Profile\Port\PublicProfileRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\AthleteProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePublicProfileRepository implements PublicProfileRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function findBySlug(string $slug): ?array
    {
        // Business
        $p = $this->em->getRepository(BusinessProfileOrm::class)->findOneBy(['slug' => $slug]);
        if ($p !== null) {
            return [
                'userId' => $p->userId,
                'slug' => $p->slug,
                'name' => $p->name,
                'avatar' => $p->profileIcon,
                'role' => 'ROLE_BUSINESS',
                'lat' => $p->lat,
                'lng' => $p->lng,
            ];
        }

        // Athlete
        $p = $this->em->getRepository(AthleteProfileOrm::class)->findOneBy(['slug' => $slug]);
        if ($p !== null) {
            return [
                'userId' => $p->userId,
                'slug' => $p->slug,
                'name' => $p->name,
                'avatar' => $p->avatar,
                'role' => 'ROLE_ATHLETE',
                'city' => $p->city,
                'birthDate' => $p->birthDate?->format('Y-m-d'),
            ];
        }

        // Guide
        $p = $this->em->getRepository(GuideProfileOrm::class)->findOneBy(['slug' => $slug]);
        if ($p !== null) {
            return [
                'userId' => $p->userId,
                'slug' => $p->slug,
                'name' => $p->name,
                'avatar' => $p->avatar,
                'role' => 'ROLE_GUIDE',
                'bio' => $p->bio,
                'city' => $p->city,
                'lat' => $p->lat,
                'lng' => $p->lng,
                'sports' => $p->sports ?? [],
            ];
        }

        return null;
    }
}
