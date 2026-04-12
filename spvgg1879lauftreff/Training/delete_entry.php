<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';

// Nur POST erlauben — kein Löschen per GET/Link/Prefetch
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Methode nicht erlaubt.');
}

verifyCsrf();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("
        DELETE FROM training_entries
        WHERE id = :id
          AND user_id = :user_id
        LIMIT 1
    ");
    $stmt->execute([
        'id'      => $id,
        'user_id' => currentUserId(),
    ]);
}

header('Location: /training/entries.php?deleted=1');
exit;
