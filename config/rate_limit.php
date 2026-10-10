<?php

function resume_client_ip(): string
{
    $ip = getenv('RESUME_TRUST_PROXY') === '1' ? (string)($_SERVER['HTTP_X_REAL_IP'] ?? '') : '';
    if ($ip !== '' && !filter_var($ip, FILTER_VALIDATE_IP)) {
        if (preg_match('/^\[([0-9a-f:]+)\](?::\d+)?$/i', $ip, $match)) {
            $ip = $match[1];
        } elseif (preg_match('/^([0-9.]+):\d+$/', $ip, $match)) {
            $ip = $match[1];
        }
    }
    if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    return (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function rate_limit_key(string $scope, string $value): string
{
    return hash('sha256', $scope . "\0" . strtolower(trim($value)));
}

function rate_limit_is_blocked(PDO $pdo, string $key): bool
{
    $statement = $pdo->prepare('SELECT blocked_until > NOW() FROM auth_rate_limits WHERE bucket_key = ?');
    $statement->execute([$key]);
    return (bool)$statement->fetchColumn();
}

function rate_limit_record_failure(PDO $pdo, string $key, int $maxAttempts, int $windowMinutes, int $blockMinutes): void
{
    $windowMinutes = max(1, min($windowMinutes, 1440));
    $blockMinutes = max(1, min($blockMinutes, 1440));
    $maxAttempts = max(1, min($maxAttempts, 1000));
    if (random_int(1, 100) === 1) {
        $pdo->exec('DELETE FROM auth_rate_limits WHERE window_started_at < DATE_SUB(NOW(), INTERVAL 2 DAY)');
    }
    $pdo->prepare(
        'INSERT INTO auth_rate_limits (bucket_key, attempts, window_started_at, blocked_until)
         VALUES (?, 1, NOW(), NULL)
         ON DUPLICATE KEY UPDATE
            blocked_until = IF(window_started_at < DATE_SUB(NOW(), INTERVAL ' . $windowMinutes . ' MINUTE), NULL,
                IF(attempts + 1 >= ' . $maxAttempts . ', DATE_ADD(NOW(), INTERVAL ' . $blockMinutes . ' MINUTE), blocked_until)),
            attempts = IF(window_started_at < DATE_SUB(NOW(), INTERVAL ' . $windowMinutes . ' MINUTE), 1, attempts + 1),
            window_started_at = IF(window_started_at < DATE_SUB(NOW(), INTERVAL ' . $windowMinutes . ' MINUTE), NOW(), window_started_at)'
    )->execute([$key]);
}

function rate_limit_clear(PDO $pdo, string $key): void
{
    $pdo->prepare('DELETE FROM auth_rate_limits WHERE bucket_key = ?')->execute([$key]);
}
