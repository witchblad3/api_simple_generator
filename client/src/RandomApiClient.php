<?php
declare(strict_types=1);

namespace Client;

final class RandomApiClient
{
    public function __construct(
        private string $baseUrl,
        private int $timeoutSec = 10,
        private int $connectTimeoutSec = 3
    ) {
        $this->baseUrl = rtrim($this->baseUrl, '/');
    }

    /** @return array{id:string,value:int,created_at:string} */
    public function random(): array
    {
        return $this->request('GET', '/random');
    }

    /** @return array{id:string,value:int,created_at:string} */
    public function get(string $id): array
    {
        return $this->request('GET', '/get?id=' . rawurlencode($id));
    }

    /** @return array<string,mixed> */
    private function request(string $method, string $path): array
    {
        $url = $this->baseUrl . $path;

        $ch = curl_init($url);
        if ($ch === false) {
            throw new ClientException('Failed to init curl');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => $this->timeoutSec,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeoutSec,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'User-Agent: RandomApiClient/1.0',
            ],
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new ClientException('Curl error: ' . $err);
        }

        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            throw new ClientException('Invalid JSON response', $status, ['raw' => $raw]);
        }

        if (!is_array($data)) {
            throw new ClientException('Invalid response shape', $status, ['raw' => $raw]);
        }

        if ($status >= 400) {
            $msg = $this->extractErrorMessage($data) ?? ('HTTP ' . $status);
            throw new ClientException($msg, $status, $data);
        }

        return $data;
    }

    private function extractErrorMessage(array $data): ?string
    {
        if (isset($data['error']['message']) && is_string($data['error']['message'])) {
            return $data['error']['message'];
        }
        if (isset($data['error']) && is_string($data['error'])) {
            return $data['error'];
        }
        return null;
    }
}
