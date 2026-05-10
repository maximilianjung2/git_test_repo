<?php
// Interne Dokumentation für die öffentliche Strava-Integration.
// Geschützt per .htaccess (siehe Abschnitt 9).

require __DIR__ . '/includes/health.php';
$secrets = require __DIR__ . '/secrets.php';

$statusEntries = [
    health_check_token(__DIR__ . '/strava_tokens.json'),
    health_check_cache(__DIR__ . '/strava_km_cache.json', 600),
    health_check_db($secrets['db']),
];

// Worst-State für die Gesamt-Ampel oben.
$priority = ['ok' => 0, 'unknown' => 1, 'warn' => 2, 'error' => 3];
$overall  = 'ok';
foreach ($statusEntries as $e) {
    if ($priority[$e['state']] > $priority[$overall]) {
        $overall = $e['state'];
    }
}
$overallText = [
    'ok'      => 'Alles im grünen Bereich.',
    'warn'    => 'Achtung — etwas läuft nicht ganz rund.',
    'error'   => 'Problem erkannt — Eingriff nötig.',
    'unknown' => 'Status unklar.',
][$overall];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Wiki – Öffentliche Strava-Integration</title>
  <style>
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
      background-color: #f0f2f5;
      color: #222;
      line-height: 1.55;
    }
    .wrapper {
      max-width: 880px;
      margin: 32px auto;
      padding: 32px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }
    h1 {
      margin-top: 0;
      font-size: 1.8rem;
      border-bottom: 2px solid #191fcb;
      padding-bottom: 8px;
    }
    h2 {
      margin-top: 2.2rem;
      font-size: 1.3rem;
      color: #191fcb;
      border-bottom: 1px solid #e3e3e8;
      padding-bottom: 4px;
    }
    h3 {
      margin-top: 1.4rem;
      font-size: 1.05rem;
    }
    p, li { font-size: 0.96rem; }
    code, pre {
      font-family: SFMono-Regular, Menlo, Consolas, monospace;
      font-size: 0.88rem;
    }
    code {
      background: #f4f4f8;
      padding: 1px 5px;
      border-radius: 4px;
    }
    pre {
      background: #1e1e2e;
      color: #e8e8ec;
      padding: 14px 16px;
      border-radius: 8px;
      overflow-x: auto;
      line-height: 1.45;
    }
    pre code { background: transparent; padding: 0; color: inherit; }
    .toc {
      background: #f7f8fc;
      border-left: 3px solid #191fcb;
      padding: 12px 18px;
      border-radius: 4px;
      margin: 1.5rem 0;
    }
    .toc ul { margin: 6px 0 0 0; padding-left: 18px; }
    .note {
      border-left: 4px solid #f0a500;
      background: #fff7e6;
      padding: 10px 14px;
      border-radius: 4px;
      margin: 1rem 0;
    }
    .danger {
      border-left: 4px solid #d62828;
      background: #fdecec;
      padding: 10px 14px;
      border-radius: 4px;
      margin: 1rem 0;
    }
    .ok {
      border-left: 4px solid #2a9d3a;
      background: #e9f7ec;
      padding: 10px 14px;
      border-radius: 4px;
      margin: 1rem 0;
    }
    table {
      border-collapse: collapse;
      width: 100%;
      margin: 1rem 0;
    }
    th, td {
      text-align: left;
      padding: 8px 10px;
      border-bottom: 1px solid #e3e3e8;
      vertical-align: top;
    }
    th { background: #f7f8fc; }
    .muted { color: #777; font-size: 0.9rem; }
    a { color: #191fcb; }

    /* Status-Dashboard */
    .status-overall {
      margin: 1rem 0 0.4rem;
      font-size: 0.95rem;
      font-weight: 600;
    }
    .status-overall.status-ok      { color: #2a9d3a; }
    .status-overall.status-warn    { color: #c47c00; }
    .status-overall.status-error   { color: #d62828; }
    .status-overall.status-unknown { color: #555; }
    .status-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 12px;
      margin: 0.8rem 0 1.6rem;
    }
    .status-card {
      display: flex;
      gap: 12px;
      padding: 12px 14px;
      background: #f7f8fc;
      border-radius: 8px;
      border-left: 4px solid #ccc;
    }
    .status-card.status-ok      { border-left-color: #2a9d3a; }
    .status-card.status-warn    { border-left-color: #f0a500; }
    .status-card.status-error   { border-left-color: #d62828; }
    .status-card.status-unknown { border-left-color: #999; }
    .status-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin-top: 5px;
      flex-shrink: 0;
      background: #ccc;
    }
    .status-card.status-ok      .status-dot { background: #2a9d3a; }
    .status-card.status-warn    .status-dot { background: #f0a500; }
    .status-card.status-error   .status-dot { background: #d62828; }
    .status-card.status-unknown .status-dot { background: #999; }
    .status-body { flex: 1; }
    .status-title {
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #777;
      font-weight: 600;
    }
    .status-label {
      font-size: 1.05rem;
      font-weight: 600;
      margin: 2px 0 4px;
    }
    .status-detail {
      font-size: 0.85rem;
      color: #555;
      line-height: 1.4;
    }
  </style>
</head>
<body>
<div class="wrapper">

<h1>Wiki – Öffentliche Strava-Integration</h1>
<p class="muted">
  Interne Doku für den öffentlichen Bereich (<code>spvgg1879-lauftreff.de</code>).
  Beschreibt, wie die Vereins-Kilometer von Strava geholt und angezeigt werden
  und wie man die Integration im Alltag bedient.
</p>

<div class="ok">
  <strong>Zugriff geschützt:</strong> Diese Seite und
  <code>kilometer_debug.php</code> sind per HTTP-Basic-Auth über
  <code>.htaccess</code> / <code>.htpasswd</code> abgesichert
  (siehe <a href="#zugangsschutz">Abschnitt 9</a> zur Verwaltung).
</div>

<div class="status-overall status-<?php echo htmlspecialchars($overall); ?>">
  ● <?php echo htmlspecialchars($overallText); ?>
  <span class="muted" style="font-weight:normal;">
    (Stand: <?php echo date('d.m.Y H:i'); ?>)
  </span>
</div>
<div class="status-grid">
<?php foreach ($statusEntries as $entry) {
    echo render_status_card($entry);
} ?>
</div>

<div class="toc">
  <strong>Inhalt</strong>
  <ul>
    <li><a href="#ueberblick">1. Überblick</a></li>
    <li><a href="#dateien">2. Dateien &amp; Zuständigkeiten</a></li>
    <li><a href="#workflow">3. Workflow im laufenden Betrieb</a></li>
    <li><a href="#einrichtung">4. Erstmalige Einrichtung &amp; Re-Authorisierung</a></li>
    <li><a href="#secret">5. Client-Secret rotieren</a></li>
    <li><a href="#beziehung">6. Beziehung zur Member-Seite (<code>/training/</code>)</a></li>
    <li><a href="#troubleshooting">7. Troubleshooting</a></li>
    <li><a href="#sicherheit">8. Sicherheits-Hinweise</a></li>
    <li><a href="#zugangsschutz">9. Zugangsschutz verwalten (.htaccess / .htpasswd)</a></li>
    <li><a href="#cache">10. Caching von <code>kilometer.php</code></a></li>
    <li><a href="#backup">11. Token-Backup &amp; Recovery</a></li>
    <li><a href="#notify">12. Fehler-Benachrichtigungen per E-Mail</a></li>
  </ul>
</div>

<h2 id="ueberblick">1. Überblick</h2>
<p>
  Die öffentliche Vereins-Seite zeigt die kumulierten Lauf-Kilometer aller
  Aktivitäten, die im Strava-Konto des Vereins (Athlet-ID hinter der
  App-Verknüpfung) den Text <em>„Spvgg. Hainstadt“</em> im Titel haben.
  Dafür wird einmal die Strava-OAuth-Authorisierung durchlaufen, und ab dann
  hält sich das System mit Refresh-Tokens selbst frisch.
</p>
<p>
  Die Strava-App hat die Client-ID <code>163827</code> und ist bei Strava unter
  der „Authorization Callback Domain“ <code>spvgg1879-lauftreff.de</code>
  registriert. Beide Bereiche (öffentlich + <code>/training/</code>) nutzen
  dieselbe App, aber getrennte Redirect-URIs und getrennte Token-Speicher.
</p>

<h2 id="dateien">2. Dateien &amp; Zuständigkeiten</h2>
<table>
  <tr>
    <th>Datei</th>
    <th>Zweck</th>
  </tr>
  <tr>
    <td><code>callback.php</code></td>
    <td>OAuth-Callback. Wird von Strava nach der Autorisierung aufgerufen,
        tauscht den <code>code</code> gegen Tokens und schreibt sie nach
        <code>strava_tokens.json</code>. POST an Strava, mit Validierung.</td>
  </tr>
  <tr>
    <td><code>kilometer.php</code></td>
    <td>Live-Endpoint. Wird vom Frontend abgerufen, refresht das Access-Token
        bei Bedarf, holt die letzten 50 Aktivitäten und gibt die Summe als
        gerundete Kilometer (Plain-Text) zurück.</td>
  </tr>
  <tr>
    <td><code>kilometer_debug.php</code></td>
    <td>Debug-Variante mit ausführlicher Konsolen-Ausgabe und DB-Schreibung.
        Holt 365 Tage zurück, schreibt qualifizierende Aktivitäten in
        <code>strava_activities</code> und zeigt jede Stufe sichtbar.
        Nicht öffentlich verlinken.</td>
  </tr>
  <tr>
    <td><code>strava_tokens.json</code></td>
    <td>Wird automatisch angelegt von <code>callback.php</code> /
        beim Refresh durch <code>kilometer.php</code> /
        <code>kilometer_debug.php</code>. Enthält
        <code>access_token</code>, <code>refresh_token</code>,
        <code>expires_at</code>. Niemals committen.</td>
  </tr>
  <tr>
    <td><code>strava_km_cache.json</code></td>
    <td>Cache-Datei mit der zuletzt berechneten Kilometer-Summe
        (TTL 10 Min, siehe <a href="#cache">Abschnitt 10</a>).
        Wird von <code>kilometer.php</code> automatisch geschrieben.
        Bei Bedarf einfach löschen, dann wird beim nächsten Aufruf
        frisch von Strava geholt.</td>
  </tr>
  <tr>
    <td><code>secrets.php</code></td>
    <td>Zentrale Konfigurationsdatei mit Strava-Credentials und
        DB-Zugang. Wird von Public- und <code>/training/</code>-Bereich
        gemeinsam genutzt. Per <code>.htaccess</code> vor direktem
        Abruf geschützt.</td>
  </tr>
  <tr>
    <td><code>includes/strava.php</code></td>
    <td>Geteilte Strava-Client-Bibliothek (Token-Refresh,
        Code-Exchange, API-GET). Wird vom Public-Bereich UND vom
        Member-Bereich (<code>Training/includes/strava_client.php</code>)
        verwendet. Bei Strava-API-Änderungen ist nur diese Datei
        anzupassen.</td>
  </tr>
  <tr>
    <td><code>includes/health.php</code></td>
    <td>Status-Checks für das Wiki-Dashboard oben. Prüft ohne
        externe API-Calls den lokalen Zustand (Token, Cache, DB).</td>
  </tr>
  <tr>
    <td><code>includes/notify.php</code></td>
    <td>Verschickt automatische Admin-Mails bei Token- oder
        API-Fehlern. Pro Subject höchstens eine Mail pro Stunde
        (siehe <a href="#notify">Abschnitt 12</a>).</td>
  </tr>
  <tr>
    <td><code>strava_connect.php</code></td>
    <td>Startet den OAuth-Flow mit CSRF-State-Schutz. Hinter Basic-Auth.
        Sollte beim Re-Auth (siehe Abschnitt 4) der einzige Einstiegs­punkt sein.</td>
  </tr>
  <tr>
    <td><code>strava_tokens.json.bak</code></td>
    <td>Automatisch erzeugte Sicherheitskopie der vorherigen
        <code>strava_tokens.json</code>. Wird bei jedem Refresh / Re-Auth
        überschrieben (siehe <a href="#backup">Abschnitt 11</a>).</td>
  </tr>
  <tr>
    <td><code>chart.html</code>, <code>aktivitäten.html</code>,
        <code>chart_data.php</code></td>
    <td>Anzeigeseiten, die <code>kilometer.php</code> bzw.
        <code>chart_data.php</code> abfragen.</td>
  </tr>
</table>

<h2 id="workflow">3. Workflow im laufenden Betrieb</h2>
<p>
  Nach einmaliger Autorisierung läuft alles automatisch:
</p>
<ol>
  <li>Browser ruft eine Anzeigeseite auf, die <code>kilometer.php</code>
      einbettet (z. B. die Kilometer-Anzeige auf der Startseite).</li>
  <li><code>kilometer.php</code> liest <code>strava_tokens.json</code>.</li>
  <li>Falls <code>expires_at &lt;= jetzt</code>: Token-Refresh per
      <code>POST https://www.strava.com/oauth/token</code> mit
      <code>grant_type=refresh_token</code>. Neue Tokens werden
      zurückgeschrieben.</li>
  <li>Mit dem gültigen Access-Token werden Aktivitäten via
      <code>GET /api/v3/athlete/activities</code> geholt.</li>
  <li>Distanz aller Aktivitäten vom Typ <code>Run</code> wird summiert
      und als Kilometer zurückgegeben.</li>
</ol>

<div class="note">
  <strong>Strava-Token-Lebensdauer:</strong> Access-Token gilt 6 Stunden,
  Refresh-Token rotiert: bei jedem erfolgreichen Refresh schickt Strava
  einen neuen mit. Die <code>strava_tokens.json</code> wird daher regelmäßig
  überschrieben — das ist normal.
</div>

<h2 id="einrichtung">4. Erstmalige Einrichtung &amp; Re-Authorisierung</h2>
<p>
  Diesen Ablauf brauchst du beim ersten Setup, nach einem Secret-Rotate,
  oder wenn die Tokens kaputt / verloren gegangen sind.
</p>
<ol>
  <li>Falls vorhanden: alte <code>strava_tokens.json</code> auf dem Webspace
      per FTP/SFTP löschen oder leeren.</li>
  <li>Im Strava-Konto des Vereins angemeldet sein, dann
      <strong><a href="/strava_connect.php">strava_connect.php</a></strong>
      aufrufen. Das Skript erzeugt einen zufälligen <code>state</code>-Token
      (CSRF-Schutz), legt ihn in der Session ab und leitet zu Strava weiter.</li>
  <li>Strava fragt einmal nach Bestätigung, leitet auf
      <code>callback.php?code=…&amp;state=…</code> weiter. Der State wird
      gegen die Session geprüft, dann werden die Tokens gespeichert
      (<em>„Tokens erfolgreich gespeichert"</em>).</li>
  <li>Anschließend einmal <code>kilometer_debug.php</code> aufrufen, um zu
      verifizieren: Tokens werden geladen, Refresh klappt (oder Token noch
      gültig), Aktivitäten werden geholt, DB-Eintrag funktioniert.</li>
</ol>

<div class="note">
  <strong>Manueller Authorize-Link (nur Notfall):</strong> Falls
  <code>strava_connect.php</code> aus irgendeinem Grund nicht läuft,
  kannst du den Authorize-Schritt auch direkt aufrufen:
  <pre><code>https://www.strava.com/oauth/authorize?client_id=163827&amp;response_type=code&amp;redirect_uri=https://spvgg1879-lauftreff.de/callback.php&amp;approval_prompt=auto&amp;scope=activity:read_all</code></pre>
  <code>callback.php</code> wird dann allerdings den State-Check
  ablehnen — vorher in <code>callback.php</code> den State-Block
  vorübergehend auskommentieren, oder direkt
  <code>strava_connect.php</code> reparieren.
</div>

<div class="ok">
  <strong>Erfolgsmerkmal:</strong> <code>kilometer.php</code> liefert eine
  Zahl &gt; 0 zurück (z. B. <code>3287.4</code>), und <code>strava_tokens.json</code>
  enthält ein <code>access_token</code> sowie ein neues <code>expires_at</code>
  in der Zukunft.
</div>

<h2 id="secret">5. Client-Secret rotieren</h2>
<p>
  Den Secret regelmäßig (z. B. einmal jährlich) zu erneuern ist guter Stil.
  Zwingend nötig ist es, sobald der Verdacht besteht, dass jemand Drittes
  ihn gesehen hat (z. B. weil er in URL-Parametern, Logs, Screenshots,
  öffentlichem Repo aufgetaucht ist).
</p>
<ol>
  <li>Auf <a href="https://www.strava.com/settings/api" target="_blank" rel="noopener">strava.com/settings/api</a>
      einloggen.</li>
  <li>Bei „Client Secret“ auf <em>Refresh</em> klicken — der alte Wert wird
      <strong>sofort</strong> ungültig.</li>
  <li>Neuen Secret <strong>an genau einer Stelle</strong> eintragen:
      in <code>secrets.php</code> im Web-Root, im Block
      <code>strava → client_secret</code>. Sowohl der Public-Bereich
      als auch <code>/training/</code> ziehen den Wert von dort.</li>
  <li>Re-Auth-Flow aus Abschnitt 4 einmal komplett durchziehen.</li>
  <li>Member im Bereich <code>/training/</code> bekommen automatisch eine
      Aufforderung, ihre Strava-Verbindung neu zu autorisieren — ihre
      Refresh-Tokens sind durch die Rotation ungültig geworden.
      Idealerweise das vorher kurz im Member-Bereich ankündigen.</li>
</ol>

<div class="note">
  <strong>Hinweis:</strong> Die alte <code>Training/.env</code> wird zwar
  weiterhin geladen (für <code>STRAVA_REDIRECT_URI</code> und
  <code>APP_URL</code>), wird aber bei Konflikten von <code>secrets.php</code>
  überstimmt. Wenn du in <code>.env</code> noch alte Strava- oder DB-Werte
  stehen hast, kannst du sie dort entfernen — sie werden nicht mehr genutzt.
</div>

<h2 id="beziehung">6. Beziehung zur Member-Seite (<code>/training/</code>)</h2>
<p>
  Beide Bereiche teilen sich:
</p>
<ul>
  <li>dieselbe Strava-App (Client-ID <code>163827</code>) und damit
      denselben Client-Secret,</li>
  <li>dieselbe Datenbank-Verbindung (<code>dbs14323265</code>).</li>
</ul>
<p>
  Aber sie sind funktional getrennt:
</p>
<ul>
  <li>Der öffentliche Bereich nutzt das Vereins-Strava-Konto und einen
      einzigen, geteilten Token-Store (<code>strava_tokens.json</code>).</li>
  <li>Der Member-Bereich verwaltet pro Mitglied eine eigene
      Strava-Verbindung in der Datenbank (siehe
      <code>Training/includes/strava_client.php</code>).</li>
</ul>
<p>
  Folge: Eine Secret-Rotation betrifft <strong>beide</strong> Bereiche
  gleichzeitig. Re-Auth ist im öffentlichen Bereich einmalig vom Admin
  zu machen, im Member-Bereich von jedem Mitglied selbst.
</p>
<p>
  Auch die <strong>Strava-Client-Logik</strong> (Token-Refresh,
  Code-Exchange, API-GET) ist seit der jüngsten Konsolidierung
  gemeinsam: <code>includes/strava.php</code> wird sowohl von den
  Public-Skripten als auch vom Member-Bereich
  (<code>Training/includes/strava_client.php</code>) eingebunden. Wenn
  Strava einmal die API ändert oder ein Bug im Client gefunden wird,
  ist <strong>genau eine Datei</strong> anzufassen, nicht mehr vier.
</p>

<h2 id="troubleshooting">7. Troubleshooting</h2>

<h3>„Token-Datei ist leer oder ungültig“</h3>
<p>
  <code>strava_tokens.json</code> existiert, aber Inhalt ist leer / kein JSON.
  Ursachen: vorheriger Re-Auth ist fehlgeschlagen, der Code wurde doppelt
  benutzt, oder Strava hat etwas anderes als JSON zurückgegeben. Lösung:
  Datei löschen, Re-Auth-Flow aus Abschnitt 4 erneut durchlaufen.
</p>

<h3>HTTP 404 beim Token-Refresh</h3>
<p>
  Tritt nur noch auf, wenn jemand die Refresh-Logik versehentlich auf
  GET zurückbaut. Strava akzeptiert am Token-Endpoint nur POST. Falls das
  je wieder passiert: in <code>kilometer.php</code> /
  <code>kilometer_debug.php</code> sicherstellen, dass <code>curl_init</code>
  mit <code>CURLOPT_POST = true</code> verwendet wird, nicht
  <code>file_get_contents</code> mit Query-String.
</p>

<h3>HTTP 401 beim Token-Refresh</h3>
<p>
  „Unauthorized“ — der <code>refresh_token</code> ist ungültig. Üblicher
  Grund: der Client-Secret wurde rotiert, aber die Tokens sind noch die
  alten. Lösung: <code>strava_tokens.json</code> löschen, Re-Auth-Flow
  erneut durchlaufen.
</p>

<h3><code>kilometer.php</code> liefert <code>0</code></h3>
<p>
  Bei Fehlern (Token weg, Refresh fehlgeschlagen) gibt
  <code>kilometer.php</code> bewusst <code>0</code> mit HTTP 502 / 503
  zurück, damit das Frontend nicht abstürzt. Echten Fehler in
  <code>kilometer_debug.php</code> sichtbar machen — der zeigt jeden Schritt
  und die rohe Strava-Antwort.
</p>

<h3>„Tracking access on null“-Warnings</h3>
<p>
  Sollte mit der aktuellen Version nicht mehr vorkommen — das war das
  Symptom dafür, dass <code>$tokens</code> null war (weil
  <code>strava_tokens.json</code> nicht existierte oder leer war), und der
  Code trotzdem auf <code>$tokens['…']</code> zugriff. Falls es doch
  wieder auftaucht: <code>strava_tokens.json</code> prüfen, ggf. neu
  autorisieren.
</p>

<h2 id="sicherheit">8. Sicherheits-Hinweise</h2>
<ul>
  <li>Der <strong>Client-Secret</strong> ist wie ein Passwort. Niemals in
      URLs (Query-String) packen — er landet sonst in Server-Logs und
      Browser-History. Token-Endpoint immer per <strong>POST</strong>
      aufrufen.</li>
  <li>Das Repo nicht öffentlich pushen, solange der Secret hardcoded in
      den Dateien steht. Idealerweise Secret in eine separate, nicht
      versionierte Datei auslagern (siehe Tipp in Abschnitt 5).</li>
  <li><code>strava_tokens.json</code> nicht versionieren
      (in <code>.gitignore</code> aufnehmen, falls sie versehentlich
      hinzugefügt wurde).</li>
  <li><code>wiki.php</code> und <code>kilometer_debug.php</code> sind per
      <code>.htaccess</code> mit Passwort geschützt — siehe
      <a href="#zugangsschutz">Abschnitt 9</a> für Verwaltung.</li>
</ul>

<h2 id="zugangsschutz">9. Zugangsschutz verwalten (.htaccess / .htpasswd)</h2>
<p>
  Im Web-Root liegen zwei Dateien, die <code>wiki.php</code> und
  <code>kilometer_debug.php</code> per HTTP-Basic-Auth absichern:
</p>
<table>
  <tr>
    <th>Datei</th>
    <th>Zweck</th>
  </tr>
  <tr>
    <td><code>.htaccess</code></td>
    <td>Definiert per <code>&lt;FilesMatch&gt;</code>, welche Dateien
        Authentifizierung verlangen. Verweist auf den Pfad zur
        <code>.htpasswd</code>.</td>
  </tr>
  <tr>
    <td><code>.htpasswd</code></td>
    <td>Enthält Benutzername(n) + gehashtes Passwort
        (Format: <code>user:$apr1$…</code>). Wird vom Webserver
        beim Login geprüft und nicht ausgeliefert.</td>
  </tr>
</table>

<h3>Passwort ändern</h3>
<p>
  Neuen Hash erzeugen und in <code>.htpasswd</code> ersetzen. Auf einem
  Linux-System mit OpenSSL (oder über einen Online-Generator
  für „htpasswd APR1"):
</p>
<pre><code>openssl passwd -apr1 'NeuesPasswortHier'</code></pre>
<p>
  Output sieht z. B. so aus: <code>$apr1$ab12cd34$XyZ…</code>. Das
  ersetzt in <code>.htpasswd</code> die alte Zeile, sodass dort steht:
</p>
<pre><code>admin:$apr1$ab12cd34$XyZ…</code></pre>
<p>
  Hash sofort nach dem Speichern testen, indem man die wiki.php neu im
  Browser lädt — Browser fragt erneut nach Login.
</p>

<h3>Weiteren Benutzer hinzufügen</h3>
<p>
  Einfach eine zweite Zeile in <code>.htpasswd</code> ergänzen,
  selbe Form (Benutzername + Hash). Mehrere Benutzer dürfen sich dann
  gleichzeitig einloggen.
</p>

<h3>Weitere Datei schützen</h3>
<p>
  In <code>.htaccess</code> die Liste in der <code>FilesMatch</code>-Zeile
  erweitern, z. B. um <code>callback.php</code> mit aufzunehmen:
</p>
<pre><code>&lt;FilesMatch "^(wiki|kilometer_debug|callback)\.php$"&gt;</code></pre>

<h3>Pfad zu <code>.htpasswd</code> stimmt nicht</h3>
<p>
  Falls beim Aufruf der geschützten Seiten ein
  <strong>500 Internal Server Error</strong> erscheint, ist meist der
  absolute Pfad in der <code>AuthUserFile</code>-Zeile falsch. Aktuell
  eingetragen ist:
</p>
<pre><code>AuthUserFile /home/www/spvgg1879-lauftreff/htdocs/.htpasswd</code></pre>
<p>
  Ist das beim Hoster anders, eine kleine Test-Datei <code>pfad.php</code>
  mit <code>&lt;?php echo __DIR__;</code> hochladen, dann den Output
  als Pfad in <code>.htaccess</code> übernehmen (mit angehängtem
  <code>/.htpasswd</code>) und die Test-Datei wieder löschen.
</p>

<div class="note">
  <strong>Hinweis zu Hash-Format:</strong> APR1-MD5 ist Apaches
  Standard und funktioniert auf so gut wie jedem Webspace. Wenn der
  Hoster moderner konfiguriert ist, geht auch bcrypt
  (<code>htpasswd -nbB user 'passwort'</code>) — sicherer, aber nicht
  überall unterstützt. Im Zweifel APR1 lassen.
</div>

<h2 id="cache">10. Caching von <code>kilometer.php</code></h2>
<p>
  <code>kilometer.php</code> cached die berechnete Kilometer-Summe in
  <code>strava_km_cache.json</code> mit einer TTL von 10 Minuten. Innerhalb
  des TTL liefert der Endpoint den gecachten Wert direkt zurück, ohne
  Strava zu kontaktieren. Ergebnis: weniger Last gegen die Strava-API
  (Rate-Limit: 100 Req / 15 Min, 1000 / Tag) und deutlich schnellere
  Antwortzeiten für Besucher.
</p>

<h3>Verhalten im Detail</h3>
<table>
  <tr><th>Situation</th><th>Reaktion</th></tr>
  <tr>
    <td>Cache existiert &amp; jünger als 10 Min</td>
    <td>Sofortige Ausgabe des gecachten Wertes.</td>
  </tr>
  <tr>
    <td>Cache stale oder nicht vorhanden</td>
    <td>Frischer Strava-Aufruf, neuer Cache wird geschrieben.</td>
  </tr>
  <tr>
    <td>Strava-Aufruf schlägt fehl, alter Cache existiert</td>
    <td>Letzter gecachter Wert wird ausgegeben („graceful fallback").</td>
  </tr>
  <tr>
    <td>Strava-Aufruf schlägt fehl, kein Cache</td>
    <td><code>0</code> mit HTTP 503.</td>
  </tr>
</table>

<h3>Cache manuell leeren</h3>
<p>
  Einfach <code>strava_km_cache.json</code> per FTP löschen — der nächste
  Aufruf legt sie frisch an. Oder mit Query-Parameter erzwingen:
</p>
<pre><code>https://spvgg1879-lauftreff.de/kilometer.php?nocache=1</code></pre>
<p>
  <strong>Achtung:</strong> Der <code>?nocache=1</code>-Parameter umgeht
  den Cache komplett und triggert immer einen Strava-Aufruf. Nicht
  öffentlich verlinken — bei Missbrauch kann das Rate-Limit reißen.
  Idealerweise nur intern zum Debuggen verwenden, oder
  <code>kilometer.php</code> später ebenfalls per <code>.htaccess</code>
  schützen, wenn der Parameter regelmäßig gebraucht wird.
</p>

<h3>TTL anpassen</h3>
<p>
  In <code>kilometer.php</code>, ganz oben:
</p>
<pre><code>$cacheTTL = 600; // 10 Minuten in Sekunden</code></pre>
<p>
  Sinnvolle Werte: 300 (5 Min) bis 3600 (1 Std). Höher = weniger
  Strava-Calls, dafür „verzögerte" Anzeige nach einem Lauf. Niedriger =
  zeitnahe Updates, mehr API-Last.
</p>

<h2 id="backup">11. Token-Backup &amp; Recovery</h2>
<p>
  Bei jedem erfolgreichen Token-Refresh oder Re-Auth wird die alte
  <code>strava_tokens.json</code> automatisch nach
  <code>strava_tokens.json.bak</code> kopiert, bevor sie überschrieben
  wird. Damit hast du immer die letzte funktionierende Version als
  Fallback.
</p>

<h3>Wann wird das Backup gebraucht?</h3>
<ul>
  <li>Wenn die <code>strava_tokens.json</code> versehentlich überschrieben
      wird oder kaputt geht (z. B. bei einem fehlgeschlagenen Refresh
      mitten im Schreibvorgang).</li>
  <li>Wenn ein Code-Bug auf dem Server zwischenzeitlich ungültiges JSON
      hineinschreibt.</li>
</ul>

<h3>Recovery-Schritt</h3>
<ol>
  <li>Per FTP <code>strava_tokens.json</code> löschen (oder umbenennen
      in <code>strava_tokens.json.broken</code> für die Forensik).</li>
  <li><code>strava_tokens.json.bak</code> umbenennen in
      <code>strava_tokens.json</code>.</li>
  <li><code>kilometer_debug.php</code> aufrufen — Refresh sollte mit dem
      restaurierten Token klappen, neue Tokens werden gespeichert.</li>
</ol>

<div class="note">
  <strong>Achtung:</strong> Refresh-Tokens sind „rotating" — bei jedem
  Refresh schickt Strava einen neuen mit. Ein altes Backup ist deshalb
  nur kurz nach dem letzten Refresh verlässlich (typischerweise einige
  Stunden bis Tage). Wenn das Backup zu alt ist, hilft nur noch eine
  vollständige Re-Authorisierung über
  <a href="/strava_connect.php">strava_connect.php</a>.
</div>

<h2 id="notify">12. Fehler-Benachrichtigungen per E-Mail</h2>
<p>
  Wenn der Token-Refresh oder ein Strava-API-Aufruf fehlschlägt,
  schickt das System automatisch eine kurze E-Mail an die in
  <code>secrets.php</code> hinterlegte Admin-Adresse. Damit erfährst
  du von Problemen, bevor jemand dich auf die „0 km"-Anzeige hinweist.
</p>

<h3>Konfiguration</h3>
<p>
  In <code>secrets.php</code> im Block <code>notifications</code>:
</p>
<pre><code>'notifications' => [
    'admin_email' => 'maximilianjung@live.com',
    'mail_from'   => 'noreply@spvgg1879-lauftreff.de',
],</code></pre>
<p>
  <strong>Wichtig zur Absender-Adresse:</strong> Manche Hoster verwerfen
  Mails, deren <code>From:</code>-Adresse nicht zur eigenen Domain
  passt — deshalb ist <code>noreply@spvgg1879-lauftreff.de</code> ein
  guter Default. Falls Mails nicht ankommen, im Hoster-Panel prüfen, ob
  der Adresse das Postfach existiert oder ob der Server Mails ohne
  echtes Postfach trotzdem sendet.
</p>

<h3>Throttling</h3>
<p>
  Pro Subject wird höchstens eine Mail innerhalb von 60 Minuten
  verschickt. So vermeiden wir, dass ein dauerhaft kaputter Token in
  Hunderten Mails pro Tag resultiert. Der Throttle-Status wird in
  <code>var/notify/</code> als kleine leere Marker-Dateien abgelegt.
</p>

<h3>Welche Ereignisse triggern eine Mail?</h3>
<ul>
  <li><code>kilometer.php</code>: fehlende/ungültige Token-Datei,
      Refresh-Fehler, API-Fehler.</li>
  <li><code>kilometer_debug.php</code>: Refresh-Fehler.</li>
  <li><code>callback.php</code>: ungültiger State-Parameter,
      Token-Austausch-Fehler, Schreibfehler.</li>
</ul>

<h3>Mails komplett deaktivieren</h3>
<p>
  In <code>secrets.php</code> einfach <code>admin_email</code> auf einen
  leeren String setzen (<code>''</code>). Die Notify-Funktion ist dann
  no-op und schreibt auch keine Marker-Dateien.
</p>

<p class="muted" style="margin-top:2.5rem;">
  Stand: <?php echo date('Y-m-d'); ?> ·
  <a href="/training/wiki.php">Member-Wiki →</a>
</p>

</div>
</body>
</html>
