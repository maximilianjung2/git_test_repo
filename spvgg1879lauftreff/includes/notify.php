<?php
/**
 * Schlanker Admin-Notification-Service.
 *
 * Schickt eine E-Mail an die in secrets.php → notifications.admin_email
 * konfigurierte Adresse. Pro Subject wird höchstens eine Mail innerhalb
 * des Throttle-Fensters verschickt (Default 1h), damit ein dauerhaft
 * kaputter Token nicht in 144 Mails am Tag resultiert.
 *
 * Idempotent + safe: schlägt mail() fehl, kein weiterer Schaden — die
 * eigentliche Funktion (z.B. Token-Refresh) entscheidet weiter selbst,
 * was sie mit dem Fehler tut.
 */

if (!function_exists('notify_admin')) {

    function notify_admin(string $subject, string $body, int $throttleSeconds = 3600): bool
    {
        $secrets = require __DIR__ . '/../secrets.php';
        $email   = $secrets['notifications']['admin_email'] ?? '';
        $from    = $secrets['notifications']['mail_from']   ?? '';

        if ($email === '' || $from === '') {
            return false; // Mail-Versand deaktiviert oder nicht konfiguriert.
        }

        // Throttle-Marker im Notify-Verzeichnis (überlebt Cron-Runs etc.)
        $markerDir = __DIR__ . '/../var/notify';
        if (!is_dir($markerDir)) {
            @mkdir($markerDir, 0755, true);
        }
        $marker = $markerDir . '/' . md5($subject);
        if (file_exists($marker) && (time() - filemtime($marker)) < $throttleSeconds) {
            return false; // Innerhalb des Throttle-Fensters → nicht erneut.
        }

        $bodyFull = $body
            . "\n\n— Automatische Nachricht von spvgg1879-lauftreff.de"
            . "\n  Zeit: "  . date('Y-m-d H:i:s')
            . "\n  Server: " . ($_SERVER['SERVER_NAME'] ?? '?')
            . "\n  Skript: " . ($_SERVER['SCRIPT_NAME'] ?? '?');

        $headers = "From: $from\r\nContent-Type: text/plain; charset=UTF-8\r\n";

        $sent = @mail($email, '[Lauftreff] ' . $subject, $bodyFull, $headers);
        if ($sent) {
            @touch($marker);
        }
        return $sent;
    }
}
