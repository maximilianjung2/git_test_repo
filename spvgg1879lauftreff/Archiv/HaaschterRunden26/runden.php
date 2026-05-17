<?php
/**
 * Live-Runden-Tracker für die Haaschter Runden 2026.
 *
 * Zeigt alle Teilnehmer mit Runden, Kilometern und Plus/Minus-Buttons.
 * Kein Admin-Passwort nötig – offen für alle.
 */

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
        $row['typ_label'] = ($row['Typ'] == 2) ? 'Walken' : 'Laufen';
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
<meta name="theme-color" content="#1e3a8a" />
<title>Rundenübersicht – Haaschter Runden 2026</title>
<link rel="stylesheet" href="/assets/css/lauftreff-base.css">
<link rel="stylesheet" href="/assets/css/public.css">
<style>
    .tracker-page { padding-top: var(--space-5); }
    .tracker-page h1 {
        text-align: center;
        margin-bottom: var(--space-2);
    }
    .tracker-subtitle {
        text-align: center;
        color: var(--muted);
        font-size: 0.95rem;
        margin-bottom: var(--space-5);
    }
    .tracker-card {
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }
    .tracker-table {
        width: 100%;
        border-collapse: collapse;
    }
    .tracker-table th,
    .tracker-table td {
        text-align: left;
        padding: var(--space-3) var(--space-4);
        border-bottom: 1px solid var(--border);
    }
    .tracker-table th {
        background: var(--blue-dark);
        color: #fff;
        font-weight: 600;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .tracker-table td {
        font-size: 0.95rem;
        vertical-align: middle;
    }
    .tracker-table tbody tr:hover {
        background: var(--border-soft);
    }
    .tracker-table .km {
        font-weight: 700;
        color: var(--blue);
        font-variant-numeric: tabular-nums;
    }
    .tracker-table .runden {
        font-weight: 700;
        font-size: 1.1rem;
        text-align: center;
    }
    .badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-laufen {
        background: #dbeafe;
        color: #1e40af;
    }
    .badge-walken {
        background: #dcfce7;
        color: #166534;
    }
    .btn-group {
        display: inline-flex;
        gap: var(--space-2);
    }
    .btn-round {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 1.3rem;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.1s ease, opacity 0.15s ease;
        color: #fff;
        line-height: 1;
    }
    .btn-round:hover { transform: scale(1.08); }
    .btn-round:active { transform: scale(0.95); }
    .btn-round.plus { background: #16a34a; }
    .btn-round.minus { background: var(--red); }
    .tracker-sum {
        background: var(--border-soft);
        font-weight: 700;
    }
    .tracker-sum td {
        border-top: 2px solid var(--border);
        border-bottom: none;
        font-size: 1.05rem;
    }
    .empty-state {
        text-align: center;
        color: var(--muted);
        padding: var(--space-6) 0;
    }

    /* Mobile */
    @media (max-width: 640px) {
        .tracker-table th,
        .tracker-table td { padding: var(--space-2) var(--space-3); font-size: 0.9rem; }
        .btn-round { width: 44px; height: 44px; font-size: 1.4rem; }
        .tracker-table .runden { font-size: 1.2rem; }
    }
</style>
</head>
<body>

<header class="public-topbar">
    <div class="public-topbar-inner">
        <a href="/" class="public-brand">Spvgg. Hainstadt Lauftreff</a>
        <a href="index.html" class="btn btn-ghost btn-sm">← Zurück</a>
    </div>
</header>

<main class="public-page">
    <div class="container">
        <div class="tracker-page">
            <h1>Rundenübersicht</h1>
            <p class="tracker-subtitle">Haaschter Runden 2026 · eine Runde = 6,7 km</p>

            <?php if (count($teilnehmer) > 0): ?>
            <div class="tracker-card">
                <div style="overflow-x: auto;">
                    <table class="tracker-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Disziplin</th>
                                <th style="text-align:center;">Runden</th>
                                <th style="text-align:right;">km</th>
                                <th style="text-align:center;">Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teilnehmer as $row):
                                $gesamtRunden += $row['runden'];
                                $gesamtKm     += $row['km'];
                                $badgeClass = ($row['Typ'] == 2) ? 'badge-walken' : 'badge-laufen';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['Vorname'] . ' ' . $row['Name']) ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= $row['typ_label'] ?></span></td>
                                <td class="runden"><?= $row['runden'] ?></td>
                                <td class="km" style="text-align:right;"><?= number_format($row['km'], 2, ',', '.') ?></td>
                                <td style="text-align:center;">
                                    <div class="btn-group">
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>" />
                                            <input type="hidden" name="action" value="plus" />
                                            <button type="submit" class="btn-round plus" aria-label="Runde erhöhen">+</button>
                                        </form>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>" />
                                            <input type="hidden" name="action" value="minus" />
                                            <button type="submit" class="btn-round minus" aria-label="Runde verringern">−</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="tracker-sum">
                                <td colspan="2">Gesamt</td>
                                <td style="text-align:center;"><?= $gesamtRunden ?></td>
                                <td class="km" style="text-align:right;"><?= number_format($gesamtKm, 2, ',', '.') ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
                <div class="card empty-state">
                    <p>Noch keine Teilnehmer angemeldet.</p>
                    <a href="anmeldung.html" class="btn btn-primary" style="margin-top: var(--space-4);">Jetzt anmelden →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer class="public-footer">
    <p>&copy; Spvgg. 1879 Hainstadt — Lauftreff</p>
</footer>

</body>
</html>
<?php
$conn->close();
