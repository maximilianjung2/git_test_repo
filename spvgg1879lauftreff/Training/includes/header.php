<?php
$pageTitle = $pageTitle ?? 'Trainingsbereich';
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
$loggedIn = function_exists('isLoggedIn') && isLoggedIn();

$isAdminPage = in_array($currentScript, ['admin_users.php', 'admin_invites.php']);
$isStravaPage = in_array($currentScript, ['strava_import.php', 'strava_connect.php', 'strava_callback.php']);

$theme = $_SESSION['theme'] ?? 'modern';
$cssFile = $theme === 'classic' ? 'training.css' : 'training-modern.css';

function navClass(string $script, string $current): string {
    return $script === $current ? 'nav-link active' : 'nav-link';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> – Trainingsbereich</title>
    <link rel="stylesheet" href="/training/assets/css/<?= htmlspecialchars($cssFile) ?>">
</head>
<body<?= $loggedIn ? '' : ' class="auth-body"' ?>>
<?php if ($loggedIn): ?>
<nav class="main-nav">
    <a class="nav-brand" href="/training/dashboard.php">Lauftreff</a>
    <div class="nav-links">
        <a class="<?= navClass('dashboard.php', $currentScript) ?>" href="/training/dashboard.php">Dashboard</a>
        <a class="<?= navClass('entries.php', $currentScript) ?>" href="/training/entries.php">Einheiten</a>
        <a class="<?= navClass('entry_form.php', $currentScript) ?>" href="/training/entry_form.php">+ Neu</a>
        <a class="<?= $isStravaPage ? 'nav-link active' : 'nav-link' ?>" href="/training/strava_import.php">Strava</a>
        <a class="<?= navClass('wiki.php', $currentScript) ?>" href="/training/wiki.php">Wiki</a>
        <?php if (function_exists('isAdmin') && isAdmin()): ?>
        <a class="<?= $isAdminPage ? 'nav-link active' : 'nav-link' ?>" href="/training/admin_users.php">Admin</a>
        <?php endif; ?>
    </div>
    <span class="nav-user">
        <?= htmlspecialchars(currentUsername() ?? '') ?>
        &middot;
        <a href="/training/toggle_theme.php" title="Design wechseln">
            <?= $theme === 'modern' ? 'Classic' : 'Modern' ?>
        </a>
        &middot;
        <a href="/training/logout.php">Logout</a>
    </span>
</nav>
<div class="page-main">
<?php endif; ?>
