<?php
/**
 * Beispiel-Konfiguration (Template).
 *
 * Kopiere diese Datei zu secrets.php und füll die echten Werte ein.
 * secrets.php darf NIEMALS ins Repository committed werden.
 */

return [
    'strava' => [
        'client_id'     => '',
        'client_secret' => '',
    ],
    'db' => [
        'host'    => '',
        'name'    => '',
        'user'    => '',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'notifications' => [
        'admin_email' => '',
        'mail_from'   => '',
    ],
    // Optional: eigener Salt für RSVP-Rate-Limit.
    // Wenn leer, wird ein interner Default verwendet.
    'rsvp_salt' => '',
];
