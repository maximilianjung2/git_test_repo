<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';

$error = '';

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
    $distanceKm = isset($_POST['distance_km']) && $_POST['distance_km'] !== '' ? (float)$_POST['distance_km'] : null;
    $durationMin = isset($_POST['duration_min']) && $_POST['duration_min'] !== '' ? (int)$_POST['duration_min'] : null;
    $rpe = isset($_POST['rpe']) && $_POST['rpe'] !== '' ? (int)$_POST['rpe'] : null;
    $fitnessFeeling = isset($_POST['fitness_feeling']) && $_POST['fitness_feeling'] !== '' ? (int)$_POST['fitness_feeling'] : null;
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

$pageTitle = 'Neue Einheit';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <h1>Neue Einheit eintragen</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="activity_date">Datum</label>
            <input type="date" id="activity_date" name="activity_date"
                value="<?= htmlspecialchars($_POST['activity_date'] ?? date('Y-m-d')) ?>" required>
        </div>

        <div class="form-group">
            <label for="title">Titel</label>
            <input type="text" id="title" name="title"
                placeholder="z. B. Lockerer Dauerlauf"
                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="sport_type">Sportart</label>
            <input type="text" id="sport_type" name="sport_type"
                placeholder="z. B. Run"
                value="<?= htmlspecialchars($_POST['sport_type'] ?? 'Run') ?>">
        </div>

        <div class="form-group">
            <label for="training_type">Einheitstyp</label>
            <select id="training_type" name="training_type">
                <option value="">Bitte wählen</option>
                <?php foreach ($trainingTypes as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>" <?= (($_POST['training_type'] ?? '') === $type) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="distance_km">Distanz (km)</label>
            <input type="number" id="distance_km" name="distance_km" step="0.01" min="0"
                placeholder="z. B. 10.5"
                value="<?= htmlspecialchars($_POST['distance_km'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="duration_min">Dauer (Minuten)</label>
            <input type="number" id="duration_min" name="duration_min" min="0"
                placeholder="z. B. 60"
                value="<?= htmlspecialchars($_POST['duration_min'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="rpe">RPE – subjektive Anstrengung (1–10)</label>
            <input type="number" id="rpe" name="rpe" min="1" max="10"
                placeholder="1 = sehr leicht, 10 = maximal"
                value="<?= htmlspecialchars($_POST['rpe'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="fitness_feeling">Fitnessgefühl (1–10)</label>
            <input type="number" id="fitness_feeling" name="fitness_feeling" min="1" max="10"
                placeholder="1 = sehr schlecht, 10 = top"
                value="<?= htmlspecialchars($_POST['fitness_feeling'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="avg_heart_rate">Ø Puls (bpm)</label>
            <input type="number" id="avg_heart_rate" name="avg_heart_rate" min="30" max="250"
                placeholder="z. B. 148"
                value="<?= htmlspecialchars($_POST['avg_heart_rate'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="notes">Notizen</label>
            <textarea id="notes" name="notes" rows="5"
                placeholder="Tagesform, Wetter, Besonderheiten ..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit">Einheit speichern</button>
            <a class="button btn-secondary" href="/training/entries.php">Abbrechen</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
