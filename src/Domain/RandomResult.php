<?php
declare(strict_types=1);

namespace App\Domain;

final class RandomResult implements \JsonSerializable
{
    public function __construct(
        public string $id,
        public int $value,
        public string $createdAtIso
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'created_at' => $this->createdAtIso,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string)($data['id'] ?? ''),
            (int)($data['value'] ?? 0),
            (string)($data['created_at'] ?? '')
        );
    }
}
