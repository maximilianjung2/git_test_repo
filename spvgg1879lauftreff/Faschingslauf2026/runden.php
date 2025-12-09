<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB-Verbindung
$servername = "database-5018019376.webspace-host.com";
$username   = "dbu302398";
$password   = "lauftreffhomepage";
$dbname     = "dbs14323265";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Teilnehmer laden – ACHTUNG: Veranstaltung richtig schreiben
$sql = "SELECT id, Vorname, Name, Typ, Distanz 
        FROM haaschterrunden2025_teilnehmer 
        WHERE Veranstaltung='Faschingslauf2026'";

$result = $conn->query($sql);

$teilnehmer = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $teilnehmer[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Teilnehmer – Neujahrslauf</title>
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #f0f2f5;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 20px;
        min-height: 100vh;
    }
    .container {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-width: 1000px;
        width: 100%;
        text-align: center;
    }
    .logo {
        max-width: 100px;
        height: auto;
        margin-bottom: 10px;
    }
    h1 {
        color: #191fcb;
        font-size: clamp(1.5rem, 4vw, 2rem);
        margin-bottom: 20px;
    }
    .table-wrapper {
        overflow-x: auto;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }
    th, td {
        border-bottom: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }
    th {
        background-color: #191fcb;
        color: white;
    }
    @media (max-width: 600px) {
        th, td {
            padding: 6px;
        }
    }
</style>
</head>
<body>
<div class="container">
    <img src="/Images/spvgg_logo.png" alt="Logo" class="logo" />
    <h1>Teilnehmer – Neujahrslauf 2026</h1>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Vorname</th>
                    <th>Name</th>
                    <th>Disziplin</th>
                    <th>Distanz</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($teilnehmer) > 0): ?>
                <?php foreach ($teilnehmer as $row): 
                    // Typ lesbar machen
                    if ($row['Typ'] == 1) {
                        $disziplin = "Laufen";
                    } elseif ($row['Typ'] == 2) {
                        $disziplin = "Walken";
                    } else {
                        $disziplin = "";
                    }
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Vorname']) ?></td>
                        <td><?= htmlspecialchars($row['Name']) ?></td>
                        <td><?= htmlspecialchars($disziplin) ?></td>
                        <td><?= htmlspecialchars($row['Distanz']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">Keine Teilnehmer gefunden</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php
$conn->close();
?>
