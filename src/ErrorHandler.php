<?php

namespace TestAssignment\src;

use Throwable;
use ErrorException;
use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\StreamFactory;
use RuntimeException;

class ErrorHandler
{

    public function __construct(private bool $debugMode = false)
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }

    public function handleException(Throwable $exception): void
    {
        if ($this->debugMode) {
            $responseBody = json_encode([
                "code" => $exception->getCode(),
                "message" => $exception->getMessage(),
                "file" => $exception->getFile(),
                "line" => $exception->getLine()
            ]);
        } else {
            $responseBody = json_encode(['error' => [
                'code' => 500,
                'message' => 'Internal Server Error'
            ]]);
        }
        $stream = new StreamFactory();
        $response = new Response();
        $response = $response
            ->withStatus(500)
            ->withBody($stream->createStream($responseBody));

        http_response_code($response->getStatusCode());
        echo $response->getBody();
    }

    public function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline,
    ): void {
        define('DEBUG_MODE', false);
        define('LOG_FILE', dirname(__DIR__) . '/error.log');

        if ($this->debugMode) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        } else {
            $logMessage = date('d-m-Y H:i:s') . ": PHP Error: $errstr in $errfile on line $errline";
            error_log($logMessage . PHP_EOL, 3, LOG_FILE);
        }
    }
}
