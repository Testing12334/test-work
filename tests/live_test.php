<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/Sync.php';
date_default_timezone_set('UTC');
$result = synchronizeOrders(fetchOrders('https://test.ooobs.ru/api/orders'));
if ($result['stats'] !== ['received'=>17, 'unique'=>8, 'duplicates_merged'=>5, 'invalid_dropped'=>4]) {
    throw new RuntimeException('Unexpected live statistics');
}
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), "\n";
