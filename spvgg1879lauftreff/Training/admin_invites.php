<?php

require __DIR__ . '/includes/auth.php';
requireAdmin();
require __DIR__ . '/includes/db.php';

$error = '';
$successLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
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

$inviteStmt = $pdo->query("
    SELECT
        id,
        email,
        created_at,
        expires_at,
        used_at
    FROM invites
    ORDER BY created_at DESC, id DESC
");
$invites = $inviteStmt->fetchAll();

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Invites - Trainingsbereich</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
</head>
<body>
    <div class="container wide">
        <h1>Invite-Verwaltung</h1>
        <p>Neue Einladungslinks erstellen und bestehende Invites im Blick behalten.</p>

        <p>
            <a class="button" href="/training/dashboard.php">Dashboard</a>
            <a class="button" href="/training/admin_users.php">Nutzerverwaltung</a>
        </p>

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
            <p style="color:green;"><strong>Invite erstellt.</strong></p>
            <p><strong>Neuer Invite-Link:</strong></p>
            <p style="word-break: break-all;"><?= htmlspecialchars($successLink) ?></p>
        <?php endif; ?>

        <h2>Bestehende Invites</h2>

        <?php if (!$invites): ?>
            <p>Es sind noch keine Invites vorhanden.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>E-Mail</th>
                            <th>Status</th>
                            <th>Erstellt am</th>
                            <th>Gültig bis</th>
                            <th>Genutzt am</th>
                            <th>Registrierungslink</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invites as $invite): ?>
                            <?php
                            $status = 'offen';
                            if (!empty($invite['used_at'])) {
                                $status = 'genutzt';
                            } elseif (strtotime((string)$invite['expires_at']) < time()) {
                                $status = 'abgelaufen';
                            }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($invite['email']) ?></td>
                                <td><?= htmlspecialchars($status) ?></td>
                                <td><?= $invite['created_at'] ? htmlspecialchars((string)$invite['created_at']) : '-' ?></td>
                                <td><?= htmlspecialchars((string)$invite['expires_at']) ?></td>
                                <td><?= $invite['used_at'] ? htmlspecialchars((string)$invite['used_at']) : '-' ?></td>
                                <td>
                                    <?php if ($status === 'offen'): ?>
                                        <span style="word-break: break-all;">
                                            <?= htmlspecialchars($scheme . '://' . $host . '/training/register.php?token=[nur-neu-erzeugte-links-sichtbar]') ?>
                                        </span>
                                        <br><small>Aus Sicherheitsgründen wird der echte Token nur direkt nach dem Erzeugen angezeigt.</small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
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
