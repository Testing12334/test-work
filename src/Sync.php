<?php
declare(strict_types=1);

function normalizePhone(mixed $value): ?string
{
    if (!is_string($value) && !is_int($value)) return null;
    $digits = preg_replace('/[^0-9]/', '', (string) $value);
    if (strlen($digits) === 10) $digits = '7' . $digits;
    elseif (strlen($digits) === 11 && $digits[0] === '8') $digits = '7' . substr($digits, 1);
    return strlen($digits) === 11 && $digits[0] === '7' ? '+' . $digits : null;
}

function synchronizeOrders(array $orders): array
{
    $winners = [];
    $invalid = 0;
    $duplicates = 0;
    foreach ($orders as $order) {
        $phone = normalizePhone(is_array($order) ? ($order['phone'] ?? null) : null);
        if ($phone === null) { $invalid++; continue; }
        $amount = is_numeric($order['amount'] ?? null) ? (float) $order['amount'] : 0.0;
        $timestamp = strtotime((string) ($order['created_at'] ?? ''));
        $timestamp = $timestamp === false ? PHP_INT_MIN : $timestamp;
        $candidate = [
            'client' => [
                'name' => (string) ($order['name'] ?? ''),
                'phone' => $phone,
                'email' => (string) ($order['email'] ?? ''),
                'amount' => $amount,
            ],
            'timestamp' => $timestamp,
        ];
        if (!isset($winners[$phone])) { $winners[$phone] = $candidate; continue; }
        $duplicates++;
        $previous = $winners[$phone];
        if ($amount > $previous['client']['amount'] ||
            ($amount === $previous['client']['amount'] && $timestamp > $previous['timestamp'])) {
            $winners[$phone] = $candidate;
        }
    }
    ksort($winners, SORT_STRING);
    return [
        'clients' => array_values(array_map(static fn (array $winner): array => $winner['client'], $winners)),
        'stats' => [
            'received' => count($orders), 'unique' => count($winners),
            'duplicates_merged' => $duplicates, 'invalid_dropped' => $invalid,
        ],
    ];
}

function fetchOrders(string $url): array
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\nUser-Agent: TestWork-Synchronizer/1.0\r\n",
            'timeout' => 10, 'ignore_errors' => true, 'follow_location' => 0,
        ],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $body = @file_get_contents($url, false, $context);
    $status = $http_response_header[0] ?? '';
    if ($body === false || !preg_match('/^HTTP\/\S+\s+200\b/', $status)) {
        throw new RuntimeException('Orders API is unavailable');
    }
    $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data) || !array_is_list($data)) throw new RuntimeException('Orders API must return a JSON array');
    return $data;
}
