<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Public\Sport;

use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetPublicSportsController extends AbstractController
{
    #[Route('/api/public/sports', name: 'public_sports_list', methods: ['GET'])]
    public function __invoke(EntityManagerInterface $em): JsonResponse
    {
        /** @var SportOrm[] $sports */
        $sports = $em->getRepository(SportOrm::class)->findBy(
            ['isActive' => true],
            ['code' => 'ASC']
        );

        $data = array_map(static fn(SportOrm $s) => [
            'code' => $s->code,
            'name' => $s->name,
        ], $sports);

        return $this->json($data);
    }
}
