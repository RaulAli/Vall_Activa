<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Offer;

use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ToggleAdminOfferController extends AbstractController
{
    #[Route('/api/admin/offers/{id}/toggle', name: 'admin_toggle_offer', methods: ['PATCH'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $offer = $em->getRepository(OfferOrm::class)->find($id);
        if ($offer === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $offer->adminDisabled = !$offer->adminDisabled;
        $em->flush();

        return $this->json(['id' => $offer->id, 'adminDisabled' => $offer->adminDisabled]);
    }
}
