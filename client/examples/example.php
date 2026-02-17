<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'Client\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

use Client\RandomApiClient;
use Client\ClientException;

$client = new RandomApiClient('http://127.0.0.1:8000');

try {
    $generated = $client->random();
    echo "Generated: " . json_encode($generated, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    $fetched = $client->get($generated['id']);
    echo "Fetched: " . json_encode($fetched, JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (ClientException $e) {
    echo "Client error: {$e->getMessage()} (HTTP {$e->httpCode})" . PHP_EOL;
    if ($e->response !== null) {
        echo "Response: " . json_encode($e->response, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }
}
