<?php

if (session_status() === PHP_SESSION_NONE) {
    $config = require __DIR__ . '/config.php';
    session_name($config['app']['session_name']);
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: /training/login.php');
        exit;
    }
}

function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}