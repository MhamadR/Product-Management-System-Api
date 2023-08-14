<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use TestAssignment\src\ErrorHandler;
use TestAssignment\src\database;
use TestAssignment\src\ProductGateway;
use TestAssignment\src\ProductController;
use TestAssignment\src\ProductRepository;
use TestAssignment\src\ProductValidator;

require_once('vendor/autoload.php');

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$errorHandler = new ErrorHandler();

header("Content-type: application/json; charset=UTF-8");
// Enable CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$request = ServerRequestFactory::fromGlobals();
$response = new Response();

// Check if it's a preflight request
if ($request->getMethod() === "OPTIONS") {
    header("HTTP/1.1 204 No Content");
    exit();
}

// Divide the Request URI string into an array of substrings based on the separator "/"
$parts = explode("/", $request->getUri()->getPath());

// Throw 404 code when the URI is different than "/products or "/products/"
if ($parts[2] !== "products" || (isset($parts[3]) && $parts[3] !== "")) {
    http_response_code(404);
    exit;
}

$database = new Database($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);

$gateway = new ProductGateway($database);

$repository = new ProductRepository($gateway);

$validator = new ProductValidator($repository);

$controller = new ProductController($repository, $validator);

$response = $controller->processRequest($request, $response);
http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
