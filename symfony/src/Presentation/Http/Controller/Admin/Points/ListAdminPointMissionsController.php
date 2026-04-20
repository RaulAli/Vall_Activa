<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Points;

use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminPointMissionsController extends AbstractController
{
    #[Route('/api/admin/points/missions', name: 'admin_point_missions_list', methods: ['GET'])]
    public function __invoke(EntityManagerInterface $em): JsonResponse
    {
        /** @var PointMissionOrm[] $missions */
        $missions = $em->createQueryBuilder()
            ->select('m')
            ->from(PointMissionOrm::class, 'm')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->json(array_map(static fn(PointMissionOrm $m): array => [
            'id' => $m->id,
            'code' => $m->code,
            'title' => $m->title,
            'description' => $m->description,
            'pointsReward' => $m->pointsReward,
            'isActive' => $m->isActive,
            'createdAt' => $m->createdAt->format(DATE_ATOM),
            'updatedAt' => $m->updatedAt->format(DATE_ATOM),
        ], $missions));
    }
}
