<?php
// Datenbank-Verbindungsdaten anpassen:
$servername = "database-5018019376.webspace-host.com";
$username = "dbu302398";
$password = "lauftreffhomepage";
$dbname = "dbs14323265";
// Verbindung zur Datenbank herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

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
