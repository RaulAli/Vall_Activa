<?php
declare(strict_types=1);

namespace App\Application\Offer\Handler;

use App\Application\Offer\Command\CreateOfferCommand;
use App\Application\Shared\Port\TransactionInterface;
use App\Application\Shared\Security\AuthorizationServiceInterface;
use App\Domain\Identity\ValueObject\Role;
use App\Domain\Offer\Entity\Offer;
use App\Domain\Offer\Repository\OfferRepositoryInterface;
use App\Domain\Offer\ValueObject\DiscountType;
use App\Domain\Offer\ValueObject\OfferStatus;

final class CreateOfferHandler
{
    public function __construct(
        private readonly AuthorizationServiceInterface $auth,
        private readonly TransactionInterface $tx,
        private readonly OfferRepositoryInterface $offers
    ) {}

    public function __invoke(CreateOfferCommand $cmd): string
    {
        // Business o Admin
        if (!$cmd->actor->isAdmin()) {
            $this->auth->requireRole($cmd->actor, Role::BUSINESS);
        }

        $discountType = DiscountType::tryFrom($cmd->discountType);
        if ($discountType === null) {
            throw new \InvalidArgumentException('Invalid discountType.');
        }

        $status = OfferStatus::tryFrom($cmd->status);
        if ($status === null) {
            throw new \InvalidArgumentException('Invalid status.');
        }

        return $this->tx->run(function () use ($cmd, $discountType, $status): string {
            $businessId = $cmd->actor->userId->value(); // MVP: businessId = userId

            $offer = Offer::create(
                businessId: $businessId,
                title: $cmd->title,
                slug: $cmd->slug,
                description: $cmd->description,
                price: $cmd->price,
                currency: $cmd->currency,
                isActive: $cmd->isActive,
                quantity: $cmd->quantity,
                pointsCost: $cmd->pointsCost,
                image: $cmd->image,
                discountType: $discountType,
                status: $status
            );

            $this->offers->save($offer);

            return $offer->id()->value();
        });
    }
}
