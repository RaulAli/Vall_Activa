<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Points;

use App\Domain\Shared\ValueObject\Uuid;
use App\Infrastructure\Persistence\Doctrine\Entity\Points\PointMissionOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CreateAdminPointMissionController extends AbstractController
{
    #[Route('/api/admin/points/missions', name: 'admin_point_missions_create', methods: ['POST'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            return $this->json(['error' => 'invalid_json'], 400);
        }

        $code = isset($payload['code']) && is_string($payload['code']) ? strtoupper(trim($payload['code'])) : '';
        $title = isset($payload['title']) && is_string($payload['title']) ? trim($payload['title']) : '';
        $description = isset($payload['description']) && is_string($payload['description'])
            ? trim($payload['description'])
            : null;
        $pointsReward = isset($payload['pointsReward']) && is_int($payload['pointsReward'])
            ? $payload['pointsReward']
            : 0;

        if ($code === '' || $title === '' || $pointsReward <= 0) {
            return $this->json(['error' => 'invalid_payload'], 422);
        }

        $exists = $em->getRepository(PointMissionOrm::class)->findOneBy(['code' => $code]);
        if ($exists instanceof PointMissionOrm) {
            return $this->json(['error' => 'mission_code_exists'], 409);
        }

        $now = new \DateTimeImmutable();

        $mission = new PointMissionOrm();
        $mission->id = Uuid::v4()->value();
        $mission->code = $code;
        $mission->title = mb_substr($title, 0, 120);
        $mission->description = $description !== '' ? $description : null;
        $mission->pointsReward = max(1, min(10000, $pointsReward));
        $mission->isActive = true;
        $mission->createdAt = $now;
        $mission->updatedAt = $now;

        $em->persist($mission);
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
        ], 201);
    }
}
