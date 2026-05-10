<?php
// Public-Endpoint: liefert die Lauf-km aus dem Strava-Vereinskonto.
// Strava-API-Antwort wird gecacht (TTL 10min), um Rate-Limits zu schonen
// und die Anzeige zu beschleunigen. Bei API-Fehlern wird der letzte
// gecachte Wert zurückgegeben (graceful degradation).

require __DIR__ . '/includes/strava.php';
require __DIR__ . '/includes/notify.php';
$secrets = require __DIR__ . '/secrets.php';

$tokenFile = __DIR__ . '/strava_tokens.json';
$cacheFile = __DIR__ . '/strava_km_cache.json';
$cacheTTL  = 600; // 10 Minuten

header('Content-Type: text/plain');

$forceRefresh = isset($_GET['nocache']);

// 1) Cache-Hit?
if (!$forceRefresh && file_exists($cacheFile)) {
    $age = time() - filemtime($cacheFile);
    if ($age < $cacheTTL) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached) && isset($cached['km'])) {
            echo $cached['km'];
            exit;
        }
    }
}

// 2) Fallback bei jedem Fehler: alten Cache-Wert ausliefern, sonst 503.
$fallback = function () use ($cacheFile) {
    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (is_array($cached) && isset($cached['km'])) {
            echo $cached['km'];
            exit;
        }
    }
    http_response_code(503);
    echo '0';
    exit;
};

// Tokens laden
if (!file_exists($tokenFile)) {
    notify_admin(
        'Strava-Tokens fehlen',
        "Die Datei $tokenFile existiert nicht. Re-Auth nötig (siehe wiki.php → Abschnitt 4)."
    );
    $fallback();
}
$tokens = json_decode(file_get_contents($tokenFile), true);
if (!is_array($tokens) || empty($tokens['refresh_token'])) {
    notify_admin(
        'Strava-Token-Datei kaputt',
        "Die Datei $tokenFile ist leer oder enthält ungültiges JSON. Re-Auth nötig."
    );
    $fallback();
}

// Token ggf. refreshen
if (strava_token_needs_refresh($tokens)) {
    $refresh = strava_refresh_tokens(
        $secrets['strava']['client_id'],
        $secrets['strava']['client_secret'],
        $tokens['refresh_token']
    );
    if (!$refresh['ok']) {
        notify_admin(
            'Strava Token-Refresh fehlgeschlagen',
            "Token-Refresh (kilometer.php) hat einen Fehler zurückgegeben.\n"
            . "HTTP-Code: {$refresh['http_code']}\n"
            . "Fehler:    {$refresh['error']}\n\n"
            . "Bei HTTP 400/401 ist meist eine Re-Auth über strava_connect.php nötig."
        );
        $fallback();
    }
    if (!strava_save_tokens_with_backup($tokenFile, $refresh['data'])) {
        notify_admin(
            'Token-Datei nicht schreibbar',
            "Refresh war erfolgreich, aber Schreiben nach $tokenFile schlug fehl."
        );
    }
    $tokens = $refresh['data'];
}

// Aktivitäten holen
$result = strava_api_get(
    $tokens['access_token'],
    'https://www.strava.com/api/v3/athlete/activities?per_page=50'
);
if (!$result['ok']) {
    notify_admin(
        'Strava-API nicht erreichbar',
        "Aktivitäten-Abruf ist fehlgeschlagen.\n"
        . "HTTP-Code: {$result['http_code']}\n"
        . "Fehler:    {$result['error']}"
    );
    $fallback();
}

$totalDistance = 0.0;
foreach ($result['data'] as $activity) {
    if (($activity['type'] ?? '') === 'Run') {
        $totalDistance += (float) ($activity['distance'] ?? 0);
    }
}
$km = round($totalDistance / 1000, 1);

file_put_contents(
    $cacheFile,
    json_encode(['km' => $km, 'updated_at' => time()]),
    LOCK_EX
);

echo $km;
