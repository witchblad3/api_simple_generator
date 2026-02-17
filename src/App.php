<?php
declare(strict_types=1);

namespace App;

use App\Config\Config;
use App\Controller\RandomController;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Service\RandomService;
use App\Storage\FileResultRepository;
use App\Support\HttpException;

final class App
{
    public function __construct(
        private Router $router
    ) {}

    public static function create(): self
    {
        $config = Config::fromEnv();

        $repository = new FileResultRepository($config->storageDir . DIRECTORY_SEPARATOR . 'random');
        $service = new RandomService($repository, $config->randomMin, $config->randomMax);
        $controller = new RandomController($service);

        $router = new Router();

        $router->add('GET',  '/random', [$controller, 'random']);
        $router->add('POST', '/random', [$controller, 'random']);
        $router->add('GET', '/get', [$controller, 'get']);

        return new self($router);
    }

    public function handle(Request $request): Response
    {
        try {
            $response = $this->router->dispatch($request);
            $response->headers['X-Request-Id'] = $request->requestId;

            return $response;
        } catch (HttpException $e) {
            return Response::json(
                [
                    'error' => [
                        'message' => $e->getMessage(),
                        'code' => $e->statusCode,
                        'request_id' => $request->requestId,
                        'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                    ],
                ],
                $e->statusCode,
                ['X-Request-Id' => $request->requestId]
            );
        } catch (\Throwable $e) {
            return Response::json(
                [
                    'error' => [
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'request_id' => $request->requestId,
                        'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
                    ],
                ],
                500,
                ['X-Request-Id' => $request->requestId]
            );
        }
    }
}
