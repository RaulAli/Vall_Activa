<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\User;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DeleteAdminUserController extends AbstractController
{
    #[Route('/api/admin/users/{id}', name: 'admin_delete_user', methods: ['DELETE'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(UserOrm::class)->find($id);
        if ($user === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(null, 204);
    }
}
