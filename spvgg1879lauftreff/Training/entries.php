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

$pageTitle = 'Meine Einheiten';
require __DIR__ . '/includes/header.php';
?>
<div class="container wide">
    <h1>Meine Einheiten</h1>

    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success">Eintrag wurde gespeichert.</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Eintrag wurde aktualisiert.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Eintrag wurde gelöscht.</div>
    <?php endif; ?>
    <?php if (isset($_GET['imported'])): ?>
        <div class="alert alert-success">Strava-Läufe wurden importiert.</div>
    <?php endif; ?>
    <?php if (isset($_GET['quickupdated'])): ?>
        <div class="alert alert-success">Eintrag wurde direkt aktualisiert.</div>
    <?php endif; ?>
    <?php if (isset($_GET['quickerror'])): ?>
        <div class="alert alert-error">Direktes Speichern war nicht möglich.</div>
    <?php endif; ?>

    <div class="page-actions">
        <a class="button" href="/training/entry_form.php">+ Neue Einheit</a>
        <a class="button btn-secondary" href="/training/export_entries.php">CSV-Export</a>
    </div>

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
                        <th>Ø Puls</th>
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
                                <input type="range" name="rpe" form="<?= $formId ?>" min="1" max="10"
                                    value="<?= $entry['rpe'] !== null ? (int)$entry['rpe'] : 5 ?>"
                                    oninput="this.nextElementSibling.textContent = this.value">
                                <span class="range-value"><?= $entry['rpe'] !== null ? (int)$entry['rpe'] : 5 ?></span>
                            </td>

                            <td>
                                <input type="range" name="fitness_feeling" form="<?= $formId ?>" min="1" max="10"
                                    value="<?= $entry['fitness_feeling'] !== null ? (int)$entry['fitness_feeling'] : 5 ?>"
                                    oninput="this.nextElementSibling.textContent = this.value">
                                <span class="range-value"><?= $entry['fitness_feeling'] !== null ? (int)$entry['fitness_feeling'] : 5 ?></span>
                            </td>

                            <td><?= $entry['avg_heart_rate'] !== null ? htmlspecialchars((string)$entry['avg_heart_rate']) : '-' ?></td>

                            <td><?= htmlspecialchars($entry['source']) ?></td>

                            <td class="actions-cell">
                                <form id="<?= $formId ?>" method="post" action="/training/update_quick_entry.php" class="inline-form">
                                    <input type="hidden" name="id" value="<?= (int)$entry['id'] ?>">
                                    <button type="submit" class="action-link-button">Speichern</button>
                                </form>
                                <a href="/training/edit_entry.php?id=<?= (int)$entry['id'] ?>">Details</a>
                                <a href="/training/delete_entry.php?id=<?= (int)$entry['id'] ?>" onclick="return confirm('Eintrag wirklich löschen?');">Löschen</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
