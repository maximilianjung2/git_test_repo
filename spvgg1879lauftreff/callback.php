<?php
// OAuth-Callback für die öffentliche Strava-Anbindung des Vereinskontos.
// Erwartet ?code=... und ?state=... von Strava nach der Authorize-Bestätigung.

session_name('lauftreff_public');
session_start();

require __DIR__ . '/includes/strava.php';
require __DIR__ . '/includes/notify.php';
$secrets = require __DIR__ . '/secrets.php';

echo "<pre>";

// 1. Code prüfen
if (empty($_GET['code'])) {
    die("❌ Kein 'code' erhalten. Diese Seite darf nur über Strava aufgerufen werden.\n");
}
$code = $_GET['code'];

// 2. State prüfen (CSRF-Schutz). Strava reicht den state, den wir in
//    strava_connect.php gesetzt haben, unverändert weiter — wenn er
//    fehlt oder nicht passt, hat jemand den Flow nicht von uns gestartet.
$expectedState = $_SESSION['strava_oauth_state'] ?? '';
$receivedState = $_GET['state'] ?? '';

if ($expectedState === '' || !hash_equals($expectedState, (string)$receivedState)) {
    notify_admin(
        'OAuth-State-Check fehlgeschlagen',
        "Beim Strava-OAuth-Callback stimmte der state-Parameter nicht.\n"
        . "Erwartet (Session): " . ($expectedState !== '' ? '<gesetzt>' : '<leer>') . "\n"
        . "Empfangen (GET):    " . ($receivedState !== '' ? '<gesetzt>' : '<leer>') . "\n\n"
        . "Mögliche Ursachen: Session abgelaufen, OAuth-Flow nicht über strava_connect.php "
        . "gestartet, oder ein CSRF-Versuch."
    );
    die("❌ Ungültiger oder fehlender state-Parameter. Bitte den Flow erneut über strava_connect.php starten.\n");
}

// State ist nur einmal gültig — sofort invalidieren.
unset($_SESSION['strava_oauth_state'], $_SESSION['strava_oauth_state_created']);

echo "✅ Code und state empfangen.\n";

// 3. Code gegen Tokens tauschen
$result = strava_exchange_code(
    $secrets['strava']['client_id'],
    $secrets['strava']['client_secret'],
    $code
);

if (!$result['ok']) {
    notify_admin(
        'Strava Token-Austausch fehlgeschlagen',
        "Beim Tausch des OAuth-Codes gegen Tokens hat Strava einen Fehler zurückgegeben.\n"
        . "HTTP-Code: {$result['http_code']}\n"
        . "Fehler:    {$result['error']}"
    );
    die("❌ Token-Austausch fehlgeschlagen (HTTP {$result['http_code']}): {$result['error']}\n");
}

echo "✅ Antwort von Strava:\n";
print_r($result['data']);

// 4. Tokens speichern (mit Backup der bisherigen Version)
$tokenFile = __DIR__ . '/strava_tokens.json';
if (!strava_save_tokens_with_backup($tokenFile, $result['data'])) {
    notify_admin(
        'Token-Datei konnte nicht geschrieben werden',
        "Schreibvorgang nach $tokenFile ist fehlgeschlagen — Verzeichnis-Rechte prüfen."
    );
    die("❌ Token-Datei konnte nicht geschrieben werden: $tokenFile\n");
}
echo "✅ Tokens erfolgreich gespeichert in $tokenFile\n";
echo "   Backup der vorherigen Version (falls vorhanden): {$tokenFile}.bak\n";
echo "</pre>";
