<?php
/**
 * Anmeldung für die Haaschter Runden 2026.
 *
 * Speichert Teilnehmer in der Tabelle haaschterrunden2026_teilnehmer.
 * Verwendet Prepared Statements gegen SQL-Injection.
 */

require __DIR__ . '/../../secrets.php';

$secrets = require __DIR__ . '/../../secrets.php';
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
    // Eingaben bereinigen
    $vorname = trim($_POST['vorname'] ?? '');
    $name    = trim($_POST['name'] ?? '');
    $typ_str = $_POST['disziplin'] ?? '';

    // Validierung
    if ($vorname === '' || mb_strlen($vorname) < 2) {
        die('<p>Bitte gib einen gültigen Vornamen an.</p><p><a href="anmeldung.html">Zurück</a></p>');
    }
    if ($name === '' || mb_strlen($name) < 2) {
        die('<p>Bitte gib einen gültigen Nachnamen an.</p><p><a href="anmeldung.html">Zurück</a></p>');
    }

    // Typ in Zahl umwandeln
    $typ = match ($typ_str) {
        'laufen' => 1,
        'walken' => 2,
        default  => null,
    };

    if ($typ === null) {
        die('<p>Ungültige Disziplin ausgewählt.</p><p><a href="anmeldung.html">Zurück</a></p>');
    }

    // Prepared Statement
    $stmt = $conn->prepare(
        "INSERT INTO haaschterrunden2026_teilnehmer (Name, Vorname, Typ, runden) VALUES (?, ?, ?, 0)"
    );

    if (!$stmt) {
        die('Datenbankfehler: ' . $conn->error);
    }

    $stmt->bind_param('ssi', $name, $vorname, $typ);

    if ($stmt->execute()) {
        echo "<p><strong>Danke für deine Anmeldung, " . htmlspecialchars($vorname) . " " . htmlspecialchars($name) . "!</strong></p>";
        echo '<p><a href="index.html">Zurück zur Startseite</a></p>';
        echo '<p><a href="runden.php">Zur Teilnehmerliste</a></p>';
    } else {
        echo '<p>Fehler: ' . htmlspecialchars($stmt->error) . '</p>';
        echo '<p><a href="anmeldung.html">Zurück</a></p>';
    }

    $stmt->close();
}

$conn->close();
