<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require_once('vendor/autoload.php');

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

spl_autoload_register(function ($class) {
    $classFile = __DIR__ . "/src/$class.php";
    if (file_exists($classFile)) {
        require $classFile;
    }
});

set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

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

// Throw 404 code when the URI is different than "/products" and "/add-product"
// Change to $parts[1] on deployment
if ($parts[2] !== "products" && $parts[2] !== "add-product") {
    http_response_code(404);
    exit;
}

$database = new Database($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);

$gateway = new ProductGateway($database);

$controller = new ProductController($gateway);

$controller->processRequest($_SERVER["REQUEST_METHOD"]);
