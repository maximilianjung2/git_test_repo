<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("
        DELETE FROM training_entries
        WHERE id = :id
          AND user_id = :user_id
        LIMIT 1
    ");
    $stmt->execute([
        'id' => $id,
        'user_id' => currentUserId(),
    ]);
}

header('Location: /training/entries.php?deleted=1');
exit;