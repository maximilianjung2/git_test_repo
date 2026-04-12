<?php

require __DIR__ . '/includes/auth.php';
requireLogin();

$current = $_SESSION['theme'] ?? 'modern';
$_SESSION['theme'] = ($current === 'modern') ? 'classic' : 'modern';

// Nur auf bekannte interne Seiten weiterleiten — kein Open Redirect via HTTP_REFERER
$allowed = [
    'dashboard.php',
    'entries.php',
    'entry_form.php',
    'wiki.php',
    'strava_import.php',
    'admin_users.php',
    'admin_invites.php',
];

$referer  = $_SERVER['HTTP_REFERER'] ?? '';
$basename = basename(parse_url($referer, PHP_URL_PATH) ?? '');
$target   = in_array($basename, $allowed, true) ? '/training/' . $basename : '/training/dashboard.php';

header('Location: ' . $target);
exit;
