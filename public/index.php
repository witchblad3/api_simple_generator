<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\App;
use App\Http\Request;

$app = App::create();
$request = Request::fromGlobals();

$response = $app->handle($request);
$response->send();
