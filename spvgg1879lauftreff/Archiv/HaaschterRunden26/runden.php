<?php
/**
 * Live-Runden-Tracker für die Haaschter Runden 2026.
 *
 * Zeigt alle Teilnehmer mit Runden, Kilometern und Plus/Minus-Buttons.
 * Kein Admin-Passwort nötig – offen für alle.
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

// Verarbeitung Plus/Minus Buttons
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = intval($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id > 0) {
        if ($action === 'plus') {
            $stmt = $conn->prepare('UPDATE haaschterrunden2026_teilnehmer SET runden = runden + 1 WHERE id = ?');
        } elseif ($action === 'minus') {
            $stmt = $conn->prepare('UPDATE haaschterrunden2026_teilnehmer SET runden = GREATEST(runden - 1, 0) WHERE id = ?');
        }

        if (isset($stmt)) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header('Location: runden.php');
    exit;
}

// Teilnehmer laden
$sql    = 'SELECT id, Vorname, Name, Typ, runden FROM haaschterrunden2026_teilnehmer ORDER BY Name, Vorname';
$result = $conn->query($sql);

$teilnehmer = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['km'] = $row['runden'] * 6.7;
        $teilnehmer[] = $row;
    }
}

// Nach km absteigend sortieren
usort($teilnehmer, fn($a, $b) => $b['km'] <=> $a['km']);

$gesamtRunden = 0;
$gesamtKm     = 0.0;
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Rundenübersicht – Haaschter Runden 2026</title>
<style>
    * { box-sizing: border-box; }
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
        max-width: 80px;
        height: auto;
        margin-bottom: 10px;
    }
    h1 {
        color: #191fcb;
        font-size: clamp(1.4rem, 4vw, 2rem);
        margin-bottom: 10px;
    }
    .table-wrapper {
        overflow-x: auto;
        margin-top: 15px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px;
    }
    th, td {
        border-bottom: 1px solid #ddd;
        padding: 10px 8px;
        text-align: center;
    }
    th {
        background-color: #191fcb;
        color: white;
        font-size: 0.9rem;
    }
    td { font-size: 0.95rem; }
    .button {
        padding: 8px 14px;
        font-size: 1rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin: 2px;
        user-select: none;
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
    .back-link {
        display: inline-block;
        margin-top: 20px;
        color: #191fcb;
        text-decoration: none;
        font-size: 0.9rem;
    }
    .back-link:hover { text-decoration: underline; }

    @media (max-width: 600px) {
        .button { padding: 10px 16px; font-size: 1.1rem; }
        th, td { padding: 8px 4px; font-size: 0.85rem; }
        .logo { max-width: 60px; }
    }
</style>
</head>
<body>
<div class="container">
    <img src="/Images/spvgg_logo.png" alt="Logo" class="logo" />
    <h1>Rundenübersicht – Haaschter Runden 2026</h1>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Vorname</th>
                    <th>Name</th>
                    <th>Runden</th>
                    <th>km</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($teilnehmer) > 0): ?>
                <?php foreach ($teilnehmer as $row):
                    $gesamtRunden += $row['runden'];
                    $gesamtKm     += $row['km'];
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Vorname']) ?></td>
                        <td><?= htmlspecialchars($row['Name']) ?></td>
                        <td><?= $row['runden'] ?></td>
                        <td><?= number_format($row['km'], 2, ',', '.') ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>" />
                                <input type="hidden" name="action" value="plus" />
                                <button type="submit" class="button plus" aria-label="Runde erhöhen">+</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>" />
                                <input type="hidden" name="action" value="minus" />
                                <button type="submit" class="button minus" aria-label="Runde verringern">−</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="summe">
                    <td colspan="2">Gesamt</td>
                    <td><?= $gesamtRunden ?></td>
                    <td><?= number_format($gesamtKm, 2, ',', '.') ?></td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr><td colspan="5">Noch keine Teilnehmer angemeldet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="index.html" class="back-link">← Zurück zur Übersicht</a>
</div>
</body>
</html>
<?php
$conn->close();
