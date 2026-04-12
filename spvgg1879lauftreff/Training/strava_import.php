<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/strava_client.php';

$userId = currentUserId();
$connection = getStravaConnection($pdo, $userId);
$stravaError = null;
$runs = [];
$importedIds = [];

$stravaConnectAssetPath = '/training/assets/img/strava/btn_strava_connect_with_orange_x2.png';
$stravaPoweredByAssetPath = '/training/assets/img/strava/api_logo_pwrdBy_strava_horiz_orange.png';

function renderStravaConnectCta(string $assetPath): void
{
    ?>
    <p>Um Aktivitäten aus Strava zu importieren, musst du dein Konto einmal mit Strava verbinden.</p>
    <p style="margin-top: 16px;">
        <a href="/training/strava_connect.php" aria-label="Connect with Strava">
            <img src="<?= htmlspecialchars($assetPath, ENT_QUOTES, 'UTF-8') ?>"
                alt="Connect with Strava" style="height: 48px; width: auto; display: inline-block;">
        </a>
    </p>
    <?php
}

function renderPoweredByStrava(string $assetPath): void
{
    ?>
    <div class="strava-powered">
        <img src="<?= htmlspecialchars($assetPath, ENT_QUOTES, 'UTF-8') ?>" alt="Powered by Strava">
    </div>
    <?php
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $selected = $_POST['activity_ids'] ?? [];

    if ($connection && is_array($selected) && count($selected) > 0) {
        try {
            $runs = getRecentStravaRuns($pdo, $userId, 50);
            $importedIds = getImportedStravaIds($pdo, $userId);
            $connection = getStravaConnection($pdo, $userId);
        } catch (RuntimeException $e) {
            $connection = getStravaConnection($pdo, $userId);
            $stravaError = 'Die Strava-Verbindung ist nicht mehr gültig. Bitte verbinde dein Konto erneut.';
            $runs = [];
            $importedIds = [];
        }

        if (!$stravaError) {
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
                    duration_min,
                    avg_heart_rate
                ) VALUES (
                    :user_id,
                    'strava',
                    :source_activity_id,
                    :activity_date,
                    :title,
                    :sport_type,
                    :distance_km,
                    :duration_min,
                    :avg_heart_rate
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
                        'avg_heart_rate' => $run['avg_heart_rate'],
                    ]);
                } catch (PDOException $e) {
                    // Doppelte Imports still ignorieren
                }
            }

            header('Location: /training/entries.php?imported=1');
            exit;
        }
    }
}

if ($connection && !$stravaError) {
    try {
        $runs = getRecentStravaRuns($pdo, $userId, 30);
        $importedIds = getImportedStravaIds($pdo, $userId);
        $connection = getStravaConnection($pdo, $userId);
    } catch (RuntimeException $e) {
        $connection = getStravaConnection($pdo, $userId);
        $stravaError = 'Die Strava-Verbindung ist nicht mehr gültig. Bitte verbinde dein Konto erneut.';
        $runs = [];
        $importedIds = [];
    }
}

$pageTitle = 'Strava-Import';
require __DIR__ . '/includes/header.php';
?>
<div class="container wide">
    <h1>Strava-Import</h1>

    <?php if (isset($_GET['connected'])): ?>
        <div class="alert alert-success">Strava wurde erfolgreich verbunden.</div>
    <?php endif; ?>

    <?php if ($stravaError): ?>
        <div class="alert alert-error"><?= htmlspecialchars($stravaError) ?></div>
        <?php renderStravaConnectCta($stravaConnectAssetPath); ?>

    <?php elseif (!$connection): ?>
        <p>Dein Strava-Konto ist noch nicht verbunden.</p>
        <?php renderStravaConnectCta($stravaConnectAssetPath); ?>

    <?php else: ?>
        <p>Wähle die Läufe aus, die du importieren möchtest.</p>

        <?php if (!$runs): ?>
            <p>Keine Läufe gefunden.</p>
        <?php else: ?>
            <form method="post">
                <?= csrfField() ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Auswahl</th>
                                <th>Datum</th>
                                <th>Titel</th>
                                <th>km</th>
                                <th>Min</th>
                                <th>Ø Puls</th>
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
                                            –
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($run['activity_date'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($run['name']) ?></td>
                                    <td><?= $run['distance_km'] !== null ? htmlspecialchars(number_format((float)$run['distance_km'], 2, ',', '.')) : '-' ?></td>
                                    <td><?= $run['duration_min'] !== null ? htmlspecialchars((string)$run['duration_min']) : '-' ?></td>
                                    <td><?= $run['avg_heart_rate'] !== null ? htmlspecialchars((string)$run['avg_heart_rate']) : '-' ?></td>
                                    <td>
                                        <?php if ($alreadyImported): ?>
                                            <span class="badge badge-gray">Importiert</span>
                                        <?php else: ?>
                                            <span class="badge badge-blue">Neu</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="form-actions" style="margin-top: 16px;">
                    <button type="submit">Ausgewählte Läufe importieren</button>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <?php renderPoweredByStrava($stravaPoweredByAssetPath); ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
