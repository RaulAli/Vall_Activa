<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Sport;

use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateAdminSportController extends AbstractController
{
    #[Route('/api/admin/sports/{id}', name: 'admin_update_sport', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $sport = $em->getRepository(SportOrm::class)->find($id);
        if ($sport === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $body = json_decode($request->getContent(), true);

        if (isset($body['name']) && trim((string) $body['name']) !== '') {
            $sport->name = trim((string) $body['name']);
        }

        $sport->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json(['id' => $sport->id, 'code' => $sport->code, 'name' => $sport->name]);
    }
}
