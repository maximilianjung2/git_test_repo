<?php

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, is_active, COALESCE(role, 'user') AS role
        FROM users
        WHERE username = :login OR email = :login
        LIMIT 1
    ");
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch();

    if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?: 'user';

        header('Location: /training/dashboard.php');
        exit;
    } else {
        $error = 'Benutzername/E-Mail oder Passwort ist falsch.';
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-box">
    <h1>Trainingsbereich</h1>
    <p class="auth-sub">Melde dich an, um fortzufahren.</p>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <?= csrfField() ?>
        <div class="form-group">
            <label for="login">Benutzername oder E-Mail</label>
            <input type="text" id="login" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Passwort</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-actions">
            <button type="submit">Einloggen</button>
        </div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
