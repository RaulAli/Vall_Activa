<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Business;

use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminBusinessesController extends AbstractController
{
    #[Route('/api/admin/businesses', name: 'admin_list_businesses', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = $request->query->get('q');

        $qb = $em->createQueryBuilder()
            ->select('b')
            ->from(BusinessProfileOrm::class, 'b')
            ->orderBy('b.createdAt', 'DESC');

        if (is_string($q) && trim($q) !== '') {
            $qb->andWhere('b.name LIKE :q')
                ->setParameter('q', '%' . trim($q) . '%');
        }

        /** @var BusinessProfileOrm[] $businesses */
        $businesses = $qb->getQuery()->getResult();

        $data = array_map(static fn(BusinessProfileOrm $b) => [
            'userId' => $b->userId,
            'slug' => $b->slug,
            'name' => $b->name,
            'profileIcon' => $b->profileIcon,
            'lat' => $b->lat,
            'lng' => $b->lng,
            'isActive' => $b->isActive,
            'createdAt' => $b->createdAt->format(\DateTimeInterface::ATOM),
        ], $businesses);

        return $this->json($data);
    }
}
