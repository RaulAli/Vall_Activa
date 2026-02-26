<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Offer;

use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminOffersController extends AbstractController
{
    #[Route('/api/admin/offers', name: 'admin_list_offers', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = $request->query->get('q');

        $qb = $em->createQueryBuilder()
            ->select('o')
            ->from(OfferOrm::class, 'o')
            ->orderBy('o.createdAt', 'DESC');

        if (is_string($q) && trim($q) !== '') {
            $qb->andWhere('o.title LIKE :q')
                ->setParameter('q', '%' . trim($q) . '%');
        }

        /** @var OfferOrm[] $offers */
        $offers = $qb->getQuery()->getResult();

        $data = array_map(static fn(OfferOrm $o) => [
            'id' => $o->id,
            'title' => $o->title,
            'slug' => $o->slug,
            'businessId' => $o->businessId,
            'price' => $o->price,
            'currency' => $o->currency,
            'discountType' => $o->discountType,
            'status' => $o->status,
            'isActive' => $o->isActive,
            'quantity' => $o->quantity,
            'pointsCost' => $o->pointsCost,
            'createdAt' => $o->createdAt->format(\DateTimeInterface::ATOM),
            'adminDisabled' => $o->adminDisabled,
        ], $offers);

        return $this->json($data);
    }
}
