<?php
declare(strict_types=1);

namespace App\Service;

use App\Domain\RandomResult;
use App\Storage\ResultRepositoryInterface;
use App\Support\Uuid;

final class RandomService
{
    public function __construct(
        private ResultRepositoryInterface $repo,
        private int $min,
        private int $max
    ) {}

    public function generate(): RandomResult
    {
        $id = Uuid::v4();
        $value = random_int($this->min, $this->max);
        $createdAt = (new \DateTimeImmutable())->format(DATE_ATOM);

        $result = new RandomResult($id, $value, $createdAt);
        $this->repo->save($result);

        return $result;
    }

    public function getById(string $id): ?RandomResult
    {
        return $this->repo->find($id);
    }
}
