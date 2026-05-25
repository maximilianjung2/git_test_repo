<?php
/**
 * AJAX-Endpoint für Live-Runden-Updates (Haaschter Runden 2025, Archiv).
 *
 * Erwartet POST-Parameter: id, runden
 * Gibt "OK" oder Fehlertext zurück.
 *
 * Credentials kommen aus ../../secrets.php; SQL läuft über Prepared
 * Statement. Sicherheitshinweis: dieser Endpoint ist absichtlich ohne
 * Auth/CSRF (offen für alle, analog zur HaaschterRunden26-Variante).
 */

$secrets = require __DIR__ . '/../../secrets.php';
$db      = $secrets['db'] ?? null;

if (!$db) {
    http_response_code(500);
    echo '[DB-Config fehlt in secrets.php]';
    exit;
}

$conn = new mysqli(
    $db['host'],
    $db['user'],
    $db['pass'],
    $db['name']
);

if ($conn->connect_error) {
    http_response_code(500);
    echo 'Verbindung fehlgeschlagen: ' . $conn->connect_error;
    exit;
}

$conn->set_charset($db['charset'] ?? 'utf8mb4');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Nur POST erlaubt';
    exit;
}

$id     = isset($_POST['id'])     ? (int)$_POST['id']     : 0;
$runden = isset($_POST['runden']) ? (int)$_POST['runden'] : -1;

if ($id <= 0 || $runden < 0) {
    http_response_code(400);
    echo 'Ungültige Parameter';
    exit;
}

$stmt = $conn->prepare('UPDATE haaschterrunden2025_teilnehmer SET Runden = ? WHERE id = ?');
if (!$stmt) {
    http_response_code(500);
    echo 'DB-Fehler: ' . htmlspecialchars($conn->error);
    exit;
}

$stmt->bind_param('ii', $runden, $id);

if ($stmt->execute()) {
    echo 'OK';
} else {
    http_response_code(500);
    echo 'DB-Fehler: ' . htmlspecialchars($stmt->error);
}

$stmt->close();
$conn->close();
