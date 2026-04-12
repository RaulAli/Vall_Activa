<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\IncidentCategory;

use App\Domain\Shared\ValueObject\Uuid;
use App\Infrastructure\Persistence\Doctrine\Entity\Incident\IncidentCategoryOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateAdminIncidentCategoryController extends AbstractController
{
    #[Route('/api/admin/incident-categories', name: 'admin_create_incident_category', methods: ['POST'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $code = strtoupper(trim((string) ($body['code'] ?? '')));
        $name = trim((string) ($body['name'] ?? ''));

        if ($code === '' || $name === '') {
            return $this->json(['error' => 'code and name are required'], 422);
        }

        $existing = $em->getRepository(IncidentCategoryOrm::class)->findOneBy(['code' => $code]);
        if ($existing !== null) {
            return $this->json(['error' => 'code_already_exists'], 409);
        }

        $category = new IncidentCategoryOrm();
        $category->id = Uuid::v4()->value();
        $category->code = $code;
        $category->name = $name;
        $category->isActive = true;
        $category->createdAt = new \DateTimeImmutable();
        $category->updatedAt = new \DateTimeImmutable();

        $em->persist($category);
        $em->flush();

        return $this->json([
            'id' => $category->id,
            'code' => $category->code,
            'name' => $category->name,
            'isActive' => $category->isActive,
        ], 201);
    }
}
