<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Route;

use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteAdminRouteController extends AbstractController
{
    #[Route('/api/admin/routes/{id}', name: 'admin_delete_route', methods: ['DELETE'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $route = $em->getRepository(RouteOrm::class)->find($id);
        if ($route === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $em->remove($route);
        $em->flush();

        return $this->json(null, 204);
    }
}
