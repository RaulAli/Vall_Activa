<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\IncidentCategory;

use App\Infrastructure\Persistence\Doctrine\Entity\Incident\IncidentCategoryOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateAdminIncidentCategoryController extends AbstractController
{
    #[Route('/api/admin/incident-categories/{id}', name: 'admin_update_incident_category', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $category = $em->getRepository(IncidentCategoryOrm::class)->find($id);
        if ($category === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $body = json_decode($request->getContent(), true);

        if (isset($body['name']) && trim((string) $body['name']) !== '') {
            $category->name = trim((string) $body['name']);
        }

        if (isset($body['code']) && trim((string) $body['code']) !== '') {
            $newCode = strtoupper(trim((string) $body['code']));
            $existing = $em->getRepository(IncidentCategoryOrm::class)->findOneBy(['code' => $newCode]);
            if ($existing !== null && $existing->id !== $category->id) {
                return $this->json(['error' => 'code_already_exists'], 409);
            }
            $category->code = $newCode;
        }

        $category->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'id' => $category->id,
            'code' => $category->code,
            'name' => $category->name,
            'isActive' => $category->isActive,
        ]);
    }
}
