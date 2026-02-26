<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Sport;

use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateAdminSportController extends AbstractController
{
    #[Route('/api/admin/sports', name: 'admin_create_sport', methods: ['POST'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $code = strtoupper(trim((string) ($body['code'] ?? '')));
        $name = trim((string) ($body['name'] ?? ''));

        if ($code === '' || $name === '') {
            return $this->json(['error' => 'code and name are required'], 422);
        }

        $existing = $em->getRepository(SportOrm::class)->findOneBy(['code' => $code]);
        if ($existing !== null) {
            return $this->json(['error' => 'code_already_exists'], 409);
        }

        $sport = new SportOrm();
        $sport->id = \Symfony\Component\Uid\Uuid::v4()->toRfc4122();
        $sport->code = $code;
        $sport->name = $name;
        $sport->isActive = true;
        $sport->createdAt = new \DateTimeImmutable();
        $sport->updatedAt = new \DateTimeImmutable();

        $em->persist($sport);
        $em->flush();

        return $this->json([
            'id' => $sport->id,
            'code' => $sport->code,
            'name' => $sport->name,
            'isActive' => $sport->isActive,
        ], 201);
    }
}
