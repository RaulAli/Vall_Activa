<?php
declare(strict_types=1);

namespace App\Application\Shared\Port;

interface TransactionInterface
{
    /** @template T */
    public function run(callable $fn);
}
