<?php
declare(strict_types=1);

namespace App\Storage;

use App\Domain\RandomResult;

final class FileResultRepository implements ResultRepositoryInterface
{
    public function __construct(
        private string $baseDir
    ) {}

    public function save(RandomResult $result): void
    {
        [$dir, $path] = $this->resolvePath($result->id);

        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException('Failed to create storage directory: ' . $dir);
        }

        $tmp = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';

        $payload = json_encode(
            $result->jsonSerialize(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        $bytes = file_put_contents($tmp, $payload, LOCK_EX);
        if ($bytes === false) {
            @unlink($tmp);
            throw new \RuntimeException('Failed to write storage file');
        }

        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            throw new \RuntimeException('Failed to move temp file into place');
        }
    }

    public function find(string $id): ?RandomResult
    {
        [, $path] = $this->resolvePath($id);

        if (!is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return null;
        }

        if (!is_array($data)) {
            return null;
        }

        $result = RandomResult::fromArray($data);

        if ($result->id === '' || $result->createdAtIso === '') {
            return null;
        }

        return $result;
    }

    /**
     * @return array{0:string,1:string} [dir, filepath]
     */
    private function resolvePath(string $id): array
    {
        $safeId = preg_replace('~[^0-9a-zA-Z\-]~', '', $id) ?: $id;

        $shard = substr(hash('sha256', $safeId), 0, 2);

        $dir = rtrim($this->baseDir, '/\\') . DIRECTORY_SEPARATOR . $shard;
        $path = $dir . DIRECTORY_SEPARATOR . $safeId . '.json';

        return [$dir, $path];
    }
}
