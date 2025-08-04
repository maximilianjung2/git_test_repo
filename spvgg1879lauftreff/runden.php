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

// Verarbeitung Plus/Minus Buttons
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id     = intval($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id > 0) {
        if ($action === 'plus') {
            $conn->query("UPDATE haaschterrunden2025_teilnehmer SET runden = runden + 1 WHERE id = $id");
        } elseif ($action === 'minus') {
            $conn->query("UPDATE haaschterrunden2025_teilnehmer SET runden = GREATEST(runden - 1, 0) WHERE id = $id");
        }
    }
    header("Location: runden.php");
    exit;
}

// Teilnehmer laden
$sql    = "SELECT id, Vorname, Name, Typ, runden FROM haaschterrunden2025_teilnehmer";
$result = $conn->query($sql);

$teilnehmer = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['km'] = $row['runden'] * 6.7;
        $teilnehmer[] = $row;
    }
}

// Sortieren nach km absteigend
usort($teilnehmer, function($a, $b) {
    return $b['km'] <=> $a['km'];
});

$gesamtRunden = 0;
$gesamtKm     = 0.0;
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Rundenübersicht – Haaschter Runden</title>
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
    .button {
        padding: 8px 12px;
        font-size: 1rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin: 2px;
        user-select: none;
        -webkit-user-select: none;
    }
    .plus {
        background-color: #28a745;
        color: white;
    }
    .minus {
        background-color: #dc3545;
        color: white;
    }
    .summe {
        font-weight: bold;
        background-color: #eee;
    }
    @media (max-width: 600px) {
        .button {
            padding: 10px 14px;
            font-size: 1.2rem;
        }
        th, td {
            padding: 6px;
        }
    }
</style>
</head>
<body>
<div class="container">
    <img src="/Images/spvgg_logo.png" alt="Logo" class="logo" />
    <h1>Rundenübersicht – Haaschter Runden</h1>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Vorname</th>
                    <th>Runden</th>
                    <th>km</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($teilnehmer) > 0): ?>
                <?php foreach ($teilnehmer as $row): 
                    $gesamtRunden += $row['runden'];
                    $gesamtKm += $row['km'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Name']) ?></td>
                        <td><?= htmlspecialchars($row['Vorname']) ?></td>
                        <td><?= $row['runden'] ?></td>
                        <td><?= number_format($row['km'], 2, ',', '.') ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                                <input type="hidden" name="action" value="plus" />
                                <button type="submit" class="button plus" aria-label="Runde erhöhen">+</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>" />
                                <input type="hidden" name="action" value="minus" />
                                <button type="submit" class="button minus" aria-label="Runde verringern">−</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="summe">
                    <td colspan="3">Gesamt</td>
                    <td><?= $gesamtRunden ?></td>
                    <td><?= number_format($gesamtKm, 2, ',', '.') ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="6">Keine Teilnehmer gefunden</td></tr>
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
