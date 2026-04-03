<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/entry_repository.php';

$entries = getVisibleEntriesForUser($pdo, currentUserId());

$trainingTypes = [
    'Locker',
    'Intervall',
    'Tempolauf',
    'Langer Lauf',
    'Regeneration',
    'Wettkampf',
    'Alternativtraining'
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meine Einheiten - Trainingsbereich</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
</head>
<body>
    <div class="container wide">
        <h1>Meine Einheiten</h1>

        <?php if (isset($_GET['created'])): ?>
            <p style="color:green;">Eintrag wurde gespeichert.</p>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <p style="color:green;">Eintrag wurde aktualisiert.</p>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <p style="color:green;">Eintrag wurde gelöscht.</p>
        <?php endif; ?>

        <?php if (isset($_GET['imported'])): ?>
            <p style="color:green;">Strava-Läufe wurden importiert.</p>
        <?php endif; ?>

        <?php if (isset($_GET['quickupdated'])): ?>
            <p style="color:green;">Eintrag wurde direkt aktualisiert.</p>
        <?php endif; ?>

        <?php if (isset($_GET['quickerror'])): ?>
            <p style="color:red;">Direktes Speichern war nicht möglich.</p>
        <?php endif; ?>

        <p>
            <a class="button" href="/training/entry_form.php">Neue Einheit</a>
            <a class="button" href="/training/dashboard.php">Dashboard</a>
            <a class="button" href="/training/strava_import.php">Strava-Import</a>
            <a class="button" href="/training/export_entries.php">CSV-Export</a>
        </p>

        <?php if (!$entries): ?>
            <p>Noch keine Einträge vorhanden.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Titel</th>
                            <th>Typ</th>
                            <th>km</th>
                            <th>Min</th>
                            <th>RPE</th>
                            <th>Fitness</th>
                            <th>Quelle</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <?php $formId = 'quick-form-' . (int)$entry['id']; ?>
                            <tr>
                                <td><?= htmlspecialchars($entry['activity_date']) ?></td>

                                <td>
                                    <?= htmlspecialchars($entry['title']) ?>
                                    <?php if (!empty($entry['notes'])): ?>
                                        <br><small><?= nl2br(htmlspecialchars($entry['notes'])) ?></small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <select name="training_type" form="<?= $formId ?>">
                                        <option value="">Wählen</option>
                                        <?php foreach ($trainingTypes as $type): ?>
                                            <option value="<?= htmlspecialchars($type) ?>" <?= (($entry['training_type'] ?? '') === $type) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($type) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td><?= $entry['distance_km'] !== null ? htmlspecialchars(number_format((float)$entry['distance_km'], 2, ',', '.')) : '-' ?></td>
                                <td><?= $entry['duration_min'] !== null ? htmlspecialchars((string)$entry['duration_min']) : '-' ?></td>

                                <td>
                                    <input
                                        type="range"
                                        name="rpe"
                                        form="<?= $formId ?>"
                                        min="1"
                                        max="10"
                                        value="<?= $entry['rpe'] !== null ? (int)$entry['rpe'] : 5 ?>"
                                        oninput="this.nextElementSibling.textContent = this.value"
                                    >
                                    <span><?= $entry['rpe'] !== null ? (int)$entry['rpe'] : 5 ?></span>
                                </td>

                                <td>
                                    <input
                                        type="range"
                                        name="fitness_feeling"
                                        form="<?= $formId ?>"
                                        min="1"
                                        max="10"
                                        value="<?= $entry['fitness_feeling'] !== null ? (int)$entry['fitness_feeling'] : 5 ?>"
                                        oninput="this.nextElementSibling.textContent = this.value"
                                    >
                                    <span><?= $entry['fitness_feeling'] !== null ? (int)$entry['fitness_feeling'] : 5 ?></span>
                                </td>

                                <td><?= htmlspecialchars($entry['source']) ?></td>

                                <td class="actions-cell">
                                    <form id="<?= $formId ?>" method="post" action="/training/update_quick_entry.php" class="inline-form">
                                        <input type="hidden" name="id" value="<?= (int)$entry['id'] ?>">
                                        <button type="submit" class="action-link-button">Speichern</button>
                                    </form>

                                    <a href="/training/edit_entry.php?id=<?= (int)$entry['id'] ?>">Details</a><br>
                                    <a href="/training/delete_entry.php?id=<?= (int)$entry['id'] ?>" onclick="return confirm('Eintrag wirklich löschen?');">Löschen</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>