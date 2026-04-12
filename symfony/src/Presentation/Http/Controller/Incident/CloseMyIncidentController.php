<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Incident;

use App\Infrastructure\Persistence\Doctrine\Entity\Incident\IncidentOrm;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CloseMyIncidentController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/incidents/{id}/close', name: 'close_my_incident', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        /** @var IncidentOrm|null $incident */
        $incident = $em->getRepository(IncidentOrm::class)->findOneBy(['id' => $id, 'userId' => $userId]);
        if ($incident === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $incident->status = 'RESOLVED';
        $incident->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json(['id' => $incident->id, 'status' => $incident->status]);
    }

    private function extractUserId(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        try {
            $payload = $this->jwtManager->parse(substr($authHeader, 7));
            return isset($payload['userId']) ? (string) $payload['userId'] : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
