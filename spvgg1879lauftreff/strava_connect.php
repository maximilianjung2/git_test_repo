<?php
// Startet den OAuth-Flow für die öffentliche Vereins-Strava-App.
// Generiert einen zufälligen state-Token und legt ihn in der Session
// ab — callback.php prüft ihn und wehrt damit CSRF / OAuth-Hijacking ab.
//
// Diese Datei ist per .htaccess hinter Basic-Auth gesichert; nur Admins.

session_name('lauftreff_public');
session_start();

$secrets = require __DIR__ . '/secrets.php';

$state = bin2hex(random_bytes(16));
$_SESSION['strava_oauth_state'] = $state;
$_SESSION['strava_oauth_state_created'] = time();

$params = http_build_query([
    'client_id'       => $secrets['strava']['client_id'],
    'response_type'   => 'code',
    'redirect_uri'    => 'https://spvgg1879-lauftreff.de/callback.php',
    'approval_prompt' => 'auto',
    'scope'           => 'activity:read_all',
    'state'           => $state,
]);

header('Location: https://www.strava.com/oauth/authorize?' . $params);
exit;
