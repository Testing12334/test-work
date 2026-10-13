<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/Sync.php';
date_default_timezone_set('UTC');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
    http_response_code(405); header('Allow: GET, HEAD');
    echo json_encode(['error' => 'Method not allowed']); exit;
}
if ($path === '/healthz') { echo json_encode(['status' => 'ok']); exit; }
if (!in_array($path, ['/', '/index.php', '/api/clients', '/api/sync'], true)) {
    http_response_code(404); echo json_encode(['error' => 'Not found']); exit;
}
try {
    $orders = fetchOrders(getenv('ORDERS_API_URL') ?: 'https://test.ooobs.ru/api/orders');
    echo json_encode(synchronizeOrders($orders), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log('Synchronization failed: ' . $error->getMessage());
    http_response_code(502); echo json_encode(['error' => 'Unable to synchronize orders']);
}
