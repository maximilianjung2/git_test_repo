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

function currentUsername(): ?string
{
    return $_SESSION['username'] ?? null;
}

function currentUserRole(): string
{
    return $_SESSION['role'] ?? 'user';
}

function isAdmin(): bool
{
    return currentUserRole() === 'admin';
}

function requireAdmin(): void
{
    requireLogin();

    if (!isAdmin()) {
        http_response_code(403);
        echo 'Zugriff verweigert.';
        exit;
    }
}
