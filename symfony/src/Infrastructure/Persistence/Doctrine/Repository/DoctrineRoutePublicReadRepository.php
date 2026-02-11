<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Route\DTO\RoutePublicListItem;
use App\Application\Route\Port\RoutePublicReadRepositoryInterface;
use App\Application\Shared\DTO\PaginatedResult;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use Doctrine\ORM\EntityManagerInterface;
use App\Application\Route\DTO\RoutePublicFilters;
use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;

final class DoctrineRoutePublicReadRepository implements RoutePublicReadRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function listPublic(RoutePublicFilters $filters, int $page, int $limit, string $sort, string $order): PaginatedResult
    {
        $offset = ($page - 1) * $limit;

        // Base QB (filtros comunes)
        $baseQb = $this->em->createQueryBuilder()
            ->select('r')
            ->from(RouteOrm::class, 'r')
            ->andWhere('r.isActive = true')
            ->andWhere('r.visibility = :vis')
            ->andWhere('r.status = :status')
            ->setParameter('vis', 'PUBLIC')
            ->setParameter('status', 'PUBLISHED');

        // Join sport solo si hay sportCode (para no encarecer siempre)
        if ($filters->sportCode !== null && $filters->sportCode !== '') {
            $baseQb->innerJoin(
                SportOrm::class,
                's',
                'WITH',
                's.id = r.sportId'
            )
                ->andWhere('LOWER(s.code) = :sportCode')
                ->setParameter('sportCode', strtolower($filters->sportCode));
        }

        // Distancia
        if ($filters->distanceMin !== null) {
            $baseQb->andWhere('r.distanceM >= :distMin')
                ->setParameter('distMin', $filters->distanceMin);
        }
        if ($filters->distanceMax !== null) {
            $baseQb->andWhere('r.distanceM <= :distMax')
                ->setParameter('distMax', $filters->distanceMax);
        }

        // Desnivel positivo
        if ($filters->gainMin !== null) {
            $baseQb->andWhere('r.elevationGainM >= :gainMin')
                ->setParameter('gainMin', $filters->gainMin);
        }
        if ($filters->gainMax !== null) {
            $baseQb->andWhere('r.elevationGainM <= :gainMax')
                ->setParameter('gainMax', $filters->gainMax);
        }

        // BBOX intersección (rectángulo del mapa)
        $hasBbox = $filters->bboxMinLat !== null && $filters->bboxMinLng !== null
            && $filters->bboxMaxLat !== null && $filters->bboxMaxLng !== null;

        if ($hasBbox) {
            // Intersección entre bbox ruta y bbox mapa
            $baseQb
                ->andWhere('r.minLat <= :bboxMaxLat')
                ->andWhere('r.maxLat >= :bboxMinLat')
                ->andWhere('r.minLng <= :bboxMaxLng')
                ->andWhere('r.maxLng >= :bboxMinLng')
                ->setParameter('bboxMaxLat', $filters->bboxMaxLat)
                ->setParameter('bboxMinLat', $filters->bboxMinLat)
                ->setParameter('bboxMaxLng', $filters->bboxMaxLng)
                ->setParameter('bboxMinLng', $filters->bboxMinLng);
        }

        // Texto (MVP con LIKE)
        if ($filters->q !== null && trim($filters->q) !== '') {
            $q = '%' . mb_strtolower(trim($filters->q)) . '%';
            $baseQb
                ->andWhere('(LOWER(r.title) LIKE :q OR LOWER(r.description) LIKE :q)')
                ->setParameter('q', $q);
        }

        // TOTAL (clonar qb)
        $totalQb = clone $baseQb;
        $total = (int) $totalQb->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // ORDER BY
        $orderByField = match ($sort) {
            'distance' => 'r.distanceM',
            'gain' => 'r.elevationGainM',
            default => 'r.createdAt',
        };

        $rowsQb = clone $baseQb;
        $rows = $rowsQb
            ->select('r.id, r.sportId, r.title, r.slug, r.polyline, r.minLat, r.minLng, r.maxLat, r.maxLng, r.distanceM, r.elevationGainM, r.elevationLossM, r.isActive, r.createdAt')
            ->orderBy($orderByField, strtoupper($order))
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        $items = array_map(static function (array $r): RoutePublicListItem {
            $createdAt = $r['createdAt'];
            $createdAtStr = $createdAt instanceof \DateTimeInterface ? $createdAt->format(DATE_ATOM) : (string) $createdAt;

            return new RoutePublicListItem(
                id: (string) $r['id'],
                sportId: (string) $r['sportId'],
                title: (string) $r['title'],
                slug: (string) $r['slug'],
                polyline: $r['polyline'] ?? null,
                minLat: isset($r['minLat']) ? (float) $r['minLat'] : null,
                minLng: isset($r['minLng']) ? (float) $r['minLng'] : null,
                maxLat: isset($r['maxLat']) ? (float) $r['maxLat'] : null,
                maxLng: isset($r['maxLng']) ? (float) $r['maxLng'] : null,
                distanceM: (int) $r['distanceM'],
                elevationGainM: (int) $r['elevationGainM'],
                elevationLossM: (int) $r['elevationLossM'],
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

    public function findPublicBySlug(string $slug): ?\App\Application\Route\DTO\RoutePublicDetails
    {
        $row = $this->em->createQueryBuilder()
            ->select('r.id, r.sportId, r.title, r.slug, r.description, r.visibility, r.status,
                 r.startLat, r.startLng, r.endLat, r.endLng,
                 r.minLat, r.minLng, r.maxLat, r.maxLng,
                 r.distanceM, r.elevationGainM, r.elevationLossM, r.polyline, r.createdAt')
            ->from(\App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm::class, 'r')
            ->andWhere('r.isActive = true')
            ->andWhere('r.visibility = :vis')
            ->andWhere('r.status = :status')
            ->andWhere('r.slug = :slug')
            ->setParameter('vis', 'PUBLIC')
            ->setParameter('status', 'PUBLISHED')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if ($row === null) {
            return null;
        }

        $createdAt = $row['createdAt'];
        $createdAtStr = $createdAt instanceof \DateTimeInterface ? $createdAt->format(DATE_ATOM) : (string) $createdAt;

        return new \App\Application\Route\DTO\RoutePublicDetails(
            id: (string) $row['id'],
            sportId: (string) $row['sportId'],
            title: (string) $row['title'],
            slug: (string) $row['slug'],
            description: $row['description'] ?? null,

            visibility: (string) $row['visibility'],
            status: (string) $row['status'],

            startLat: isset($row['startLat']) ? (float) $row['startLat'] : null,
            startLng: isset($row['startLng']) ? (float) $row['startLng'] : null,
            endLat: isset($row['endLat']) ? (float) $row['endLat'] : null,
            endLng: isset($row['endLng']) ? (float) $row['endLng'] : null,

            minLat: isset($row['minLat']) ? (float) $row['minLat'] : null,
            minLng: isset($row['minLng']) ? (float) $row['minLng'] : null,
            maxLat: isset($row['maxLat']) ? (float) $row['maxLat'] : null,
            maxLng: isset($row['maxLng']) ? (float) $row['maxLng'] : null,

            distanceM: (int) $row['distanceM'],
            elevationGainM: (int) $row['elevationGainM'],
            elevationLossM: (int) $row['elevationLossM'],

            polyline: $row['polyline'] ?? null,

            createdAt: $createdAtStr
        );
    }

}
