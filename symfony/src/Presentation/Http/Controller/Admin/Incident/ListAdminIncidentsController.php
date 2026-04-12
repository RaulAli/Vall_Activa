<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Incident;

use App\Application\Incident\Handler\ListAdminIncidentsHandler;
use App\Application\Incident\Query\ListAdminIncidentsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminIncidentsController extends AbstractController
{
    #[Route('/api/admin/incidents', name: 'admin_list_incidents', methods: ['GET'])]
    public function __invoke(Request $request, ListAdminIncidentsHandler $handler): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $items = $handler(new ListAdminIncidentsQuery(
            q: $request->query->get('q'),
            status: $request->query->get('status'),
        ));

        return $this->json($items);
    }
}
