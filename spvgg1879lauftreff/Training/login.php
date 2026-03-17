<?php

require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT id, username, email, password_hash, is_active
        FROM users
        WHERE username = :login OR email = :login
        LIMIT 1
    ");
    $stmt->execute(['login' => $login]);
    $user = $stmt->fetch();

    if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];

        header('Location: /training/dashboard.php');
        exit;
    } else {
        $error = 'Benutzername/E-Mail oder Passwort ist falsch.';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Trainingsbereich</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
</head>
<body>
    <div class="container">
        <h1>Trainingsbereich Login</h1>

        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <p>
                <input type="text" name="login" placeholder="Benutzername oder E-Mail" required>
            </p>
            <p>
                <input type="password" name="password" placeholder="Passwort" required>
            </p>
            <p>
                <button class="button" type="submit">Einloggen</button>
            </p>
        </form>
    </div>
</body>
</html>