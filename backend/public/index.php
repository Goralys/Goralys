<?php

require __DIR__ . "/../vendor/autoload.php";
require __DIR__ . "/../src/Kernel/bootstrap.php";

use Goralys\Shared\Exception\GoralysRuntimeException;

try {
    $kernel = bootKernel();
} catch (GoralysRuntimeException $e) {
    http_response_code(500);
    exit(1);
}
$router = $kernel->router;

(require __DIR__ . "/../API/api.php")();

const BASE_PREFIX = '/backend/public';

if (str_starts_with($_SERVER['REQUEST_URI'], BASE_PREFIX)) {
    $_SERVER['REQUEST_URI'] = str_replace(BASE_PREFIX, '', $_SERVER['REQUEST_URI']);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
