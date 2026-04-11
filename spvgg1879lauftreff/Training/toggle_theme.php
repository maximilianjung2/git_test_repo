<?php

require __DIR__ . '/includes/auth.php';
requireLogin();

$current = $_SESSION['theme'] ?? 'modern';
$_SESSION['theme'] = ($current === 'modern') ? 'classic' : 'modern';

$referer = $_SERVER['HTTP_REFERER'] ?? '/training/dashboard.php';
header('Location: ' . $referer);
exit;
