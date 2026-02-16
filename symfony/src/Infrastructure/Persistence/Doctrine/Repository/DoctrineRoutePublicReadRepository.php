<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Route\DTO\RouteMapMarker;
use App\Application\Route\DTO\RoutePublicListItem;
use App\Application\Route\Port\RoutePublicReadRepositoryInterface;
use App\Application\Shared\DTO\PaginatedResult;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use Doctrine\ORM\EntityManagerInterface;
use App\Application\Route\DTO\RoutePublicFilters;
use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\QueryBuilder;

final class DoctrineRoutePublicReadRepository implements RoutePublicReadRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function listPublic(RoutePublicFilters $filters, int $page, int $limit, string $sort, string $order): PaginatedResult
    {
        $offset = ($page - 1) * $limit;

        $baseQb = $this->em->createQueryBuilder()
            ->from(RouteOrm::class, 'r')
            ->andWhere('r.isActive = true')
            ->andWhere('r.visibility = :vis')
            ->andWhere('r.status = :status')
            ->setParameter('vis', 'PUBLIC')
            ->setParameter('status', 'PUBLISHED');

        // sportCode
        if ($filters->sportCode !== null && $filters->sportCode !== '') {
            $baseQb->innerJoin(SportOrm::class, 's', 'WITH', 's.id = r.sportId')
                ->andWhere('LOWER(s.code) = :sportCode')
                ->setParameter('sportCode', strtolower($filters->sportCode));
        }

        // distance
        if ($filters->distanceMin !== null) {
            $baseQb->andWhere('r.distanceM >= :distMin')->setParameter('distMin', $filters->distanceMin);
        }
        if ($filters->distanceMax !== null) {
            $baseQb->andWhere('r.distanceM <= :distMax')->setParameter('distMax', $filters->distanceMax);
        }

        // gain
        if ($filters->gainMin !== null) {
            $baseQb->andWhere('r.elevationGainM >= :gainMin')->setParameter('gainMin', $filters->gainMin);
        }
        if ($filters->gainMax !== null) {
            $baseQb->andWhere('r.elevationGainM <= :gainMax')->setParameter('gainMax', $filters->gainMax);
        }

        // GEO: focus > bbox
        $this->applyGeoFilter($baseQb, $filters);

        // q
        if ($filters->q !== null && trim($filters->q) !== '') {
            $q = '%' . mb_strtolower(trim($filters->q)) . '%';
            $baseQb
                ->andWhere('(LOWER(r.title) LIKE :q OR LOWER(COALESCE(r.description, \'\')) LIKE :q)')
                ->setParameter('q', $q);
        }

        // total
        $total = (int) (clone $baseQb)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // orderBy
        $orderByField = match ($sort) {
            'distance' => 'r.distanceM',
            'gain' => 'r.elevationGainM',
            default => 'r.createdAt',
        };

        // list (SIN polyline)
        $rows = (clone $baseQb)
            ->select('r.id, r.sportId, r.title, r.slug, r.startLat, r.startLng, r.distanceM, r.elevationGainM, r.elevationLossM, r.isActive, r.createdAt, r.image')
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
                startLat: isset($r['startLat']) ? (float) $r['startLat'] : null,
                startLng: isset($r['startLng']) ? (float) $r['startLng'] : null,
                distanceM: (int) $r['distanceM'],
                elevationGainM: (int) $r['elevationGainM'],
                elevationLossM: (int) $r['elevationLossM'],
                isActive: (bool) $r['isActive'],
                createdAt: $createdAtStr,
                image: $r['image'] ?? null
            );
        }, $rows);

        return new PaginatedResult(
            page: $page,
            limit: $limit,
            total: $total,
            items: $items
        );
    }

    /** @return RouteMapMarker[] */
    public function listMapMarkers(RoutePublicFilters $filters, int $limit = 5000): array
    {
        $qb = $this->em->createQueryBuilder()
            ->from(RouteOrm::class, 'r')
            ->andWhere('r.isActive = true')
            ->andWhere('r.visibility = :vis')
            ->andWhere('r.status = :status')
            ->setParameter('vis', 'PUBLIC')
            ->setParameter('status', 'PUBLISHED');

        // Reusar filtros “ligeros” (sin join sport salvo que haga falta)
        if ($filters->sportCode !== null && $filters->sportCode !== '') {
            $qb->innerJoin(SportOrm::class, 's', 'WITH', 's.id = r.sportId')
                ->andWhere('LOWER(s.code) = :sportCode')
                ->setParameter('sportCode', strtolower($filters->sportCode));
        }

        if ($filters->distanceMin !== null)
            $qb->andWhere('r.distanceM >= :distMin')->setParameter('distMin', $filters->distanceMin);
        if ($filters->distanceMax !== null)
            $qb->andWhere('r.distanceM <= :distMax')->setParameter('distMax', $filters->distanceMax);
        if ($filters->gainMin !== null)
            $qb->andWhere('r.elevationGainM >= :gainMin')->setParameter('gainMin', $filters->gainMin);
        if ($filters->gainMax !== null)
            $qb->andWhere('r.elevationGainM <= :gainMax')->setParameter('gainMax', $filters->gainMax);

        $this->applyGeoFilter($qb, $filters);

        if ($filters->q !== null && trim($filters->q) !== '') {
            $q = '%' . mb_strtolower(trim($filters->q)) . '%';
            $qb->andWhere('(LOWER(r.title) LIKE :q OR LOWER(COALESCE(r.description, \'\')) LIKE :q)')
                ->setParameter('q', $q);
        }

        $rows = $qb
            ->select('r.slug, r.title, r.startLat, r.startLng')
            ->andWhere('r.startLat IS NOT NULL')
            ->andWhere('r.startLng IS NOT NULL')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn(array $r) => new RouteMapMarker(
            slug: (string) $r['slug'],
            title: (string) $r['title'],
            lat: isset($r['startLat']) ? (float) $r['startLat'] : null,
            lng: isset($r['startLng']) ? (float) $r['startLng'] : null
        ), $rows);
    }

    public function findPublicBySlug(string $slug): ?\App\Application\Route\DTO\RoutePublicDetails
    {
        // (aquí sí devolvemos polyline)
        $row = $this->em->createQueryBuilder()
            ->select('r.id, r.sportId, r.title, r.slug, r.description, r.visibility, r.status,
                 r.startLat, r.startLng, r.endLat, r.endLng,
                 r.minLat, r.minLng, r.maxLat, r.maxLng,
                 r.distanceM, r.elevationGainM, r.elevationLossM, r.polyline, r.createdAt, r.image')
            ->from(RouteOrm::class, 'r')
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

        if ($row === null)
            return null;

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
            createdAt: $createdAtStr,
            image: $row['image'] ?? null
        );
    }

    public function getFiltersMeta(RoutePublicFilters $filters): \App\Application\Route\DTO\RouteFiltersMeta
    {
        $baseQb = $this->em->createQueryBuilder()
            ->from(RouteOrm::class, 'r')
            ->andWhere('r.isActive = true')
            ->andWhere('r.visibility = :vis')
            ->andWhere('r.status = :status')
            ->setParameter('vis', 'PUBLIC')
            ->setParameter('status', 'PUBLISHED');

        // sportCode (aplica a rangos y counts, pero NO a facet sports)
        if ($filters->sportCode !== null && trim($filters->sportCode) !== '') {
            $baseQb->innerJoin(SportOrm::class, 's', 'WITH', 's.id = r.sportId')
                ->andWhere('LOWER(s.code) = :sportCode')
                ->setParameter('sportCode', strtolower(trim($filters->sportCode)));
        }

        if ($filters->distanceMin !== null)
            $baseQb->andWhere('r.distanceM >= :distMin')->setParameter('distMin', $filters->distanceMin);
        if ($filters->distanceMax !== null)
            $baseQb->andWhere('r.distanceM <= :distMax')->setParameter('distMax', $filters->distanceMax);
        if ($filters->gainMin !== null)
            $baseQb->andWhere('r.elevationGainM >= :gainMin')->setParameter('gainMin', $filters->gainMin);
        if ($filters->gainMax !== null)
            $baseQb->andWhere('r.elevationGainM <= :gainMax')->setParameter('gainMax', $filters->gainMax);

        // GEO: focus > bbox
        $this->applyGeoFilter($baseQb, $filters);

        if ($filters->q !== null && trim($filters->q) !== '') {
            $q = '%' . mb_strtolower(trim($filters->q)) . '%';
            $baseQb->andWhere('(LOWER(r.title) LIKE :q OR LOWER(COALESCE(r.description, \'\')) LIKE :q)')
                ->setParameter('q', $q);
        }

        // RANGES
        $ranges = (clone $baseQb)
            ->select('MIN(r.distanceM) AS minDist, MAX(r.distanceM) AS maxDist, MIN(r.elevationGainM) AS minGain, MAX(r.elevationGainM) AS maxGain')
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY) ?? [];

        $minDist = isset($ranges['minDist']) ? (int) $ranges['minDist'] : null;
        $maxDist = isset($ranges['maxDist']) ? (int) $ranges['maxDist'] : null;
        $minGain = isset($ranges['minGain']) ? (int) $ranges['minGain'] : null;
        $maxGain = isset($ranges['maxGain']) ? (int) $ranges['maxGain'] : null;

        // COUNTS
        $routesCount = (int) (clone $baseQb)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // BOUNDS agregados (del resultado actual, usando bbox de ruta)
        $boundsRow = (clone $baseQb)
            ->select('MIN(r.minLng) AS minLng, MIN(r.minLat) AS minLat, MAX(r.maxLng) AS maxLng, MAX(r.maxLat) AS maxLat')
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY) ?? [];

        $bounds = null;
        if (
            isset($boundsRow['minLng'], $boundsRow['minLat'], $boundsRow['maxLng'], $boundsRow['maxLat']) &&
            $boundsRow['minLng'] !== null && $boundsRow['minLat'] !== null &&
            $boundsRow['maxLng'] !== null && $boundsRow['maxLat'] !== null
        ) {
            $bounds = [
                'minLng' => (float) $boundsRow['minLng'],
                'minLat' => (float) $boundsRow['minLat'],
                'maxLng' => (float) $boundsRow['maxLng'],
                'maxLat' => (float) $boundsRow['maxLat'],
            ];
        }

        // SPORTS FACET (ignora SOLO sportCode)
        $facetQb = $this->em->createQueryBuilder()
            ->from(RouteOrm::class, 'r')
            ->andWhere('r.isActive = true')
            ->andWhere('r.visibility = :vis')
            ->andWhere('r.status = :status')
            ->setParameter('vis', 'PUBLIC')
            ->setParameter('status', 'PUBLISHED');

        // copiar filtros excepto sportCode
        if ($filters->distanceMin !== null)
            $facetQb->andWhere('r.distanceM >= :distMin')->setParameter('distMin', $filters->distanceMin);
        if ($filters->distanceMax !== null)
            $facetQb->andWhere('r.distanceM <= :distMax')->setParameter('distMax', $filters->distanceMax);
        if ($filters->gainMin !== null)
            $facetQb->andWhere('r.elevationGainM >= :gainMin')->setParameter('gainMin', $filters->gainMin);
        if ($filters->gainMax !== null)
            $facetQb->andWhere('r.elevationGainM <= :gainMax')->setParameter('gainMax', $filters->gainMax);

        $this->applyGeoFilter($facetQb, $filters);

        if ($filters->q !== null && trim($filters->q) !== '') {
            $q = '%' . mb_strtolower(trim($filters->q)) . '%';
            $facetQb->andWhere('(LOWER(r.title) LIKE :q OR LOWER(COALESCE(r.description, \'\')) LIKE :q)')
                ->setParameter('q', $q);
        }

        $sportsRows = $facetQb
            ->innerJoin(SportOrm::class, 's2', 'WITH', 's2.id = r.sportId')
            ->andWhere('s2.isActive = true')
            ->select('LOWER(s2.code) AS code, s2.name AS name, COUNT(r.id) AS count')
            ->groupBy('s2.code, s2.name')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $sports = array_map(static fn(array $row) => [
            'code' => (string) $row['code'],
            'name' => (string) $row['name'],
            'count' => (int) $row['count'],
        ], $sportsRows);

        return new \App\Application\Route\DTO\RouteFiltersMeta(
            distance: ['min' => $minDist, 'max' => $maxDist],
            gain: ['min' => $minGain, 'max' => $maxGain],
            sports: $sports,
            counts: ['routes' => $routesCount],
            bounds: $bounds
        );
    }

    private function applyGeoFilter(QueryBuilder $qb, RoutePublicFilters $filters): void
    {
        // PRIORIDAD: focus (start cercano)
        if ($filters->hasFocus()) {
            $lat = (float) $filters->focusLat;
            $lng = (float) $filters->focusLng;
            $r = (int) $filters->focusRadiusM;

            // aproximación bbox (suficiente para 100m)
            $dLat = $r / 111320.0;
            $cos = cos(deg2rad($lat));
            if ($cos < 0.000001)
                $cos = 0.000001; // evita división por ~0
            $dLng = $r / (111320.0 * $cos);

            $minLat = $lat - $dLat;
            $maxLat = $lat + $dLat;
            $minLng = $lng - $dLng;
            $maxLng = $lng + $dLng;

            $qb->andWhere('r.startLat IS NOT NULL')
                ->andWhere('r.startLng IS NOT NULL')
                ->andWhere('r.startLat BETWEEN :fMinLat AND :fMaxLat')
                ->andWhere('r.startLng BETWEEN :fMinLng AND :fMaxLng')
                ->setParameter('fMinLat', $minLat)
                ->setParameter('fMaxLat', $maxLat)
                ->setParameter('fMinLng', $minLng)
                ->setParameter('fMaxLng', $maxLng);

            return;
        }

        // fallback: bbox normal (intersección bbox ruta vs bbox mapa)
        if ($filters->hasBbox()) {
            $qb->andWhere('r.minLat <= :bboxMaxLat')
                ->andWhere('r.maxLat >= :bboxMinLat')
                ->andWhere('r.minLng <= :bboxMaxLng')
                ->andWhere('r.maxLng >= :bboxMinLng')
                ->setParameter('bboxMaxLat', $filters->bboxMaxLat)
                ->setParameter('bboxMinLat', $filters->bboxMinLat)
                ->setParameter('bboxMaxLng', $filters->bboxMaxLng)
                ->setParameter('bboxMinLng', $filters->bboxMinLng);
        }
    }
}
