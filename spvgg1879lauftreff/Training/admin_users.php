<?php

require __DIR__ . '/includes/auth.php';
requireAdmin();
require __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_user_id'])) {
    verifyCsrf();
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

$pageTitle = 'Nutzerverwaltung';
require __DIR__ . '/includes/header.php';
?>
<div class="container wide">
    <h1>Nutzerverwaltung</h1>
    <p class="muted">Übersicht über registrierte Nutzer und ihren aktuellen Status.</p>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Der Nutzerstatus wurde aktualisiert.</div>
    <?php endif; ?>
    <?php if (isset($_GET['selftoggle'])): ?>
        <div class="alert alert-error">Der eigene Admin-Account kann hier nicht deaktiviert werden.</div>
    <?php endif; ?>

    <div class="page-actions">
        <a class="button" href="/training/admin_invites.php">Invite-Verwaltung</a>
    </div>

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
                        <th>Registriert</th>
                        <th>Strava</th>
                        <th>Einheiten</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars((string)$user['email']) ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge badge-blue">admin</span>
                                <?php else: ?>
                                    <span class="badge badge-gray">user</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$user['is_active'] === 1): ?>
                                    <span class="badge badge-green">aktiv</span>
                                <?php else: ?>
                                    <span class="badge badge-red">inaktiv</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $user['created_at'] ? htmlspecialchars(substr((string)$user['created_at'], 0, 10)) : '-' ?></td>
                            <td>
                                <?php if ((int)$user['has_strava'] === 1): ?>
                                    <span class="badge badge-green">ja</span>
                                <?php else: ?>
                                    <span class="badge badge-gray">nein</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$user['entry_count'] ?></td>
                            <td class="actions-cell">
                                <form method="post" class="inline-form" onsubmit="return confirm('Status dieses Nutzers ändern?');">
                                    <?= csrfField() ?>
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
<?php require __DIR__ . '/includes/footer.php'; ?>
