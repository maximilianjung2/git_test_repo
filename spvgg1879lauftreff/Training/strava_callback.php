<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/strava_client.php';

$config = require __DIR__ . '/includes/config.php';

if (!isset($_GET['state'], $_SESSION['strava_oauth_state']) || $_GET['state'] !== $_SESSION['strava_oauth_state']) {
    die('Ungültiger OAuth-State.');
}

unset($_SESSION['strava_oauth_state']);

if (isset($_GET['error'])) {
    die('Strava-Autorisierung wurde abgebrochen oder ist fehlgeschlagen.');
}

$code = $_GET['code'] ?? '';
$scope = $_GET['scope'] ?? '';

if ($code === '') {
    die('Kein Autorisierungscode von Strava erhalten.');
}

if (strpos($scope, 'activity:read_all') === false && strpos($scope, 'activity:read') === false) {
    die('Der erforderliche Strava-Scope für Aktivitäten wurde nicht freigegeben.');
}

$ch = curl_init('https://www.strava.com/oauth/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id' => $config['strava']['client_id'],
    'client_secret' => $config['strava']['client_secret'],
    'code' => $code,
    'grant_type' => 'authorization_code',
]));

$response = curl_exec($ch);

if ($response === false) {
    die('Fehler beim Abrufen des Strava-Tokens: ' . curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($httpCode !== 200 || !is_array($data) || empty($data['access_token'])) {
    die('Ungültige Antwort beim Token-Austausch mit Strava.');
}

saveStravaConnection($pdo, currentUserId(), $data);

header('Location: /training/strava_import.php?connected=1');
exit;