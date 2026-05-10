<?php

// Lädt zuerst die zentrale secrets.php (Strava-Credentials + DB), dann
// optional die .env-Datei für Training-spezifische Werte (redirect_uri,
// app_url). secrets.php ist die kanonische Quelle für Geheimnisse;
// .env wird nur noch für Konfiguration benutzt, die ausschließlich
// den /training/-Bereich betrifft.

$secrets = (static function (): array {
    $file = __DIR__ . '/../../secrets.php';
    return is_readable($file) ? require $file : [];
})();

// .env-Loader (Fallback für Werte, die nicht in secrets.php stehen).
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
        'host'    => $secrets['db']['host']    ?? $_ENV['DB_HOST'] ?? '',
        'name'    => $secrets['db']['name']    ?? $_ENV['DB_NAME'] ?? '',
        'user'    => $secrets['db']['user']    ?? $_ENV['DB_USER'] ?? '',
        'pass'    => $secrets['db']['pass']    ?? $_ENV['DB_PASS'] ?? '',
        'charset' => $secrets['db']['charset'] ?? 'utf8mb4',
    ],
    'app' => [
        'base_url'     => '/training',
        'app_url'      => $_ENV['APP_URL'] ?? 'https://spvgg1879-lauftreff.de/training',
        'session_name' => 'lauftreff_training',
    ],
    'strava' => [
        'client_id'     => $secrets['strava']['client_id']     ?? $_ENV['STRAVA_CLIENT_ID']     ?? '',
        'client_secret' => $secrets['strava']['client_secret'] ?? $_ENV['STRAVA_CLIENT_SECRET'] ?? '',
        'redirect_uri'  => $_ENV['STRAVA_REDIRECT_URI'] ?? '',
    ],
];
