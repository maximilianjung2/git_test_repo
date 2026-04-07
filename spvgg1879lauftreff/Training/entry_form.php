<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';

$error = '';
$success = '';

$trainingTypes = [
    'Locker',
    'Intervall',
    'Tempolauf',
    'Langer Lauf',
    'Regeneration',
    'Wettkampf',
    'Alternativtraining'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activityDate = $_POST['activity_date'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $sportType = trim($_POST['sport_type'] ?? 'Run');
    $trainingType = trim($_POST['training_type'] ?? '');
    $distanceKm = $_POST['distance_km'] !== '' ? (float)$_POST['distance_km'] : null;
    $durationMin = $_POST['duration_min'] !== '' ? (int)$_POST['duration_min'] : null;
    $rpe = $_POST['rpe'] !== '' ? (int)$_POST['rpe'] : null;
    $fitnessFeeling = $_POST['fitness_feeling'] !== '' ? (int)$_POST['fitness_feeling'] : null;
    $avgHeartRate = isset($_POST['avg_heart_rate']) && $_POST['avg_heart_rate'] !== '' ? (int)$_POST['avg_heart_rate'] : null;
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
        $stmt = $pdo->prepare("
            INSERT INTO training_entries (
                user_id,
                source,
                activity_date,
                title,
                sport_type,
                training_type,
                distance_km,
                duration_min,
                rpe,
                fitness_feeling,
                avg_heart_rate,
                notes
            ) VALUES (
                :user_id,
                'manual',
                :activity_date,
                :title,
                :sport_type,
                :training_type,
                :distance_km,
                :duration_min,
                :rpe,
                :fitness_feeling,
                :avg_heart_rate,
                :notes
            )
        ");

        $stmt->execute([
            'user_id' => currentUserId(),
            'activity_date' => $activityDate,
            'title' => $title,
            'sport_type' => $sportType,
            'training_type' => $trainingType !== '' ? $trainingType : null,
            'distance_km' => $distanceKm,
            'duration_min' => $durationMin,
            'rpe' => $rpe,
            'fitness_feeling' => $fitnessFeeling,
            'avg_heart_rate' => $avgHeartRate,
            'notes' => $notes !== '' ? $notes : null,
        ]);

        header('Location: /training/entries.php?created=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neue Einheit - Trainingsbereich</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
</head>
<body>
    <div class="container">
        <h1>Neue Einheit eintragen</h1>

        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <p>
                <input type="date" name="activity_date" value="<?= htmlspecialchars($_POST['activity_date'] ?? date('Y-m-d')) ?>" required>
            </p>

            <p>
                <input type="text" name="title" placeholder="Titel, z. B. Lockerer Dauerlauf" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </p>

            <p>
                <input type="text" name="sport_type" placeholder="Sportart, z. B. Run" value="<?= htmlspecialchars($_POST['sport_type'] ?? 'Run') ?>">
            </p>

            <p>
                <select name="training_type">
                    <option value="">Einheitstyp wählen</option>
                    <?php foreach ($trainingTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= (($_POST['training_type'] ?? '') === $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <input type="number" step="0.01" min="0" name="distance_km" placeholder="Distanz in km" value="<?= htmlspecialchars($_POST['distance_km'] ?? '') ?>">
            </p>

            <p>
                <input type="number" min="0" name="duration_min" placeholder="Dauer in Minuten" value="<?= htmlspecialchars($_POST['duration_min'] ?? '') ?>">
            </p>

            <p>
                <input type="number" min="1" max="10" name="rpe" placeholder="RPE (1-10)" value="<?= htmlspecialchars($_POST['rpe'] ?? '') ?>">
            </p>

            <p>
                <input type="number" min="1" max="10" name="fitness_feeling" placeholder="Fitnessgefühl (1-10)" value="<?= htmlspecialchars($_POST['fitness_feeling'] ?? '') ?>">
            </p>

            <p>
                <input type="number" min="30" max="250" name="avg_heart_rate" placeholder="Ø Puls (bpm)" value="<?= htmlspecialchars($_POST['avg_heart_rate'] ?? '') ?>">
            </p>

            <p>
                <textarea name="notes" placeholder="Notizen" rows="5"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
            </p>

            <p>
                <button type="submit">Einheit speichern</button>
            </p>
        </form>

        <p><a class="button" href="/training/dashboard.php">Zurück zum Dashboard</a></p>
    </div>
</body>
</html>