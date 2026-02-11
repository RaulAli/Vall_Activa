<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Offer\DTO\OfferPublicListItem;
use App\Application\Offer\Port\OfferPublicReadRepositoryInterface;
use App\Application\Shared\DTO\PaginatedResult;
use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineOfferPublicReadRepository implements OfferPublicReadRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function listPublic(int $page, int $limit): PaginatedResult
    {
        $offset = ($page - 1) * $limit;

        // Si aún no tienes PUBLISHED, cambia el filtro status por el que uses o elimínalo.
        $total = (int) $this->em->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(OfferOrm::class, 'o')
            ->andWhere('o.isActive = true')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'PUBLISHED')
            ->getQuery()
            ->getSingleScalarResult();

        $rows = $this->em->createQueryBuilder()
            ->select('o.id, o.businessId, o.title, o.slug, o.description, o.price, o.currency, o.image, o.discountType, o.status, o.quantity, o.pointsCost, o.isActive, o.createdAt')
            ->from(OfferOrm::class, 'o')
            ->andWhere('o.isActive = true')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'PUBLISHED')
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $items = array_map(static function (array $r): OfferPublicListItem {
            $createdAt = $r['createdAt'];
            $createdAtStr = $createdAt instanceof \DateTimeInterface ? $createdAt->format(DATE_ATOM) : (string) $createdAt;

            return new OfferPublicListItem(
                id: (string) $r['id'],
                businessId: (string) $r['businessId'],
                title: (string) $r['title'],
                slug: (string) $r['slug'],
                description: $r['description'] ?? null,
                price: (string) $r['price'],
                currency: (string) $r['currency'],
                image: $r['image'] ?? null,
                discountType: (string) $r['discountType'],
                status: (string) $r['status'],
                quantity: (int) $r['quantity'],
                pointsCost: (int) $r['pointsCost'],
                isActive: (bool) $r['isActive'],
                createdAt: $createdAtStr
            );
        }, $rows);

        return new PaginatedResult(
            page: $page,
            limit: $limit,
            total: $total,
            items: $items
        );
    }

    public function findPublicBySlug(string $slug): ?\App\Application\Offer\DTO\OfferPublicDetails
    {
        $row = $this->em->createQueryBuilder()
            ->select('o.id, o.businessId, o.title, o.slug, o.description,
                 o.price, o.currency, o.image, o.discountType,
                 o.quantity, o.pointsCost, o.status, o.isActive, o.createdAt')
            ->from(\App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm::class, 'o')
            ->andWhere('o.isActive = true')
            ->andWhere('o.status = :status')
            ->andWhere('o.slug = :slug')
            ->setParameter('status', 'PUBLISHED')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if ($row === null) {
            return null;
        }

        $createdAt = $row['createdAt'];
        $createdAtStr = $createdAt instanceof \DateTimeInterface
            ? $createdAt->format(DATE_ATOM)
            : (string) $createdAt;

        return new \App\Application\Offer\DTO\OfferPublicDetails(
            id: (string) $row['id'],
            businessId: (string) $row['businessId'],
            title: (string) $row['title'],
            slug: (string) $row['slug'],
            description: $row['description'] ?? null,

            price: (string) $row['price'],
            currency: (string) $row['currency'],

            image: $row['image'] ?? null,
            discountType: (string) $row['discountType'],

            quantity: (int) $row['quantity'],
            pointsCost: (int) $row['pointsCost'],

            status: (string) $row['status'],
            isActive: (bool) $row['isActive'],

            createdAt: $createdAtStr
        );
    }
}
