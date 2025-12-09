<?php
// DB-Verbindung
$servername = "database-5018019376.webspace-host.com";
$username   = "dbu302398";
$password   = "lauftreffhomepage";
$dbname     = "dbs14323265";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

$id     = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$runden = isset($_POST['runden']) ? (int)$_POST['runden'] : 0;

if ($id > 0) {
    $sql = "UPDATE haaschterrunden2025_teilnehmer SET Runden = $runden WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "OK";
    } else {
        echo "DB-Fehler: " . $conn->error;
    }
} else {
    echo "Ungültige ID";
}
$conn->close();
