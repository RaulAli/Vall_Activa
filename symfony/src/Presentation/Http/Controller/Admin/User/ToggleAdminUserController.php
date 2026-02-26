<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\User;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ToggleAdminUserController extends AbstractController
{
    #[Route('/api/admin/users/{id}/toggle', name: 'admin_toggle_user', methods: ['PATCH'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(UserOrm::class)->find($id);
        if ($user === null) {
            return $this->json(['error' => 'not_found'], 404);
        }

        $user->isActive = !$user->isActive;
        $em->flush();

        return $this->json(['id' => $user->id, 'isActive' => $user->isActive]);
    }
}
