<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin;

use App\Infrastructure\Persistence\Doctrine\Entity\Business\BusinessProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Offer\OfferOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class GetAdminStatsController extends AbstractController
{
    #[Route('/api/admin/stats', name: 'admin_stats', methods: ['GET'])]
    public function __invoke(EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();

        $totalUsers = (int) $conn->fetchOne('SELECT COUNT(*) FROM users');
        $activeUsers = (int) $conn->fetchOne('SELECT COUNT(*) FROM users WHERE is_active = true');
        $totalRoutes = (int) $conn->fetchOne('SELECT COUNT(*) FROM routes');
        $publicRoutes = (int) $conn->fetchOne("SELECT COUNT(*) FROM routes WHERE UPPER(visibility) = 'PUBLIC' AND admin_disabled = false");
        $totalOffers = (int) $conn->fetchOne('SELECT COUNT(*) FROM offers');
        $activeOffers = (int) $conn->fetchOne('SELECT COUNT(*) FROM offers WHERE is_active = true');
        $totalBusinesses = (int) $conn->fetchOne('SELECT COUNT(*) FROM business_profiles');
        $totalSports = (int) $conn->fetchOne('SELECT COUNT(*) FROM sports');

        return $this->json([
            'users' => ['total' => $totalUsers, 'active' => $activeUsers],
            'routes' => ['total' => $totalRoutes, 'public' => $publicRoutes],
            'offers' => ['total' => $totalOffers, 'active' => $activeOffers],
            'businesses' => ['total' => $totalBusinesses],
            'sports' => ['total' => $totalSports],
        ]);
    }
}
