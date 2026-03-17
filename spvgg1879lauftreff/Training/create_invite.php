<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';

$error = '';
$successLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Bitte eine gültige E-Mail-Adresse eingeben.';
    } else {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $expiresAt = (new DateTime('+2 days'))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            INSERT INTO invites (
                email,
                token_hash,
                expires_at
            ) VALUES (
                :email,
                :token_hash,
                :expires_at
            )
        ");

        $stmt->execute([
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
        ]);

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $successLink = $scheme . '://' . $host . '/training/register.php?token=' . urlencode($token);
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite erstellen</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
</head>
<body>
    <div class="container">
        <h1>Invite-Link erstellen</h1>

        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <p>
                <input type="email" name="email" placeholder="E-Mail-Adresse" required>
            </p>
            <p>
                <button type="submit">Invite erzeugen</button>
            </p>
        </form>

        <?php if ($successLink): ?>
            <hr>
            <p><strong>Invite-Link:</strong></p>
            <p style="word-break: break-all;"><?= htmlspecialchars($successLink) ?></p>
        <?php endif; ?>

        <p><a class="button" href="/training/dashboard.php">Zurück zum Dashboard</a></p>
    </div>
</body>
</html>