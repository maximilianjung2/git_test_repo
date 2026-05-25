<?php
/**
 * Anmeldung für den Faschingslauf / Neujahrslauf 2026.
 *
 * Speichert in die bestehende Tabelle haaschterrunden2025_teilnehmer
 * (mit Spalte Veranstaltung='Faschingslauf2026'), wie es schon vorher
 * der Fall war. Verwendet Prepared Statements und holt die Credentials
 * zentral aus ../secrets.php — keine hardcoded DB-Zugänge mehr.
 */

$secrets = require __DIR__ . '/../secrets.php';
$db      = $secrets['db'] ?? null;

if (!$db) {
    die('[DB-Config fehlt in secrets.php]');
}

$conn = new mysqli(
    $db['host'],
    $db['user'],
    $db['pass'],
    $db['name']
);

if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}

$conn->set_charset($db['charset'] ?? 'utf8mb4');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vorname = trim($_POST['vorname'] ?? '');
    $name    = trim($_POST['name']    ?? '');
    $typ_str = $_POST['disziplin']    ?? '';
    $distanz = $_POST['distanz']      ?? '';

    // Validierung
    if ($vorname === '' || mb_strlen($vorname) < 2) {
        die('<p>Bitte gib einen gültigen Vornamen an.</p><p><a href="anmeldung.html">Zurück</a></p>');
    }
    if ($name === '' || mb_strlen($name) < 2) {
        die('<p>Bitte gib einen gültigen Nachnamen an.</p><p><a href="anmeldung.html">Zurück</a></p>');
    }

    $typ = match ($typ_str) {
        'laufen' => 1,
        'walken' => 2,
        default  => null,
    };
    if ($typ === null) {
        die('<p>Ungültige Disziplin ausgewählt.</p><p><a href="anmeldung.html">Zurück</a></p>');
    }

    // Whitelist für Distanz — gleiche Werte wie im Formular (anmeldung.html).
    $allowedDistanzen = ['5km', '10km'];
    if (!in_array($distanz, $allowedDistanzen, true)) {
        die('<p>Ungültige Distanz ausgewählt.</p><p><a href="anmeldung.html">Zurück</a></p>');
    }

    $veranstaltung = 'Faschingslauf2026';

    $stmt = $conn->prepare(
        'INSERT INTO haaschterrunden2025_teilnehmer (Name, Vorname, Typ, Veranstaltung, Distanz)
         VALUES (?, ?, ?, ?, ?)'
    );

    if (!$stmt) {
        die('Datenbankfehler: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param('ssiss', $name, $vorname, $typ, $veranstaltung, $distanz);

    if ($stmt->execute()) {
        echo '<p><strong>Danke für deine Anmeldung, '
            . htmlspecialchars($vorname) . ' ' . htmlspecialchars($name) . '!</strong></p>';
        echo '<p><a href="Faschingslauf2026.html">Zurück zur Startseite</a></p>';
    } else {
        echo '<p>Fehler: ' . htmlspecialchars($stmt->error) . '</p>';
        echo '<p><a href="anmeldung.html">Zurück</a></p>';
    }

    $stmt->close();
}

$conn->close();
