<?php
// Verbindungsdaten zentral aus secrets.php
$secrets = require __DIR__ . '/secrets.php';
$conn = new mysqli(
    $secrets['db']['host'],
    $secrets['db']['user'],
    $secrets['db']['pass'],
    $secrets['db']['name']
);

// Verbindung prüfen
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Prüfen, ob Formular abgesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Eingaben bereinigen und sichern
    $vorname = $conn->real_escape_string(trim($_POST["vorname"]));
    $name = $conn->real_escape_string(trim($_POST["name"]));
    $typ_str = $_POST["disziplin"];

    // Typ in Zahl umwandeln
    if ($typ_str === "laufen") {
        $typ = 1;
    } elseif ($typ_str === "walken") {
        $typ = 2;
    } else {
        die("Ungültiger Typ ausgewählt.");
    }

    // SQL-Query vorbereiten und ausführen
    $sql = "INSERT INTO haaschterrunden2025_teilnehmer (Name, Vorname, Typ) VALUES ('$name', '$vorname', $typ)";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Danke für deine Anmeldung, $vorname $name!</p>";
        echo '<p><a href="haaschterrunden.html">Zurück zur Startseite</a></p>';
    } else {
        echo "Fehler: " . $conn->error;
    }
}

$conn->close();
?>
