<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Points;

use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateAdminPointMissionController extends AbstractController
{
    #[Route('/api/admin/points/missions/{id}', name: 'admin_point_missions_update', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $mission = $em->find(PointMissionOrm::class, $id);
        if (!$mission instanceof PointMissionOrm) {
            return $this->json(['error' => 'mission_not_found'], 404);
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        if (array_key_exists('code', $payload) && is_string($payload['code'])) {
            $code = strtoupper(trim($payload['code']));
            if ($code === '') {
                return $this->json(['error' => 'invalid_code'], 422);
            }

            $existing = $em->getRepository(PointMissionOrm::class)->findOneBy(['code' => $code]);
            if ($existing instanceof PointMissionOrm && $existing->id !== $mission->id) {
                return $this->json(['error' => 'mission_code_exists'], 409);
            }

            $mission->code = mb_substr($code, 0, 80);
        }

        if (array_key_exists('title', $payload) && is_string($payload['title'])) {
            $title = trim($payload['title']);
            if ($title === '') {
                return $this->json(['error' => 'invalid_title'], 422);
            }

            $mission->title = mb_substr($title, 0, 120);
        }

        if (array_key_exists('description', $payload) && (is_string($payload['description']) || $payload['description'] === null)) {
            $description = is_string($payload['description']) ? trim($payload['description']) : '';
            $mission->description = $description !== '' ? $description : null;
        }

        if (array_key_exists('pointsReward', $payload) && is_int($payload['pointsReward'])) {
            $mission->pointsReward = max(1, min(10000, $payload['pointsReward']));
        }

        $mission->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'id' => $mission->id,
            'code' => $mission->code,
            'title' => $mission->title,
            'description' => $mission->description,
            'pointsReward' => $mission->pointsReward,
            'isActive' => $mission->isActive,
            'createdAt' => $mission->createdAt->format(DATE_ATOM),
            'updatedAt' => $mission->updatedAt->format(DATE_ATOM),
        ]);
    }
}
