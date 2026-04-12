<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';

verifyCsrf();
$id = (int)($_POST['id'] ?? 0);
$trainingType = trim($_POST['training_type'] ?? '');
$rpe = isset($_POST['rpe']) && $_POST['rpe'] !== '' ? (int)$_POST['rpe'] : null;
$fitnessFeeling = isset($_POST['fitness_feeling']) && $_POST['fitness_feeling'] !== '' ? (int)$_POST['fitness_feeling'] : null;

$allowedTrainingTypes = [
    '',
    'Locker',
    'Intervall',
    'Tempolauf',
    'Langer Lauf',
    'Regeneration',
    'Wettkampf',
    'Alternativtraining'
];

if ($id <= 0) {
    header('Location: /training/entries.php?quickerror=1');
    exit;
}

if (!in_array($trainingType, $allowedTrainingTypes, true)) {
    header('Location: /training/entries.php?quickerror=1');
    exit;
}

if ($rpe !== null && ($rpe < 1 || $rpe > 10)) {
    header('Location: /training/entries.php?quickerror=1');
    exit;
}

if ($fitnessFeeling !== null && ($fitnessFeeling < 1 || $fitnessFeeling > 10)) {
    header('Location: /training/entries.php?quickerror=1');
    exit;
}

$stmt = $pdo->prepare("
    UPDATE training_entries
    SET
        training_type = :training_type,
        rpe = :rpe,
        fitness_feeling = :fitness_feeling
    WHERE id = :id
      AND user_id = :user_id
    LIMIT 1
");

$stmt->execute([
    'training_type' => $trainingType !== '' ? $trainingType : null,
    'rpe' => $rpe,
    'fitness_feeling' => $fitnessFeeling,
    'id' => $id,
    'user_id' => currentUserId(),
]);

header('Location: /training/entries.php?quickupdated=1');
exit;