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

// ── CSRF-Schutz ──────────────────────────────────────────────────────────────

/**
 * Gibt das CSRF-Token der aktuellen Session zurück.
 * Erzeugt es einmalig, falls noch keines existiert.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Gibt ein verstecktes HTML-Input-Feld mit dem CSRF-Token zurück.
 * Verwendung in Formularen: <?= csrfField() ?>
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES) . '">';
}

/**
 * Prüft ob das gesendete CSRF-Token gültig ist.
 * Bricht die Anfrage mit HTTP 403 ab wenn nicht.
 */
function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        exit('Ungültige Anfrage (CSRF-Fehler). Bitte lade die Seite neu und versuche es erneut.');
    }
}
