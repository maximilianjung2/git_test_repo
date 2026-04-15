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
    <meta name="theme-color" content="#1e293b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Lauftreff">
    <title><?= htmlspecialchars($pageTitle) ?> – Trainingsbereich</title>
    <link rel="stylesheet" href="/training/assets/css/<?= htmlspecialchars($cssFile) ?>">
    <link rel="manifest" href="/training/manifest.json">
    <link rel="apple-touch-icon" href="/training/assets/icons/icon-192.png">
</head>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/training/sw.js');
        });
    }
</script>
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
        <a class="<?= navClass('changelog.php', $currentScript) ?>" href="/training/changelog.php">Changelog</a>
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
<?php
$moreActive = in_array($currentScript, ['wiki.php', 'changelog.php', 'admin_users.php', 'admin_invites.php']);
?>

<!-- Bottom Sheet Overlay -->
<div class="more-overlay-backdrop" id="moreBackdrop"></div>
<div class="more-overlay-sheet" id="moreSheet">
    <div class="more-overlay-handle"></div>
    <a href="/training/wiki.php" class="more-overlay-item">
        <span class="more-overlay-icon">📖</span>
        <span>Wiki</span>
    </a>
    <a href="/training/changelog.php" class="more-overlay-item">
        <span class="more-overlay-icon">📝</span>
        <span>Changelog</span>
    </a>
    <?php if (function_exists('isAdmin') && isAdmin()): ?>
        <div class="more-overlay-sep"></div>
        <a href="/training/admin_users.php" class="more-overlay-item">
            <span class="more-overlay-icon">👥</span>
            <span>Nutzer verwalten</span>
        </a>
        <a href="/training/admin_invites.php" class="more-overlay-item">
            <span class="more-overlay-icon">✉️</span>
            <span>Einladungen</span>
        </a>
    <?php endif; ?>
</div>

<nav class="bottom-nav">
    <a href="/training/dashboard.php" class="bottom-nav-item <?= $currentScript === 'dashboard.php' ? 'active' : '' ?>">
        <span class="bottom-nav-icon">📊</span>
        <span>Dashboard</span>
    </a>
    <a href="/training/entries.php" class="bottom-nav-item <?= $currentScript === 'entries.php' ? 'active' : '' ?>">
        <span class="bottom-nav-icon">📋</span>
        <span>Einheiten</span>
    </a>
    <a href="/training/strava_import.php" class="bottom-nav-item <?= $isStravaPage ? 'active' : '' ?>">
        <span class="bottom-nav-icon">🏃</span>
        <span>Strava</span>
    </a>
    <a href="/training/entry_form.php" class="bottom-nav-item bottom-nav-new <?= $currentScript === 'entry_form.php' ? 'active' : '' ?>">
        <span class="bottom-nav-icon">＋</span>
        <span>Neu</span>
    </a>
    <button type="button" class="bottom-nav-item <?= $moreActive ? 'active' : '' ?>" id="moreBtn" style="background:none;border:none;cursor:pointer;">
        <span class="bottom-nav-icon">···</span>
        <span>Mehr</span>
    </button>
</nav>

<script>
(function () {
    const btn      = document.getElementById('moreBtn');
    const sheet    = document.getElementById('moreSheet');
    const backdrop = document.getElementById('moreBackdrop');
    if (!btn || !sheet || !backdrop) return;

    function open() {
        sheet.classList.add('open');
        backdrop.classList.add('open');
    }

    function close() {
        sheet.classList.remove('open');
        backdrop.classList.remove('open');
    }

    btn.addEventListener('click', function () {
        sheet.classList.contains('open') ? close() : open();
    });

    backdrop.addEventListener('click', close);
})();
</script>
<div class="page-main">
<?php endif; ?>
