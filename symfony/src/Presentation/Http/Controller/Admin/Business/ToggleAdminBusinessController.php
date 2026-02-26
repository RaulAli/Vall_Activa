<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Business;

use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ToggleAdminBusinessController extends AbstractController
{
    #[Route('/api/admin/businesses/{id}/toggle', name: 'admin_toggle_business', methods: ['PATCH'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $business = $em->getRepository(BusinessProfileOrm::class)->find($id);
        if ($business === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $business->isActive = !$business->isActive;
        $em->flush();

        return $this->json(['userId' => $business->userId, 'isActive' => $business->isActive]);
    }
}
