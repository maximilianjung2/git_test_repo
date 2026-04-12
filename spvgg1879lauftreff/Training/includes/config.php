<?php

// Lädt Schlüssel=Wert-Paare aus der .env-Datei in die Umgebung.
// Muss vor dem ersten Zugriff auf $_ENV / getenv() aufgerufen werden.
(static function (): void {
    $file = __DIR__ . '/../.env';
    if (!is_readable($file)) {
        return;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2)) + ['', ''];
        if ($key !== '' && !array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
})();

return [
    'db' => [
        'host'    => $_ENV['DB_HOST']    ?? '',
        'name'    => $_ENV['DB_NAME']    ?? '',
        'user'    => $_ENV['DB_USER']    ?? '',
        'pass'    => $_ENV['DB_PASS']    ?? '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url'     => '/training',
        'app_url'      => $_ENV['APP_URL'] ?? 'https://spvgg1879-lauftreff.de/training',
        'session_name' => 'lauftreff_training',
    ],
    'strava' => [
        'client_id'     => $_ENV['STRAVA_CLIENT_ID']     ?? '',
        'client_secret' => $_ENV['STRAVA_CLIENT_SECRET'] ?? '',
        'redirect_uri'  => $_ENV['STRAVA_REDIRECT_URI']  ?? '',
    ],
];
