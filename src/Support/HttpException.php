<?php
declare(strict_types=1);

namespace App\Support;

final class HttpException extends \RuntimeException
{
    public function __construct(
        public int $statusCode,
        string $message
    ) {
        parent::__construct($message);
    }
}
