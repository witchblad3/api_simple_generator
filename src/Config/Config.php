<?php
declare(strict_types=1);

namespace App\Config;

final class Config
{
    public function __construct(
        public string $storageDir,
        public int $randomMin,
        public int $randomMax,
    ) {}

    public static function fromEnv(): self
    {
        $root = dirname(__DIR__, 2);

        $storageDir = getenv('APP_STORAGE_DIR');
        if (!is_string($storageDir) || $storageDir === '') {
            $storageDir = $root . DIRECTORY_SEPARATOR . 'storage';
        }

        $min = getenv('RANDOM_MIN');
        $max = getenv('RANDOM_MAX');

        $randomMin = is_string($min) && $min !== '' ? (int)$min : 1;
        $randomMax = is_string($max) && $max !== '' ? (int)$max : 1_000_000;

        if ($randomMax < $randomMin) {
            [$randomMin, $randomMax] = [$randomMax, $randomMin];
        }

        return new self($storageDir, $randomMin, $randomMax);
    }
}
