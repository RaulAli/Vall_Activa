<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Incident\DTO\AdminIncidentListItem;
use App\Application\Incident\Port\AdminIncidentReadRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Incident\IncidentOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineAdminIncidentReadRepository implements AdminIncidentReadRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function list(?string $q, ?string $status): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('i.id, i.userId, i.category, i.subject, i.message, i.status, i.createdAt, u.email AS userEmail')
            ->from(IncidentOrm::class, 'i')
            ->leftJoin(UserOrm::class, 'u', 'WITH', 'u.id = i.userId')
            ->orderBy('i.createdAt', 'DESC');

        if ($q !== null) {
            $qLike = '%' . mb_strtolower($q) . '%';
            $qb
                ->andWhere('(LOWER(i.subject) LIKE :q OR LOWER(i.message) LIKE :q OR LOWER(i.category) LIKE :q OR LOWER(COALESCE(u.email, \'\')) LIKE :q)')
                ->setParameter('q', $qLike);
        }

        if ($status !== null) {
            $qb
                ->andWhere('i.status = :status')
                ->setParameter('status', $status);
        }

        $rows = $qb->getQuery()->getArrayResult();

        return array_map(static function (array $row): AdminIncidentListItem {
            $createdAt = $row['createdAt'];

            return new AdminIncidentListItem(
                id: (string) $row['id'],
                userId: (string) $row['userId'],
                userEmail: isset($row['userEmail']) ? (string) $row['userEmail'] : null,
                category: (string) $row['category'],
                subject: (string) $row['subject'],
                message: (string) $row['message'],
                status: (string) $row['status'],
                createdAt: $createdAt instanceof \DateTimeInterface ? $createdAt->format(DATE_ATOM) : (string) $createdAt,
            );
        }, $rows);
    }
}
