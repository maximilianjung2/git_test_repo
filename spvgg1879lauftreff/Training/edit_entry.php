<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';

$trainingTypes = [
    'Locker',
    'Intervall',
    'Tempolauf',
    'Langer Lauf',
    'Regeneration',
    'Wettkampf',
    'Alternativtraining'
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT *
    FROM training_entries
    WHERE id = :id
      AND user_id = :user_id
    LIMIT 1
");
$stmt->execute([
    'id' => $id,
    'user_id' => currentUserId()
]);
$entry = $stmt->fetch();

if (!$entry) {
    die('Eintrag nicht gefunden.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activityDate = $_POST['activity_date'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $sportType = trim($_POST['sport_type'] ?? 'Run');
    $trainingType = trim($_POST['training_type'] ?? '');
    $distanceKm = $_POST['distance_km'] !== '' ? (float)$_POST['distance_km'] : null;
    $durationMin = $_POST['duration_min'] !== '' ? (int)$_POST['duration_min'] : null;
    $rpe = $_POST['rpe'] !== '' ? (int)$_POST['rpe'] : null;
    $fitnessFeeling = $_POST['fitness_feeling'] !== '' ? (int)$_POST['fitness_feeling'] : null;
    $notes = trim($_POST['notes'] ?? '');

    if (!$activityDate) {
        $error = 'Bitte ein Datum angeben.';
    } elseif ($title === '') {
        $error = 'Bitte einen Titel eingeben.';
    } elseif ($rpe !== null && ($rpe < 1 || $rpe > 10)) {
        $error = 'RPE muss zwischen 1 und 10 liegen.';
    } elseif ($fitnessFeeling !== null && ($fitnessFeeling < 1 || $fitnessFeeling > 10)) {
        $error = 'Fitnessgefühl muss zwischen 1 und 10 liegen.';
    } else {
        $update = $pdo->prepare("
            UPDATE training_entries
            SET
                activity_date = :activity_date,
                title = :title,
                sport_type = :sport_type,
                training_type = :training_type,
                distance_km = :distance_km,
                duration_min = :duration_min,
                rpe = :rpe,
                fitness_feeling = :fitness_feeling,
                notes = :notes
            WHERE id = :id
              AND user_id = :user_id
        ");

        $update->execute([
            'activity_date' => $activityDate,
            'title' => $title,
            'sport_type' => $sportType,
            'training_type' => $trainingType !== '' ? $trainingType : null,
            'distance_km' => $distanceKm,
            'duration_min' => $durationMin,
            'rpe' => $rpe,
            'fitness_feeling' => $fitnessFeeling,
            'notes' => $notes !== '' ? $notes : null,
            'id' => $id,
            'user_id' => currentUserId(),
        ]);

        header('Location: /training/entries.php?updated=1');
        exit;
    }

    $entry = array_merge($entry, [
        'activity_date' => $activityDate,
        'title' => $title,
        'sport_type' => $sportType,
        'training_type' => $trainingType,
        'distance_km' => $distanceKm,
        'duration_min' => $durationMin,
        'rpe' => $rpe,
        'fitness_feeling' => $fitnessFeeling,
        'notes' => $notes,
    ]);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einheit bearbeiten</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
</head>
<body>
    <div class="container">
        <h1>Einheit bearbeiten</h1>

        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="id" value="<?= (int)$entry['id'] ?>">

            <p>
                <input type="date" name="activity_date" value="<?= htmlspecialchars($entry['activity_date']) ?>" required>
            </p>

            <p>
                <input type="text" name="title" value="<?= htmlspecialchars($entry['title']) ?>" required>
            </p>

            <p>
                <input type="text" name="sport_type" value="<?= htmlspecialchars($entry['sport_type'] ?? 'Run') ?>">
            </p>

            <p>
                <select name="training_type">
                    <option value="">Einheitstyp wählen</option>
                    <?php foreach ($trainingTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= (($entry['training_type'] ?? '') === $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <input type="number" step="0.01" min="0" name="distance_km" value="<?= htmlspecialchars((string)($entry['distance_km'] ?? '')) ?>" placeholder="Distanz in km">
            </p>

            <p>
                <input type="number" min="0" name="duration_min" value="<?= htmlspecialchars((string)($entry['duration_min'] ?? '')) ?>" placeholder="Dauer in Minuten">
            </p>

            <p>
                <input type="number" min="1" max="10" name="rpe" value="<?= htmlspecialchars((string)($entry['rpe'] ?? '')) ?>" placeholder="RPE (1-10)">
            </p>

            <p>
                <input type="number" min="1" max="10" name="fitness_feeling" value="<?= htmlspecialchars((string)($entry['fitness_feeling'] ?? '')) ?>" placeholder="Fitnessgefühl (1-10)">
            </p>

            <p>
                <textarea name="notes" rows="5" placeholder="Notizen"><?= htmlspecialchars($entry['notes'] ?? '') ?></textarea>
            </p>

            <p>
                <button type="submit">Änderungen speichern</button>
            </p>
        </form>

        <p><a class="button" href="/training/entries.php">Zurück zur Übersicht</a></p>
    </div>
</body>
</html>