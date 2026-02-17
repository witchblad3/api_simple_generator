<?php
declare(strict_types=1);

namespace App\Storage;

use App\Domain\RandomResult;

interface ResultRepositoryInterface
{
    public function save(RandomResult $result): void;

    public function find(string $id): ?RandomResult;
}
