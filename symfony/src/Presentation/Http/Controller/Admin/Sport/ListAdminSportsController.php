<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\Sport;

use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminSportsController extends AbstractController
{
    #[Route('/api/admin/sports', name: 'admin_list_sports', methods: ['GET'])]
    public function __invoke(EntityManagerInterface $em): JsonResponse
    {
        /** @var SportOrm[] $sports */
        $sports = $em->getRepository(SportOrm::class)->findBy([], ['code' => 'ASC']);

        $data = array_map(static fn(SportOrm $s) => [
            'id' => $s->id,
            'code' => $s->code,
            'name' => $s->name,
            'isActive' => $s->isActive,
            'createdAt' => $s->createdAt->format(\DateTimeInterface::ATOM),
        ], $sports);

        return $this->json($data);
    }
}
