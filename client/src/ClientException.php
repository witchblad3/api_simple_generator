<?php
declare(strict_types=1);

namespace Client;

final class ClientException extends \RuntimeException
{
    public function __construct(
        string $message,
        public int $httpCode = 0,
        public ?array $response = null
    ) {
        parent::__construct($message);
    }
}
