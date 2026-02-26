<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Route;

use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminRoutesController extends AbstractController
{
    #[Route('/api/admin/routes', name: 'admin_list_routes', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = $request->query->get('q');

        $qb = $em->createQueryBuilder()
            ->select('r')
            ->from(RouteOrm::class, 'r')
            ->orderBy('r.createdAt', 'DESC');

        if (is_string($q) && trim($q) !== '') {
            $qb->andWhere('r.title LIKE :q')
                ->setParameter('q', '%' . trim($q) . '%');
        }

        /** @var RouteOrm[] $routes */
        $routes = $qb->getQuery()->getResult();

        // Build sport map
        $sportIds = array_unique(array_map(fn(RouteOrm $r) => $r->sportId, $routes));
        $sportsMap = [];
        if (!empty($sportIds)) {
            $sports = $em->getRepository(SportOrm::class)->findBy(['id' => $sportIds]);
            foreach ($sports as $s) {
                $sportsMap[$s->id] = ['code' => $s->code, 'name' => $s->name];
            }
        }

        $data = array_map(static fn(RouteOrm $r) => [
            'id' => $r->id,
            'title' => $r->title,
            'slug' => $r->slug,
            'visibility' => $r->visibility,
            'status' => $r->status,
            'distanceM' => $r->distanceM,
            'elevationGainM' => $r->elevationGainM,
            'durationMin' => $r->durationSeconds !== null ? (int) round($r->durationSeconds / 60) : null,
            'difficulty' => $r->difficulty,
            'routeType' => $r->routeType,
            'createdByUserId' => $r->createdByUserId,
            'sport' => $sportsMap[$r->sportId] ?? null,
            'createdAt' => $r->createdAt->format(\DateTimeInterface::ATOM),
            'adminDisabled' => $r->adminDisabled,
        ], $routes);

        return $this->json($data);
    }
}
