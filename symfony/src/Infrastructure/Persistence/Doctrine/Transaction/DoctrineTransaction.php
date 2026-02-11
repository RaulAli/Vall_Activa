<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Transaction;

use App\Application\Shared\Port\TransactionInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTransaction implements TransactionInterface
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function run(callable $fn)
    {
        $this->em->beginTransaction();
        try {
            $result = $fn();
            $this->em->flush();
            $this->em->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
