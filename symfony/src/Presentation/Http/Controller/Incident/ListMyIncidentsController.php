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

final class ListMyIncidentsController extends AbstractController
{
    public function __construct(private readonly JWTTokenManagerInterface $jwtManager)
    {
    }

    #[Route('/api/incidents/me', name: 'list_my_incidents', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        /** @var IncidentOrm[] $incidents */
        $incidents = $em->getRepository(IncidentOrm::class)->findBy(
            ['userId' => $userId],
            ['createdAt' => 'DESC']
        );

        $data = array_map(static fn(IncidentOrm $incident) => [
            'id' => $incident->id,
            'category' => $incident->category,
            'subject' => $incident->subject,
            'message' => $incident->message,
            'status' => $incident->status,
            'createdAt' => $incident->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $incident->updatedAt->format(\DateTimeInterface::ATOM),
        ], $incidents);

        return $this->json($data);
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
