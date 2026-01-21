<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\TransactionController;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Redis;

// Load Env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Prometheus Setup with Redis
$redisHost = $_ENV['REDIS_HOST'] ?? 'redis';
$registry = null;
try {
    Redis::setDefaultOptions(['host' => $redisHost, 'port' => 6379]);
    $adapter = new Redis();
    $registry = CollectorRegistry::getDefault();
} catch (Exception $e) {
    // Fallback if Redis not available - metrics won't work but app will
}

// Start timing for latency measurement
$startTime = microtime(true);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Determine URI without query string
$uriCtx = strtok($uri, '?');

// Handle metrics endpoint FIRST before setting JSON content type
if ($uriCtx === '/api/metrics' && $method === 'GET') {
    try {
        if ($registry === null) {
            throw new Exception("Redis not available");
        }
        $renderer = new RenderTextFormat();
        $result = $renderer->render($registry->getMetricFamilySamples());
        header('Content-Type: ' . RenderTextFormat::MIME_TYPE);
        echo $result;
    } catch (Exception $e) {
        header('Content-Type: text/plain');
        http_response_code(500);
        echo "# Metrics Error: " . $e->getMessage() . "\n";
    }
    exit;
}

// Set JSON content type for all other API endpoints
header("Content-Type: application/json; charset=UTF-8");

// Simple Router
$authController = new AuthController();
$transactionController = new TransactionController();

$statusCode = 200;
$endpoint = 'unknown';

try {
    if ($uriCtx === '/api/register' && $method === 'POST') {
        $endpoint = 'register';
        $authController->register();
    } elseif ($uriCtx === '/api/login' && $method === 'POST') {
        $endpoint = 'login';
        $authController->login();
    } elseif ($uriCtx === '/api/transactions' && $method === 'GET') {
        $endpoint = 'transactions_list';
        $transactionController->index();
    } elseif ($uriCtx === '/api/transactions' && $method === 'POST') {
        $endpoint = 'transactions_create';
        $transactionController->store();
    } elseif (preg_match('/^\/api\/transactions\/(\d+)$/', $uriCtx, $matches)) {
        $id = $matches[1];
        if ($method === 'PUT') {
            $endpoint = 'transactions_update';
            $transactionController->update($id);
        } elseif ($method === 'DELETE') {
            $endpoint = 'transactions_delete';
            $transactionController->destroy($id);
        }
    } elseif ($uriCtx === '/api/summary' && $method === 'GET') {
        $endpoint = 'summary';
        $transactionController->summary();
    } else {
        $endpoint = 'not_found';
        $statusCode = 404;
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    $statusCode = 500;
    http_response_code(500);
    echo json_encode(['message' => 'Server error']);
}

// Record metrics after request completes
if ($registry !== null) {
    try {
        $duration = microtime(true) - $startTime;

        // Get or create counter for requests
        $counter = $registry->getOrRegisterCounter(
            'http',
            'requests_total',
            'Total HTTP requests',
            ['endpoint', 'method', 'status']
        );
        $counter->incBy(1, [$endpoint, $method, (string) $statusCode]);

        // Get or create histogram for latency
        $histogram = $registry->getOrRegisterHistogram(
            'http',
            'request_duration_seconds',
            'HTTP request duration in seconds',
            ['endpoint', 'method'],
            [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10]
        );
        $histogram->observe($duration, [$endpoint, $method]);
    } catch (Exception $e) {
        // Silently fail if metrics can't be recorded
    }
}
