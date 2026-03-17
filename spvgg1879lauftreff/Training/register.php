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
                    is_active
                ) VALUES (
                    :username,
                    :email,
                    :password_hash,
                    1
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
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrierung</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
</head>
<body>
    <div class="container">
        <h1>Registrierung</h1>

        <?php if ($success): ?>
            <p style="color:green;">Dein Konto wurde erfolgreich erstellt.</p>
            <p><a class="button" href="/training/login.php">Zum Login</a></p>
        <?php else: ?>
            <p>Eingeladen für: <strong><?= htmlspecialchars($invite['email']) ?></strong></p>

            <?php if ($error): ?>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <p>
                    <input type="email" name="email" value="<?= htmlspecialchars($invite['email']) ?>" required readonly>
                </p>

                <p>
                    <input type="text" name="username" placeholder="Benutzername" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </p>

                <p>
                    <input type="password" name="password" placeholder="Passwort (mind. 8 Zeichen)" required>
                </p>

                <p>
                    <input type="password" name="password_repeat" placeholder="Passwort wiederholen" required>
                </p>

                <p>
                    <button type="submit">Konto erstellen</button>
                </p>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>