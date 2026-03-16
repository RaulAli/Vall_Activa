<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Route;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideAvailabilityOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\GuideRouteBookingOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetRouteBookingSlotsController extends AbstractController
{
    private const DAY_KEYS = [
        1 => 'MONDAY',
        2 => 'TUESDAY',
        3 => 'WEDNESDAY',
        4 => 'THURSDAY',
        5 => 'FRIDAY',
        6 => 'SATURDAY',
        7 => 'SUNDAY',
    ];

    #[Route('/api/public/routes/{slug}/booking-slots', name: 'public_route_booking_slots', methods: ['GET'])]
    public function __invoke(string $slug, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $days = (int) $request->query->get('days', 14);
        if ($days < 1) {
            $days = 1;
        }
        if ($days > 30) {
            $days = 30;
        }

        $route = $em->createQueryBuilder()
            ->select('r.id, r.createdByUserId, g.isActive as guideActive')
            ->from(RouteOrm::class, 'r')
            ->leftJoin(GuideProfileOrm::class, 'g', 'WITH', 'g.userId = r.createdByUserId')
            ->andWhere('r.slug = :slug')
            ->andWhere('r.isActive = true')
            ->andWhere('r.adminDisabled = false')
            ->andWhere('r.visibility = :vis')
            ->andWhere('r.status = :status')
            ->setParameter('slug', $slug)
            ->setParameter('vis', 'PUBLIC')
            ->setParameter('status', 'PUBLISHED')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        if ($route === null || $route['guideActive'] === null || (bool) $route['guideActive'] !== true) {
            return $this->json(['error' => 'route_not_bookable'], 404);
        }

        $availability = $em->find(GuideAvailabilityOrm::class, (string) $route['createdByUserId']);
        if ($availability === null) {
            return $this->json([
                'routeId' => (string) $route['id'],
                'timezone' => 'UTC',
                'slotMinutes' => 60,
                'slots' => [],
            ]);
        }

        $tz = new \DateTimeZone($availability->timezone ?: 'UTC');
        $nowUtc = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $nowGuide = $nowUtc->setTimezone($tz);

        $weekMap = [];
        foreach ($availability->week as $dayCfg) {
            if (!is_array($dayCfg)) {
                continue;
            }
            $day = isset($dayCfg['day']) ? (string) $dayCfg['day'] : null;
            if ($day === null) {
                continue;
            }
            $weekMap[$day] = $dayCfg;
        }

        $rangeStartGuide = $nowGuide->setTime(0, 0);
        $rangeEndGuide = $rangeStartGuide->modify(sprintf('+%d days', $days));
        $rangeStartUtc = $rangeStartGuide->setTimezone(new \DateTimeZone('UTC'));
        $rangeEndUtc = $rangeEndGuide->setTimezone(new \DateTimeZone('UTC'));

        $bookedRows = $em->createQueryBuilder()
            ->select('b.scheduledFor, b.endsAt')
            ->from(GuideRouteBookingOrm::class, 'b')
            ->andWhere('b.guideUserId = :guideId')
            ->andWhere('b.status IN (:activeStatuses)')
            ->andWhere('b.scheduledFor < :toUtc')
            ->andWhere('b.endsAt > :fromUtc')
            ->setParameter('guideId', (string) $route['createdByUserId'])
            ->setParameter('activeStatuses', ['REQUESTED', 'CONFIRMED'])
            ->setParameter('fromUtc', $rangeStartUtc)
            ->setParameter('toUtc', $rangeEndUtc)
            ->getQuery()
            ->getArrayResult();

        $bookedRanges = [];
        foreach ($bookedRows as $row) {
            $start = $row['scheduledFor'] ?? null;
            $end = $row['endsAt'] ?? null;
            if (!($start instanceof \DateTimeInterface) || !($end instanceof \DateTimeInterface)) {
                continue;
            }
            $toUtcTs = static fn(\DateTimeInterface $dt): int =>
                (int) (($dt instanceof \DateTimeImmutable ? $dt : \DateTimeImmutable::createFromMutable($dt))
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->format('U'));
            $bookedRanges[] = [$toUtcTs($start), $toUtcTs($end)];
        }

        $slots = [];
        for ($offset = 0; $offset < $days; $offset++) {
            $dayDate = $rangeStartGuide->modify(sprintf('+%d days', $offset));
            $dayKey = self::DAY_KEYS[(int) $dayDate->format('N')] ?? null;
            if ($dayKey === null || !isset($weekMap[$dayKey])) {
                continue;
            }

            $cfg = $weekMap[$dayKey];
            $enabled = (bool) ($cfg['enabled'] ?? false);
            $daySlots = is_array($cfg['slots'] ?? null) ? $cfg['slots'] : [];
            if (!$enabled || $daySlots === []) {
                continue;
            }

            foreach ($daySlots as $slotTime) {
                if (!is_string($slotTime) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $slotTime)) {
                    continue;
                }

                [$h, $m] = explode(':', $slotTime);
                $slotGuide = $dayDate->setTime((int) $h, (int) $m);
                if ($slotGuide <= $nowGuide) {
                    continue;
                }

                $slotUtc = $slotGuide->setTimezone(new \DateTimeZone('UTC'));
                $slotUtcIso = $slotUtc->format(DATE_ATOM);
                $slotTs = (int) $slotUtc->format('U');
                $isAvailable = true;
                foreach ($bookedRanges as [$bStart, $bEnd]) {
                    if ($slotTs >= $bStart && $slotTs < $bEnd) {
                        $isAvailable = false;
                        break;
                    }
                }

                $slots[] = [
                    'startsAt' => $slotUtcIso,
                    'date' => $slotGuide->format('Y-m-d'),
                    'time' => $slotGuide->format('H:i'),
                    'day' => $dayKey,
                    'isAvailable' => $isAvailable,
                    'bookingStatus' => $isAvailable ? null : 'OCCUPIED',
                ];
            }
        }

        return $this->json([
            'routeId' => (string) $route['id'],
            'timezone' => $availability->timezone,
            'slotMinutes' => $availability->slotMinutes,
            'slots' => $slots,
        ]);
    }
}
