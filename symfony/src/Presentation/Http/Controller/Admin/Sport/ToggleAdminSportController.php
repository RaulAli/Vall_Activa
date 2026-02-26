<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Sport;

use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ToggleAdminSportController extends AbstractController
{
    #[Route('/api/admin/sports/{id}/toggle', name: 'admin_toggle_sport', methods: ['PATCH'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $sport = $em->getRepository(SportOrm::class)->find($id);
        if ($sport === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $sport->isActive = !$sport->isActive;
        $sport->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json(['id' => $sport->id, 'isActive' => $sport->isActive]);
    }
}
