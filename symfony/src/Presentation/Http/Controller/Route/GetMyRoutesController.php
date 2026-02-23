<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Route;

use App\Infrastructure\Persistence\Doctrine\Entity\Route\RouteOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Sport\SportOrm;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class GetMyRoutesController extends AbstractController
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    #[Route('/api/me/routes', name: 'me_routes_list', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $this->extractUserId($request);
        if ($userId === null) {
            return $this->json(['error' => 'unauthorized'], 401);
        }

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $em->createQueryBuilder()
            ->select(
                'r.id, r.title, r.slug, r.visibility, r.status, ' .
                'r.distanceM, r.elevationGainM, r.elevationLossM, ' .
                'r.image, r.createdAt, r.isActive, ' .
                's.code AS sportCode, s.name AS sportName'
            )
            ->from(RouteOrm::class, 'r')
            ->leftJoin(SportOrm::class, 's', 'WITH', 'r.sportId = s.id')
            ->where('r.createdByUserId = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $data = array_map(static function (array $r): array {
            /** @var \DateTimeInterface $createdAt */
            $createdAt = $r['createdAt'];
            return [
                'id'             => $r['id'],
                'title'          => $r['title'],
                'slug'           => $r['slug'],
                'visibility'     => $r['visibility'],
                'status'         => $r['status'],
                'distanceM'      => $r['distanceM'],
                'elevationGainM' => $r['elevationGainM'],
                'elevationLossM' => $r['elevationLossM'],
                'image'          => $r['image'],
                'createdAt'      => $createdAt->format(\DateTimeInterface::ATOM),
                'isActive'       => $r['isActive'],
                'sportCode'      => $r['sportCode'] ?? null,
                'sportName'      => $r['sportName'] ?? null,
            ];
        }, $rows);

        return $this->json($data);
    }

    private function extractUserId(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        try {
            $payload = $this->jwtManager->parse(substr($authHeader, 7));
            return isset($payload['userId']) ? (string) $payload['userId'] : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
