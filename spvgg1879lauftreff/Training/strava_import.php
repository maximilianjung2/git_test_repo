<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/strava_client.php';

$userId = currentUserId();
$connection = getStravaConnection($pdo, $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['activity_ids'] ?? [];

    if ($connection && is_array($selected) && count($selected) > 0) {
        $runs = getRecentStravaRuns($pdo, $userId, 50);
        $runsById = [];

        foreach ($runs as $run) {
            $runsById[(string)$run['id']] = $run;
        }

        $insert = $pdo->prepare("
            INSERT INTO training_entries (
                user_id,
                source,
                source_activity_id,
                activity_date,
                title,
                sport_type,
                distance_km,
                duration_min
            ) VALUES (
                :user_id,
                'strava',
                :source_activity_id,
                :activity_date,
                :title,
                :sport_type,
                :distance_km,
                :duration_min
            )
        ");

        foreach ($selected as $activityId) {
            $activityId = (string)$activityId;

            if (!isset($runsById[$activityId])) {
                continue;
            }

            $run = $runsById[$activityId];

            try {
                $insert->execute([
                    'user_id' => $userId,
                    'source_activity_id' => $run['id'],
                    'activity_date' => $run['activity_date'],
                    'title' => $run['name'],
                    'sport_type' => $run['sport_type'],
                    'distance_km' => $run['distance_km'],
                    'duration_min' => $run['duration_min'],
                ]);
            } catch (PDOException $e) {
                // Doppelte Imports still ignorieren
            }
        }

        header('Location: /training/entries.php?imported=1');
        exit;
    }
}

$runs = [];
$importedIds = [];

if ($connection) {
    $runs = getRecentStravaRuns($pdo, $userId, 30);
    $importedIds = getImportedStravaIds($pdo, $userId);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strava Import</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
</head>
<body>
    <div class="container wide">
        <h1>Strava-Import</h1>

        <?php if (isset($_GET['connected'])): ?>
            <p style="color: green;">Strava wurde erfolgreich verbunden.</p>
        <?php endif; ?>

        <?php if (!$connection): ?>
            <div style="max-width: 640px; margin: 0 auto 24px; text-align: left;">
            <p>Dein Strava-Konto ist noch nicht verbunden.</p>
                    <p>
                        Um Aktivitäten aus Strava zu importieren, musst du dein Konto einmal mit Strava verbinden.
                        Die Freigabe erfolgt direkt bei Strava.
                    </p>

                    <p style="margin-top: 16px;">
                        <a href="/training/strava_connect.php" aria-label="Connect with Strava">
                            <img
                                src="/training/assets/img/strava/btn_strava_connect_with_orange_x2.png"
                                alt="Connect with Strava"
                                style="height: 48px; width: auto; display: inline-block;"
                            >
                        </a>

                <p style="font-size: 0.95rem; color: #555;">
                    Nach der Freigabe bei Strava kannst du deine letzten Läufe hier zum Import auswählen.
                </p>
            </div>
            <p><a class="button" href="/training/dashboard.php">Zurück zum Dashboard</a></p>
        <?php else: ?>
            <p>Wähle die Läufe aus, die du importieren möchtest.</p>

            <?php if (!$runs): ?>
                <p>Keine Läufe gefunden.</p>
            <?php else: ?>
                <form method="post">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Auswahl</th>
                                    <th>Datum</th>
                                    <th>Titel</th>
                                    <th>km</th>
                                    <th>Min</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($runs as $run): ?>
                                    <?php $alreadyImported = in_array((int)$run['id'], $importedIds, true); ?>
                                    <tr>
                                        <td>
                                            <?php if (!$alreadyImported): ?>
                                                <input type="checkbox" name="activity_ids[]" value="<?= (int)$run['id'] ?>">
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($run['activity_date'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($run['name']) ?></td>
                                        <td><?= $run['distance_km'] !== null ? htmlspecialchars(number_format((float)$run['distance_km'], 2, ',', '.')) : '-' ?></td>
                                        <td><?= $run['duration_min'] !== null ? htmlspecialchars((string)$run['duration_min']) : '-' ?></td>
                                        <td><?= $alreadyImported ? 'Schon importiert' : 'Neu' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <p>
                        <button type="submit">Ausgewählte Läufe importieren</button>
                    </p>
                </form>
            <?php endif; ?>

            <p>
                <a class="button" href="/training/entries.php">Meine Einheiten</a>
                <a class="button" href="/training/dashboard.php">Dashboard</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>