<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\IncidentCategory;

use App\Infrastructure\Persistence\Doctrine\Entity\Incident\IncidentCategoryOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ToggleAdminIncidentCategoryController extends AbstractController
{
    #[Route('/api/admin/incident-categories/{id}/toggle', name: 'admin_toggle_incident_category', methods: ['PATCH'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $category = $em->getRepository(IncidentCategoryOrm::class)->find($id);
        if ($category === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $category->isActive = !$category->isActive;
        $category->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json(['id' => $category->id, 'isActive' => $category->isActive]);
    }
}
