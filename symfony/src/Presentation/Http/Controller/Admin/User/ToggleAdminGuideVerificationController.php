<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\User;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ToggleAdminGuideVerificationController extends AbstractController
{
    #[Route('/api/admin/guides/{id}/verify', name: 'admin_toggle_guide_verify', methods: ['PATCH'])]
    public function __invoke(string $id, EntityManagerInterface $em): JsonResponse
    {
        $guide = $em->getRepository(GuideProfileOrm::class)->find($id);
        if ($guide === null) {
            return $this->json(['error' => 'guide_not_found'], 404);
        }

        $guide->isVerified = !$guide->isVerified;
        $guide->updatedAt = new \DateTimeImmutable();
        $em->flush();

        return $this->json([
            'userId' => $guide->userId,
            'isVerified' => $guide->isVerified,
        ]);
    }
}
