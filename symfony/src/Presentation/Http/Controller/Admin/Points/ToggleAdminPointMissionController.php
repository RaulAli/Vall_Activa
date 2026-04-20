<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Points;

use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ToggleAdminPointMissionController extends AbstractController
{
    #[Route('/api/admin/points/missions/{id}/toggle', name: 'admin_point_missions_toggle', methods: ['PATCH'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $mission = $em->find(PointMissionOrm::class, $id);
        if (!$mission instanceof PointMissionOrm) {
            return $this->json(['error' => 'mission_not_found'], 404);
        }

        $mission->isActive = !$mission->isActive;
        $mission->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'id' => $mission->id,
            'isActive' => $mission->isActive,
        ]);
    }
}
