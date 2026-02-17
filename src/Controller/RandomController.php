<?php
declare(strict_types=1);

namespace App\Controller;

use App\Http\Request;
use App\Http\Response;
use App\Service\RandomService;
use App\Support\HttpException;
use App\Support\Uuid;

final class RandomController
{
    public function __construct(
        private RandomService $service
    ) {}

    public function random(Request $request): Response
    {
        $result = $this->service->generate();

        return Response::json($result->jsonSerialize(), 200);
    }

    public function get(Request $request): Response
    {
        $id = $request->getQueryString('id');
        if ($id === null || $id === '') {
            throw new HttpException(400, 'Missing required query param: id');
        }

        if (!Uuid::isValid($id)) {
            throw new HttpException(400, 'Invalid id format (expected UUIDv4)');
        }

        $result = $this->service->getById($id);
        if ($result === null) {
            throw new HttpException(404, 'Result not found');
        }

        return Response::json($result->jsonSerialize(), 200);
    }
}
