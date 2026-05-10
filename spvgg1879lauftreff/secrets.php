<?php
// Zentrale Konfiguration: Strava-App + Datenbank.
// Wird von Public- und /training/-Bereich gemeinsam genutzt.
//
// Diese Datei darf NIEMALS:
//  - öffentlich erreichbar sein  (siehe .htaccess: Deny-Block)
//  - in ein Repository committed werden  (siehe .gitignore)
//  - per HTTP direkt aufgerufen werden  (gibt 403)
//
// Beim Rotieren des Strava-Secrets nur HIER ändern.

return [
    'strava' => [
        'client_id'     => '163827',
        'client_secret' => '21c8af73247d8876684acf4e36ec1fa1d38c9a67',
    ],
    'db' => [
        'host'    => 'database-5018019376.webspace-host.com',
        'name'    => 'dbs14323265',
        'user'    => 'dbu302398',
        'pass'    => 'lauftreffhomepage',
        'charset' => 'utf8mb4',
    ],
    'notifications' => [
        // E-Mail des Admins für automatische Fehler-Benachrichtigungen.
        // Leerlassen, um Mails komplett zu deaktivieren.
        'admin_email' => 'maximilianjung@live.com',
        // Absender-Adresse (muss vom Hoster akzeptiert werden — meistens
        // eine Adresse auf der eigenen Domain).
        'mail_from'   => 'noreply@spvgg1879-lauftreff.de',
    ],
];
