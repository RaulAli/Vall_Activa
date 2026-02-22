<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Offer\DTO\OfferPublicFilters;
use App\Application\Offer\DTO\OfferPublicListItem;
use App\Application\Offer\Port\OfferPublicReadRepositoryInterface;
use App\Application\Shared\DTO\PaginatedResult;
use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineOfferPublicReadRepository implements OfferPublicReadRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function listPublic(OfferPublicFilters $filters, int $page, int $limit, string $sort, string $order): PaginatedResult
    {
        $offset = ($page - 1) * $limit;

        $baseQb = $this->em->createQueryBuilder()
            ->select('o')
            ->from(OfferOrm::class, 'o')
            ->andWhere('o.isActive = true')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'PUBLISHED');

        // JOIN al perfil del business (para bbox + markers en futuro)
        $baseQb->innerJoin(
            BusinessProfileOrm::class,
            'b',
            'WITH',
            'b.userId = o.businessId AND b.isActive = true'
        );

        // bbox por business.lat/lng
        $hasBbox =
            $filters->bboxMinLat !== null && $filters->bboxMinLng !== null &&
            $filters->bboxMaxLat !== null && $filters->bboxMaxLng !== null;

        if ($hasBbox) {
            $baseQb
                ->andWhere('b.lat IS NOT NULL AND b.lng IS NOT NULL')
                ->andWhere('b.lat >= :minLat AND b.lat <= :maxLat')
                ->andWhere('b.lng >= :minLng AND b.lng <= :maxLng')
                ->setParameter('minLat', $filters->bboxMinLat)
                ->setParameter('maxLat', $filters->bboxMaxLat)
                ->setParameter('minLng', $filters->bboxMinLng)
                ->setParameter('maxLng', $filters->bboxMaxLng);
        }

        // q
        if ($filters->q !== null && trim($filters->q) !== '') {
            $q = '%' . mb_strtolower(trim($filters->q)) . '%';
            $baseQb
                ->andWhere('(LOWER(o.title) LIKE :q OR LOWER(COALESCE(o.description, \'\')) LIKE :q)')
                ->setParameter('q', $q);
        }

        // discountType
        if ($filters->discountType !== null && trim($filters->discountType) !== '') {
            $baseQb->andWhere('o.discountType = :dt')->setParameter('dt', trim($filters->discountType));
        }

        // price
        if ($filters->priceMin !== null && $filters->priceMin !== '') {
            $baseQb->andWhere('o.price >= :pmin')->setParameter('pmin', $filters->priceMin);
        }
        if ($filters->priceMax !== null && $filters->priceMax !== '') {
            $baseQb->andWhere('o.price <= :pmax')->setParameter('pmax', $filters->priceMax);
        }

        // points
        if ($filters->pointsMin !== null) {
            $baseQb->andWhere('o.pointsCost >= :ptMin')->setParameter('ptMin', $filters->pointsMin);
        }
        if ($filters->pointsMax !== null) {
            $baseQb->andWhere('o.pointsCost <= :ptMax')->setParameter('ptMax', $filters->pointsMax);
        }

        // stock
        if ($filters->inStock === true) {
            $baseQb->andWhere('o.quantity > 0');
        }

        // total
        $totalQb = clone $baseQb;
        $total = (int) $totalQb->select('COUNT(o.id)')->getQuery()->getSingleScalarResult();

        // order by
        $orderByField = match ($sort) {
            'price' => 'o.price',
            'points' => 'o.pointsCost',
            default => 'o.createdAt',
        };

        // rows
        $rowsQb = clone $baseQb;
        $rows = $rowsQb
            ->select('o.id, o.businessId, o.title, o.slug, o.description, o.price, o.currency, o.image, o.discountType, o.status, o.quantity, o.pointsCost, o.isActive, o.createdAt, b.name AS businessName, b.slug AS businessSlug, b.profileIcon AS businessAvatar')
            ->orderBy($orderByField, strtoupper($order))
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
                createdAt: $createdAtStr,
                businessName: isset($r['businessName']) ? (string) $r['businessName'] : null,
                businessSlug: isset($r['businessSlug']) ? (string) $r['businessSlug'] : null,
                businessAvatar: $r['businessAvatar'] ?? null,
            );
        }, $rows);

        return new PaginatedResult($page, $limit, $total, $items);
    }

    public function findPublicBySlug(string $slug): ?\App\Application\Offer\DTO\OfferPublicDetails
    {
        $row = $this->em->createQueryBuilder()
            ->select('o.id, o.businessId, o.title, o.slug, o.description,
                 o.price, o.currency, o.image, o.discountType,
                 o.quantity, o.pointsCost, o.status, o.isActive, o.createdAt,
                 b.lat, b.lng, b.name AS businessName, b.slug AS businessSlug, b.profileIcon AS businessAvatar')
            ->from(\App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm::class, 'o')
            ->innerJoin(
                \App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm::class,
                'b',
                'WITH',
                'b.userId = o.businessId'
            )
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

            lat: isset($row['lat']) ? (float) $row['lat'] : null,
            lng: isset($row['lng']) ? (float) $row['lng'] : null,

            createdAt: $createdAtStr,
            businessName: isset($row['businessName']) ? (string) $row['businessName'] : null,
            businessSlug: isset($row['businessSlug']) ? (string) $row['businessSlug'] : null,
            businessAvatar: $row['businessAvatar'] ?? null,
        );
    }

    public function listMapBusinesses(\App\Application\Offer\DTO\OfferPublicFilters $filters): array
    {
        $qb = $this->em->createQueryBuilder()
            ->from(OfferOrm::class, 'o')
            ->andWhere('o.isActive = true')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'PUBLISHED');

        // join business profile (para bbox + datos marker)
        $qb->innerJoin(
            BusinessProfileOrm::class,
            'b',
            'WITH',
            'b.userId = o.businessId AND b.isActive = true'
        );

        // bbox
        $hasBbox =
            $filters->bboxMinLat !== null && $filters->bboxMinLng !== null &&
            $filters->bboxMaxLat !== null && $filters->bboxMaxLng !== null;

        if ($hasBbox) {
            $qb
                ->andWhere('b.lat IS NOT NULL AND b.lng IS NOT NULL')
                ->andWhere('b.lat >= :minLat AND b.lat <= :maxLat')
                ->andWhere('b.lng >= :minLng AND b.lng <= :maxLng')
                ->setParameter('minLat', $filters->bboxMinLat)
                ->setParameter('maxLat', $filters->bboxMaxLat)
                ->setParameter('minLng', $filters->bboxMinLng)
                ->setParameter('maxLng', $filters->bboxMaxLng);
        }

        // q
        if ($filters->q !== null && trim($filters->q) !== '') {
            $q = '%' . mb_strtolower(trim($filters->q)) . '%';
            $qb
                ->andWhere('(LOWER(o.title) LIKE :q OR LOWER(COALESCE(o.description, \'\')) LIKE :q)')
                ->setParameter('q', $q);
        }

        // discountType
        if ($filters->discountType !== null && trim($filters->discountType) !== '') {
            $qb->andWhere('o.discountType = :dt')->setParameter('dt', trim($filters->discountType));
        }

        // price
        if ($filters->priceMin !== null && $filters->priceMin !== '') {
            $qb->andWhere('o.price >= :pmin')->setParameter('pmin', $filters->priceMin);
        }
        if ($filters->priceMax !== null && $filters->priceMax !== '') {
            $qb->andWhere('o.price <= :pmax')->setParameter('pmax', $filters->priceMax);
        }

        // points
        if ($filters->pointsMin !== null) {
            $qb->andWhere('o.pointsCost >= :ptMin')->setParameter('ptMin', $filters->pointsMin);
        }
        if ($filters->pointsMax !== null) {
            $qb->andWhere('o.pointsCost <= :ptMax')->setParameter('ptMax', $filters->pointsMax);
        }

        // stock
        if ($filters->inStock === true) {
            $qb->andWhere('o.quantity > 0');
        }

        // SELECT agrupado
        $rows = $qb
            ->select('b.userId AS businessUserId, b.name AS name, b.profileIcon AS profileIcon, b.lat AS lat, b.lng AS lng, b.slug AS slug, COUNT(o.id) AS offersCount')
            ->groupBy('b.userId, b.name, b.profileIcon, b.lat, b.lng, b.slug')
            ->getQuery()
            ->getArrayResult();

        return array_map(static function (array $r): \App\Application\Offer\DTO\BusinessMapMarker {
            return new \App\Application\Offer\DTO\BusinessMapMarker(
                businessUserId: (string) $r['businessUserId'],
                slug: (string) $r['slug'],
                name: (string) $r['name'],
                lat: (float) $r['lat'],
                lng: (float) $r['lng'],
                profileIcon: $r['profileIcon'] ?? null,
                offersCount: (int) $r['offersCount']
            );
        }, $rows);
    }

    public function getFiltersMeta(\App\Application\Offer\DTO\OfferPublicFilters $filters): \App\Application\Offer\DTO\OfferFiltersMeta
    {
        // Base QB con filtros comunes (siempre)
        $baseQb = $this->em->createQueryBuilder()
            ->from(\App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm::class, 'o')
            ->andWhere('o.isActive = true')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'PUBLISHED');

        // join business profile (para bbox + bounds)
        $baseQb->innerJoin(
            \App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm::class,
            'b',
            'WITH',
            'b.userId = o.businessId AND b.isActive = true'
        );

        // bbox (minLng,minLat,maxLng,maxLat)
        $hasBbox =
            $filters->bboxMinLat !== null && $filters->bboxMinLng !== null &&
            $filters->bboxMaxLat !== null && $filters->bboxMaxLng !== null;

        if ($hasBbox) {
            $baseQb
                ->andWhere('b.lat IS NOT NULL AND b.lng IS NOT NULL')
                ->andWhere('b.lat >= :minLat AND b.lat <= :maxLat')
                ->andWhere('b.lng >= :minLng AND b.lng <= :maxLng')
                ->setParameter('minLat', $filters->bboxMinLat)
                ->setParameter('maxLat', $filters->bboxMaxLat)
                ->setParameter('minLng', $filters->bboxMinLng)
                ->setParameter('maxLng', $filters->bboxMaxLng);
        }

        // q
        if ($filters->q !== null && trim($filters->q) !== '') {
            $q = '%' . mb_strtolower(trim($filters->q)) . '%';
            $baseQb
                ->andWhere('(LOWER(o.title) LIKE :q OR LOWER(COALESCE(o.description, \'\')) LIKE :q)')
                ->setParameter('q', $q);
        }

        // discountType (solo afecta a ranges/counts, pero NO a la faceta discountTypes)
        if ($filters->discountType !== null && trim($filters->discountType) !== '') {
            $baseQb->andWhere('o.discountType = :dt')->setParameter('dt', trim($filters->discountType));
        }

        // price range filters
        if ($filters->priceMin !== null && $filters->priceMin !== '') {
            $baseQb->andWhere('o.price >= :pmin')->setParameter('pmin', $filters->priceMin);
        }
        if ($filters->priceMax !== null && $filters->priceMax !== '') {
            $baseQb->andWhere('o.price <= :pmax')->setParameter('pmax', $filters->priceMax);
        }

        // points range filters
        if ($filters->pointsMin !== null) {
            $baseQb->andWhere('o.pointsCost >= :ptMin')->setParameter('ptMin', $filters->pointsMin);
        }
        if ($filters->pointsMax !== null) {
            $baseQb->andWhere('o.pointsCost <= :ptMax')->setParameter('ptMax', $filters->pointsMax);
        }

        // stock
        if ($filters->inStock === true) {
            $baseQb->andWhere('o.quantity > 0');
        }

        // ---------- 1) RANGES (sobre query completa con TODOS los filtros) ----------
        $ranges = (clone $baseQb)
            ->select(
                'MIN(o.price) AS minPrice, MAX(o.price) AS maxPrice,
             MIN(o.pointsCost) AS minPoints, MAX(o.pointsCost) AS maxPoints'
            )
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY) ?? [];

        $priceMin = $ranges['minPrice'] ?? null;
        $priceMax = $ranges['maxPrice'] ?? null;

        $pointsMin = isset($ranges['minPoints']) ? (int) $ranges['minPoints'] : null;
        $pointsMax = isset($ranges['maxPoints']) ? (int) $ranges['maxPoints'] : null;

        // ---------- 2) COUNTS (sobre query completa con TODOS los filtros) ----------
        $offersCount = (int) (clone $baseQb)
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $businessesCount = (int) (clone $baseQb)
            ->select('COUNT(DISTINCT o.businessId)')
            ->getQuery()
            ->getSingleScalarResult();

        // ---------- 3) BOUNDS (sobre businesses filtrados) ----------
        // bounds deben reflejar el estado filtrado actual
        $boundsRow = (clone $baseQb)
            ->select('MIN(b.lng) AS minLng, MIN(b.lat) AS minLat, MAX(b.lng) AS maxLng, MAX(b.lat) AS maxLat')
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

        // ---------- 4) DISCOUNT TYPES FACET (ignora SOLO discountType seleccionado) ----------
        $facetQb = $this->em->createQueryBuilder()
            ->from(\App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm::class, 'o')
            ->andWhere('o.isActive = true')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'PUBLISHED')
            ->innerJoin(
                \App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm::class,
                'b',
                'WITH',
                'b.userId = o.businessId AND b.isActive = true'
            );

        // copiar filtros excepto discountType
        if ($hasBbox) {
            $facetQb
                ->andWhere('b.lat IS NOT NULL AND b.lng IS NOT NULL')
                ->andWhere('b.lat >= :minLat AND b.lat <= :maxLat')
                ->andWhere('b.lng >= :minLng AND b.lng <= :maxLng')
                ->setParameter('minLat', $filters->bboxMinLat)
                ->setParameter('maxLat', $filters->bboxMaxLat)
                ->setParameter('minLng', $filters->bboxMinLng)
                ->setParameter('maxLng', $filters->bboxMaxLng);
        }

        if ($filters->q !== null && trim($filters->q) !== '') {
            $q = '%' . mb_strtolower(trim($filters->q)) . '%';
            $facetQb
                ->andWhere('(LOWER(o.title) LIKE :q OR LOWER(COALESCE(o.description, \'\')) LIKE :q)')
                ->setParameter('q', $q);
        }

        if ($filters->priceMin !== null && $filters->priceMin !== '') {
            $facetQb->andWhere('o.price >= :pmin')->setParameter('pmin', $filters->priceMin);
        }
        if ($filters->priceMax !== null && $filters->priceMax !== '') {
            $facetQb->andWhere('o.price <= :pmax')->setParameter('pmax', $filters->priceMax);
        }
        if ($filters->pointsMin !== null) {
            $facetQb->andWhere('o.pointsCost >= :ptMin')->setParameter('ptMin', $filters->pointsMin);
        }
        if ($filters->pointsMax !== null) {
            $facetQb->andWhere('o.pointsCost <= :ptMax')->setParameter('ptMax', $filters->pointsMax);
        }
        if ($filters->inStock === true) {
            $facetQb->andWhere('o.quantity > 0');
        }

        $discountRows = $facetQb
            ->select('o.discountType AS value, COUNT(o.id) AS count')
            ->groupBy('o.discountType')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $discountTypes = array_map(static fn(array $r) => [
            'value' => (string) $r['value'],
            'count' => (int) $r['count'],
        ], $discountRows);

        return new \App\Application\Offer\DTO\OfferFiltersMeta(
            price: ['min' => $priceMin, 'max' => $priceMax],
            points: ['min' => $pointsMin, 'max' => $pointsMax],
            discountTypes: $discountTypes,
            counts: ['offers' => $offersCount, 'businesses' => $businessesCount],
            bounds: $bounds
        );
    }


}
