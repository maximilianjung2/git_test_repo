<?php

require __DIR__ . '/includes/db.php';

$username = 'mjung';
$password = 'max170990';

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users (username, password_hash)
    VALUES (:username, :password_hash)
");

$stmt->execute([
    'username' => $username,
    'password_hash' => $passwordHash,
]);

echo "Benutzer wurde angelegt.";