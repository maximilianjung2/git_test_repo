<?php
// Test-Endpoint für den Mail-Versand der Notify-Lib.
// Geschützt per .htaccess (Basic-Auth), bei jedem Aufruf wird eine
// neue Mail ohne Throttling verschickt.

require __DIR__ . '/includes/notify.php';
$secrets = require __DIR__ . '/secrets.php';

header('Content-Type: text/html; charset=UTF-8');
echo '<!DOCTYPE html><html lang="de"><head><meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '<title>Notify-Test</title>';
echo '<style>body{font-family:system-ui,sans-serif;max-width:680px;margin:40px auto;padding:0 20px;color:#0f172a;line-height:1.55}';
echo 'pre{background:#f1f5f9;padding:14px;border-radius:8px;overflow-x:auto;font-size:0.88rem}';
echo '.ok{color:#16a34a;font-weight:600}.err{color:#dc2626;font-weight:600}.muted{color:#64748b}';
echo 'h1{border-bottom:2px solid #1e3a8a;padding-bottom:6px}</style></head><body>';
echo '<h1>Notify-Test</h1>';

// 1. Konfiguration anzeigen
$adminEmail = $secrets['notifications']['admin_email'] ?? '';
$mailFrom   = $secrets['notifications']['mail_from']   ?? '';

echo '<h2>1. Konfiguration aus secrets.php</h2><pre>';
echo "admin_email = " . ($adminEmail !== '' ? htmlspecialchars($adminEmail) : '<leer>') . "\n";
echo "mail_from   = " . ($mailFrom   !== '' ? htmlspecialchars($mailFrom)   : '<leer>') . "\n";
echo '</pre>';

if ($adminEmail === '' || $mailFrom === '') {
    echo '<p class="err">⚠️ Mindestens ein Feld ist leer — Mail-Versand ist damit deaktiviert. Beide Werte in <code>secrets.php</code> setzen.</p>';
    echo '</body></html>';
    exit;
}

// 2. PHP-mail()-Umgebung
echo '<h2>2. PHP-Mail-Umgebung</h2><pre>';
$sendmailPath = ini_get('sendmail_path');
$smtp         = ini_get('SMTP');
$smtpPort     = ini_get('smtp_port');
echo "sendmail_path = " . ($sendmailPath !== '' ? htmlspecialchars($sendmailPath) : '<nicht gesetzt>') . "\n";
echo "SMTP          = " . ($smtp         !== '' ? htmlspecialchars($smtp)         : '<nicht gesetzt>') . "\n";
echo "smtp_port     = " . ($smtpPort     !== '' ? htmlspecialchars($smtpPort)     : '<nicht gesetzt>') . "\n";
echo "mail() vorhanden = " . (function_exists('mail') ? 'ja' : 'nein — Hoster bietet mail() nicht an') . "\n";
echo '</pre>';

// 3. Test-Mail verschicken
echo '<h2>3. Test-Mail verschicken</h2>';

$subject = 'Test-Mail vom ' . date('Y-m-d H:i:s');
$body    = "Hallo!\n\n"
         . "Wenn du diese Mail liest, funktioniert die Notify-Pipeline der Lauftreff-Site korrekt — "
         . "d.h. zukünftige Fehler im Strava-Sync oder beim Token-Refresh werden dich erreichen.\n\n"
         . "Diese Test-Mail kannst du ignorieren.\n\n"
         . "URL des Test-Endpoints: " . ($_SERVER['REQUEST_URI'] ?? '?');

$sent = notify_admin($subject, $body, 0); // 0 = kein Throttle für Tests

if ($sent) {
    echo '<p class="ok">✅ PHPs <code>mail()</code> hat <code>true</code> zurückgegeben.</p>';
    echo '<p>Bedeutet: Der Webserver hat die Mail an den lokalen Mail-Transport übergeben. ';
    echo 'Das heißt aber <strong>nicht</strong> zwingend, dass sie auch im Postfach ankommt — bitte:</p>';
    echo '<ol>';
    echo '<li>Postfach von <code>' . htmlspecialchars($adminEmail) . '</code> checken (auch Spam-Ordner!).</li>';
    echo '<li>Subject: <code>[Lauftreff] ' . htmlspecialchars($subject) . '</code></li>';
    echo '<li>Falls die Mail in 2–3 Minuten nicht kommt: häufige Ursache ist eine <code>mail_from</code>-Adresse, die kein echtes Postfach auf der eigenen Domain ist.</li>';
    echo '</ol>';
} else {
    echo '<p class="err">❌ PHPs <code>mail()</code> hat <code>false</code> zurückgegeben.</p>';
    echo '<p>Wahrscheinliche Ursachen:</p>';
    echo '<ul>';
    echo '<li>Hoster blockiert <code>mail()</code> ohne SMTP-Authentifizierung.</li>';
    echo '<li><code>mail_from</code> ist nicht auf einer erlaubten Domain.</li>';
    echo '<li>Server-Mailqueue ist voll oder nicht konfiguriert.</li>';
    echo '</ul>';
    echo '<p>Was tun:</p>';
    echo '<ol>';
    echo '<li>Im Hoster-Panel prüfen, ob es einen Eintrag „Mail-Versand via PHP" gibt und ob er aktiviert ist.</li>';
    echo '<li>In <code>secrets.php</code> als <code>mail_from</code> eine Adresse setzen, für die im Hoster ein Postfach existiert (z.B. <code>lauftreff@spvgg1879.de</code>).</li>';
    echo '<li>Notfalls: auf SMTP umstellen (würde ein paar Zeilen Refactor in <code>includes/notify.php</code> kosten, mit einer Library wie PHPMailer).</li>';
    echo '</ol>';
}

echo '<h2>4. Diagnose-Tipps</h2>';
echo '<p class="muted">Diese Seite kann beliebig oft aufgerufen werden — bei jedem Aufruf wird eine neue Test-Mail verschickt (Throttle ist hier deaktiviert).</p>';
echo '<p class="muted">Wenn <code>mail()</code> true zurückgibt, die Mail aber nicht ankommt:</p>';
echo '<ul class="muted">';
echo '<li>Spam-Ordner checken.</li>';
echo '<li>Im Hoster-Mail-Log nachsehen (meist im Hoster-Panel unter „Logs" / „E-Mail-Statistik").</li>';
echo '<li><code>mail_from</code> ändern auf eine Adresse mit echtem Postfach.</li>';
echo '</ul>';

echo '</body></html>';
