<?php
declare(strict_types=1);

namespace App\Http;

final class Response
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public int $statusCode,
        public array $headers,
        public string $body
    ) {}

    /**
     * @param array<string, string> $extraHeaders
     */
    public static function json(array $data, int $statusCode = 200, array $extraHeaders = []): self
    {
        $headers = array_merge(
            [
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'no-store',
            ],
            $extraHeaders
        );

        try {
            $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            $statusCode = 500;
            $body = '{"error":{"message":"JSON encode error","code":500}}';
        }

        return new self($statusCode, $headers, $body);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }
}
