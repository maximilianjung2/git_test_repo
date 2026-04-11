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

$pageTitle = 'Invite-Verwaltung';
require __DIR__ . '/includes/header.php';
?>
<div class="container wide">
    <h1>Invite-Verwaltung</h1>
    <p class="muted">Neue Einladungslinks erstellen und bestehende Invites im Blick behalten.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($successLink): ?>
        <div class="alert alert-success">
            <strong>Invite erstellt.</strong> Dieser Link ist 48 Stunden gültig und wird nur einmalig angezeigt:
        </div>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 16px; margin-bottom:20px; word-break:break-all; font-family:monospace; font-size:0.875rem;">
            <?= htmlspecialchars($successLink) ?>
        </div>
    <?php endif; ?>

    <div class="page-actions">
        <a class="button btn-secondary" href="/training/admin_users.php">Nutzerverwaltung</a>
    </div>

    <h2>Neuen Invite erzeugen</h2>
    <form method="post">
        <div class="form-group">
            <label for="email">E-Mail-Adresse des einzuladenden Nutzers</label>
            <input type="email" id="email" name="email" placeholder="name@beispiel.de" required>
        </div>
        <div class="form-actions">
            <button type="submit">Invite erzeugen</button>
        </div>
    </form>

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
                        $isUsed = !empty($invite['used_at']);
                        $isExpired = !$isUsed && strtotime((string)$invite['expires_at']) < time();
                        $status = $isUsed ? 'genutzt' : ($isExpired ? 'abgelaufen' : 'offen');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($invite['email']) ?></td>
                            <td>
                                <?php if ($status === 'offen'): ?>
                                    <span class="badge badge-blue">offen</span>
                                <?php elseif ($status === 'genutzt'): ?>
                                    <span class="badge badge-green">genutzt</span>
                                <?php else: ?>
                                    <span class="badge badge-red">abgelaufen</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $invite['created_at'] ? htmlspecialchars(substr((string)$invite['created_at'], 0, 10)) : '-' ?></td>
                            <td><?= htmlspecialchars(substr((string)$invite['expires_at'], 0, 10)) ?></td>
                            <td><?= $invite['used_at'] ? htmlspecialchars(substr((string)$invite['used_at'], 0, 10)) : '-' ?></td>
                            <td>
                                <?php if ($status === 'offen'): ?>
                                    <small>Nur direkt nach dem Erzeugen sichtbar.</small>
                                <?php else: ?>
                                    –
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
