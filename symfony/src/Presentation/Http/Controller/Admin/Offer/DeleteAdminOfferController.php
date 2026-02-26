<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Offer;

use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteAdminOfferController extends AbstractController
{
    #[Route('/api/admin/offers/{id}', name: 'admin_delete_offer', methods: ['DELETE'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $offer = $em->getRepository(OfferOrm::class)->find($id);
        if ($offer === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $em->remove($offer);
        $em->flush();

        return $this->json(null, 204);
    }
}
