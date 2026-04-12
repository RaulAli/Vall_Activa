<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Incident;

use App\Infrastructure\Persistence\Doctrine\Entity\Incident\IncidentOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class UpdateAdminIncidentStatusController extends AbstractController
{
    #[Route('/api/admin/incidents/{id}/status', name: 'admin_update_incident_status', methods: ['PATCH'])]
    public function __invoke(string $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var IncidentOrm|null $incident */
        $incident = $em->getRepository(IncidentOrm::class)->find($id);
        if ($incident === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $body = json_decode($request->getContent(), true);
        if (!is_array($body)) {
            return $this->json(['error' => 'bad_request', 'message' => 'Invalid JSON body.'], 400);
        }

        $status = strtoupper(trim((string) ($body['status'] ?? '')));
        if (!in_array($status, ['OPEN', 'REVIEWING', 'RESOLVED'], true)) {
            return $this->json(['error' => 'bad_request', 'message' => 'Invalid status.'], 400);
        }

        $incident->status = $status;
        $incident->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json(['id' => $incident->id, 'status' => $incident->status]);
    }
}
