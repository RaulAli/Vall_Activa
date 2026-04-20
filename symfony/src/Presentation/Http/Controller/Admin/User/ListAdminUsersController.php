<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller\Admin\User;

use App\Infrastructure\Persistence\Doctrine\Entity\Identity\GuideProfileOrm;
use App\Infrastructure\Persistence\Doctrine\Entity\Identity\UserOrm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ListAdminUsersController extends AbstractController
{
    #[Route('/api/admin/users', name: 'admin_list_users', methods: ['GET'])]
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $q = $request->query->get('q');

        $qb = $em->createQueryBuilder()
            ->select('u')
            ->from(UserOrm::class, 'u')
            ->orderBy('u.createdAt', 'DESC');

        if (is_string($q) && trim($q) !== '') {
            $qb->andWhere('u.email LIKE :q')
                ->setParameter('q', '%' . trim($q) . '%');
        }

        /** @var UserOrm[] $users */
        $users = $qb->getQuery()->getResult();

        $guideIds = array_values(array_map(
            static fn(UserOrm $u) => $u->id,
            array_filter($users, static fn(UserOrm $u) => $u->role === 'ROLE_GUIDE')
        ));

        $guideVerifiedMap = [];
        if ($guideIds !== []) {
            /** @var GuideProfileOrm[] $guideProfiles */
            $guideProfiles = $em->createQueryBuilder()
                ->select('g')
                ->from(GuideProfileOrm::class, 'g')
                ->where('g.userId IN (:ids)')
                ->setParameter('ids', $guideIds)
                ->getQuery()
                ->getResult();

            foreach ($guideProfiles as $g) {
                $guideVerifiedMap[$g->userId] = $g->isVerified;
            }
        }

        $data = array_map(static fn(UserOrm $u) => [
            'id' => $u->id,
            'email' => $u->email,
            'role' => $u->role,
            'isActive' => $u->isActive,
            'isVip' => $u->vipActive,
            'pointsBalance' => $u->pointsBalance,
            'isGuideVerified' => $u->role === 'ROLE_GUIDE' ? ($guideVerifiedMap[$u->id] ?? false) : null,
            'createdAt' => $u->createdAt->format(\DateTimeInterface::ATOM),
        ], $users);

        return $this->json($data);
    }
}
