<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Sport\Entity\Sport;
use App\Domain\Sport\Repository\SportRepositoryInterface;
use App\Domain\Sport\ValueObject\SportCode;
use App\Domain\Sport\ValueObject\SportId;
use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSportRepository implements SportRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function findActiveByCode(SportCode $code): ?Sport
    {
        $repo = $this->em->getRepository(SportOrm::class);

        /** @var SportOrm|null $orm */
        $orm = $repo->findOneBy([
            'code' => $code->value(),
            'isActive' => true,
        ]);

        if ($orm === null) {
            return null;
        }

        return Sport::fromPersistence(
            SportId::fromString($orm->id),
            SportCode::fromString($orm->code),
            $orm->name,
            $orm->isActive
        );
    }
}
