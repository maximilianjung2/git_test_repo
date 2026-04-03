<?php

require __DIR__ . '/includes/auth.php';
requireAdmin();
require __DIR__ . '/includes/db.php';

$statusMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_user_id'])) {
    $toggleUserId = (int)($_POST['toggle_user_id'] ?? 0);

    if ($toggleUserId > 0) {
        if ($toggleUserId === currentUserId()) {
            header('Location: /training/admin_users.php?selftoggle=1');
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE users
            SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $toggleUserId]);

        header('Location: /training/admin_users.php?updated=1');
        exit;
    }
}

$stmt = $pdo->query("
    SELECT
        u.id,
        u.username,
        u.email,
        u.is_active,
        COALESCE(u.role, 'user') AS role,
        u.created_at,
        CASE WHEN sc.user_id IS NULL THEN 0 ELSE 1 END AS has_strava,
        COUNT(te.id) AS entry_count
    FROM users u
    LEFT JOIN strava_connections sc ON sc.user_id = u.id
    LEFT JOIN training_entries te ON te.user_id = u.id AND te.is_hidden = 0
    GROUP BY u.id, u.username, u.email, u.is_active, u.role, u.created_at, sc.user_id
    ORDER BY u.created_at DESC, u.id DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Nutzer - Trainingsbereich</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
</head>
<body>
    <div class="container wide">
        <h1>Nutzerverwaltung</h1>
        <p>Übersicht über registrierte Nutzer und ihren aktuellen Status.</p>

        <?php if (isset($_GET['updated'])): ?>
            <p style="color:green;">Der Nutzerstatus wurde aktualisiert.</p>
        <?php endif; ?>

        <?php if (isset($_GET['selftoggle'])): ?>
            <p style="color:red;">Der eigene Admin-Account kann hier nicht deaktiviert werden.</p>
        <?php endif; ?>

        <p>
            <a class="button" href="/training/dashboard.php">Dashboard</a>
            <a class="button" href="/training/admin_invites.php">Invite-Verwaltung</a>
        </p>

        <?php if (!$users): ?>
            <p>Es sind noch keine Nutzer vorhanden.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Benutzername</th>
                            <th>E-Mail</th>
                            <th>Rolle</th>
                            <th>Status</th>
                            <th>Registriert seit</th>
                            <th>Strava verbunden</th>
                            <th>Anzahl Einheiten</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars((string)$user['email']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td><?= (int)$user['is_active'] === 1 ? 'aktiv' : 'inaktiv' ?></td>
                                <td><?= $user['created_at'] ? htmlspecialchars((string)$user['created_at']) : '-' ?></td>
                                <td><?= (int)$user['has_strava'] === 1 ? 'ja' : 'nein' ?></td>
                                <td><?= (int)$user['entry_count'] ?></td>
                                <td class="actions-cell">
                                    <form method="post" class="inline-form" onsubmit="return confirm('Status dieses Nutzers ändern?');">
                                        <input type="hidden" name="toggle_user_id" value="<?= (int)$user['id'] ?>">
                                        <button type="submit" class="action-link-button">
                                            <?= (int)$user['is_active'] === 1 ? 'deaktivieren' : 'aktivieren' ?>
                                        </button>
                                    </form>
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
