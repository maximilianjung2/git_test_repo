<?php

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$error = '';
$success = false;

$token = $_GET['token'] ?? ($_POST['token'] ?? '');

if ($token === '') {
    die('Kein Invite-Token vorhanden.');
}

$tokenHash = hash('sha256', $token);

$stmt = $pdo->prepare("
    SELECT *
    FROM invites
    WHERE token_hash = :token_hash
      AND used_at IS NULL
      AND expires_at >= NOW()
    LIMIT 1
");
$stmt->execute([
    'token_hash' => $tokenHash
]);
$invite = $stmt->fetch();

if (!$invite) {
    die('Dieser Invite-Link ist ungültig oder abgelaufen.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordRepeat = $_POST['password_repeat'] ?? '';
    $email = trim($_POST['email'] ?? '');

    if ($email !== $invite['email']) {
        $error = 'Die E-Mail-Adresse passt nicht zum Invite.';
    } elseif ($username === '') {
        $error = 'Bitte einen Benutzernamen eingeben.';
    } elseif (strlen($username) < 3) {
        $error = 'Der Benutzername muss mindestens 3 Zeichen lang sein.';
    } elseif ($password === '') {
        $error = 'Bitte ein Passwort eingeben.';
    } elseif (strlen($password) < 8) {
        $error = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
    } elseif ($password !== $passwordRepeat) {
        $error = 'Die Passwörter stimmen nicht überein.';
    } else {
        $checkStmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE username = :username OR email = :email
            LIMIT 1
        ");
        $checkStmt->execute([
            'username' => $username,
            'email' => $email,
        ]);
        $existingUser = $checkStmt->fetch();

        if ($existingUser) {
            $error = 'Benutzername oder E-Mail-Adresse ist bereits vergeben.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $insertUser = $pdo->prepare("
                INSERT INTO users (
                    username,
                    email,
                    password_hash,
                    is_active,
                    role
                ) VALUES (
                    :username,
                    :email,
                    :password_hash,
                    1,
                    'user'
                )
            ");
            $insertUser->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
            ]);

            $updateInvite = $pdo->prepare("
                UPDATE invites
                SET used_at = NOW()
                WHERE id = :id
                LIMIT 1
            ");
            $updateInvite->execute([
                'id' => $invite['id']
            ]);

            $success = true;
        }
    }
}

$pageTitle = 'Registrierung';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-box">
    <h1>Konto erstellen</h1>
    <p class="auth-sub">Eingeladen für: <strong><?= htmlspecialchars($invite['email']) ?></strong></p>

    <?php if ($success): ?>
        <div class="alert alert-success">Dein Konto wurde erfolgreich erstellt.</div>
        <div class="form-actions">
            <a class="button" href="/training/login.php">Zum Login</a>
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="form-group">
                <label for="email">E-Mail-Adresse</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($invite['email']) ?>" required readonly>
            </div>
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Passwort <span class="muted">(mind. 8 Zeichen)</span></label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_repeat">Passwort wiederholen</label>
                <input type="password" id="password_repeat" name="password_repeat" required>
            </div>
            <div class="form-actions">
                <button type="submit">Konto erstellen</button>
            </div>
        </form>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
