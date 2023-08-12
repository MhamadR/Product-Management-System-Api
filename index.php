<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use TestAssignment\Api\database;
use TestAssignment\Api\ErrorHandler;
use TestAssignment\Api\ProductController;
use TestAssignment\Api\ProductGateway;

require_once('vendor/autoload.php');

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

set_error_handler([ErrorHandler::class, 'handleError']);
set_exception_handler([ErrorHandler::class, 'handleException']);


header("Content-type: application/json; charset=UTF-8");
// Enable CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Check if it's a preflight request
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    header("HTTP/1.1 204 No Content");
    exit();
}

// Divide the Request URI string into an array of substrings based on the separator "/"
$parts = explode("/", $_SERVER["REQUEST_URI"]);

// Throw 404 code when the URI is different than "/products or "/products/"
if ($parts[2] !== "products" || (isset($parts[3]) && $parts[3] !== "")) {
    http_response_code(404);
    exit;
}

$database = new Database($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);

$gateway = new ProductGateway($database);

$controller = new ProductController($gateway);

$controller->processRequest($_SERVER["REQUEST_METHOD"]);
