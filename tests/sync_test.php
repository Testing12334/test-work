<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/Sync.php';
date_default_timezone_set('UTC');
$checks = 0;
function check(mixed $actual, mixed $expected, string $label): void {
    global $checks;
    $checks++;
    if ($actual !== $expected) { fwrite(STDERR, "FAIL: $label\n" . var_export($actual, true) . "\n"); exit(1); }
}
foreach (['+7 (999) 123-45-67', '8 999 123 45 67', '79991234567', '9991234567'] as $phone) check(normalizePhone($phone), '+79991234567', 'phone normalization');
foreach (['123', '', null, [], '+1 999 123 45 67', '799912345678', 'abcdef'] as $phone) check(normalizePhone($phone), null, 'invalid phone');
$orders = [
    ['name'=>'first','phone'=>'89991234567','email'=>'first@example.com','amount'=>100,'created_at'=>'2026-01-01T00:00:00Z'],
    ['name'=>'max','phone'=>'+79991234567','email'=>'max@example.com','amount'=>'5000','created_at'=>'2026-01-02T00:00:00Z'],
    ['name'=>'latest','phone'=>'9991234567','email'=>'latest@example.com','amount'=>5000,'created_at'=>'2026-01-03T00:00:00Z'],
    ['name'=>'low later','phone'=>'79991234567','amount'=>10,'created_at'=>'2026-01-04T00:00:00Z'],
    ['name'=>'other','phone'=>'8(900)000-00-00','email'=>'other@example.com','amount'=>20,'created_at'=>'2026-01-01T00:00:00Z'],
    ['phone'=>'bad'], ['phone'=>'123'],
];
$result = synchronizeOrders($orders);
check($result['stats'], ['received'=>7,'unique'=>2,'duplicates_merged'=>3,'invalid_dropped'=>2], 'stats');
check($result['clients'][1], ['name'=>'latest','phone'=>'+79991234567','email'=>'latest@example.com','amount'=>5000.0], 'max amount and later date');
check(synchronizeOrders($orders), $result, 'idempotency');
check(synchronizeOrders([]), ['clients'=>[],'stats'=>['received'=>0,'unique'=>0,'duplicates_merged'=>0,'invalid_dropped'=>0]], 'empty array');
check(synchronizeOrders(array_reverse($orders)), $result, 'order-independent winners');
echo "OK: $checks checks\n";
