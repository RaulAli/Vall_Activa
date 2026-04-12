<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Incident;

use App\Application\Incident\Port\IncidentCategoryReadRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListIncidentCategoriesController extends AbstractController
{
    #[Route('/api/incidents/categories', name: 'list_incident_categories', methods: ['GET'])]
    public function __invoke(IncidentCategoryReadRepositoryInterface $categories): JsonResponse
    {
        return $this->json($categories->listActive());
    }
}
