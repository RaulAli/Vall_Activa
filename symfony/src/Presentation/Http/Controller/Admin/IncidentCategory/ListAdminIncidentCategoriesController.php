<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\IncidentCategory;

use App\Infrastructure\Persistence\Doctrine\Entity\Incident\IncidentCategoryOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminIncidentCategoriesController extends AbstractController
{
    #[Route('/api/admin/incident-categories', name: 'admin_list_incident_categories', methods: ['GET'])]
    public function __invoke(EntityManagerInterface $em): JsonResponse
    {
        /** @var IncidentCategoryOrm[] $categories */
        $categories = $em->getRepository(IncidentCategoryOrm::class)->findBy([], ['name' => 'ASC']);

        $data = array_map(static fn(IncidentCategoryOrm $c) => [
            'id' => $c->id,
            'code' => $c->code,
            'name' => $c->name,
            'isActive' => $c->isActive,
            'createdAt' => $c->createdAt->format(\DateTimeInterface::ATOM),
        ], $categories);

        return $this->json($data);
    }
}
