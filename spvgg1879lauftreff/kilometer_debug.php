<?php
require __DIR__ . '/includes/strava.php';
require __DIR__ . '/includes/notify.php';
$secrets = require __DIR__ . '/secrets.php';
$client_id     = $secrets['strava']['client_id'];
$client_secret = $secrets['strava']['client_secret'];
$tokenFile     = __DIR__ . '/strava_tokens.json';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>🔄 Starte Strava-Sync...\n";

// Tokens laden
if (!file_exists($tokenFile)) {
    die("❌ Token-Datei nicht gefunden.\n");
}
$raw = file_get_contents($tokenFile);
$tokens = json_decode($raw, true);
if (!is_array($tokens) || empty($tokens['refresh_token'])) {
    die("❌ Token-Datei ist leer oder ungültig. Bitte über strava_connect.php neu autorisieren.\n");
}
echo "🔑 Tokens geladen.\n";

// Token ggf. erneuern
if (strava_token_needs_refresh($tokens)) {
    echo "🔄 Token abgelaufen, hole neuen...\n";

    $refresh = strava_refresh_tokens($client_id, $client_secret, $tokens['refresh_token']);
    if (!$refresh['ok']) {
        notify_admin(
            'Strava Token-Refresh fehlgeschlagen (Debug-Sync)',
            "Im manuell gestarteten Debug-Sync ist der Refresh fehlgeschlagen.\n"
            . "HTTP-Code: {$refresh['http_code']}\n"
            . "Fehler:    {$refresh['error']}"
        );
        die("❌ Token-Refresh fehlgeschlagen (HTTP {$refresh['http_code']}): {$refresh['error']}\n");
    }

    if (!strava_save_tokens_with_backup($tokenFile, $refresh['data'])) {
        die("❌ Token-Datei konnte nicht geschrieben werden: $tokenFile\n");
    }
    $tokens = $refresh['data'];
    echo "✅ Token aktualisiert (Backup unter {$tokenFile}.bak).\n";
} else {
    echo "✅ Access Token ist gültig.\n";
}

// Aktivitäten der letzten 365 Tage laden (alle Seiten)
echo "📡 Lade Aktivitäten der letzten 365 Tage...\n";
$yearAgo = strtotime('-365 days');
$page = 1;
$allActivities = [];

do {
    $url = "https://www.strava.com/api/v3/athlete/activities?" . http_build_query([
        'after'    => $yearAgo,
        'per_page' => 200,
        'page'     => $page,
    ]);

    $result = strava_api_get($tokens['access_token'], $url);
    if (!$result['ok']) {
        die("❌ Fehler bei API-Anfrage (Seite $page, HTTP {$result['http_code']}): {$result['error']}\n");
    }
    $activities = $result['data'];

    $allActivities = array_merge($allActivities, $activities);
    echo "📄 Seite $page: " . count($activities) . " Aktivitäten geladen.\n";
    $page++;
} while (count($activities) === 200);

// Verbindung zur Datenbank
echo "🛠️ Verbinde mit Datenbank...\n";
try {
    $db = new PDO(
        "mysql:host={$secrets['db']['host']};dbname={$secrets['db']['name']};charset={$secrets['db']['charset']}",
        $secrets['db']['user'],
        $secrets['db']['pass']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Verbindung erfolgreich.\n";
} catch (PDOException $e) {
    die("❌ DB-Fehler: " . $e->getMessage());
}

// DB-Statement vorbereiten
$insert = $db->prepare("
    INSERT INTO strava_activities (
        id, name, type, distance, moving_time, elapsed_time,
        start_date, start_date_local, timezone, average_speed,
        max_speed, total_elevation_gain, kudos_count, athlete_id
    ) VALUES (
        :id, :name, :type, :distance, :moving_time, :elapsed_time,
        :start_date, :start_date_local, :timezone, :average_speed,
        :max_speed, :total_elevation_gain, :kudos_count, :athlete_id
    )
    ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        distance = VALUES(distance),
        moving_time = VALUES(moving_time),
        elapsed_time = VALUES(elapsed_time),
        start_date = VALUES(start_date),
        start_date_local = VALUES(start_date_local),
        timezone = VALUES(timezone),
        average_speed = VALUES(average_speed),
        max_speed = VALUES(max_speed),
        total_elevation_gain = VALUES(total_elevation_gain),
        kudos_count = VALUES(kudos_count)
");

// Aktivitäten filtern & speichern
echo "💾 Verarbeite Aktivitäten mit \"Spvgg. Hainstadt\"...\n";
$kmGesamt = 0;
$count = 0;

foreach ($allActivities as $activity) {
    if ($activity['type'] === 'Run' && strpos($activity['name'], 'Spvgg. Hainstadt') !== false) {
        $insert->execute([
            ':id' => $activity['id'],
            ':name' => $activity['name'],
            ':type' => $activity['type'],
            ':distance' => $activity['distance'],
            ':moving_time' => $activity['moving_time'],
            ':elapsed_time' => $activity['elapsed_time'],
            ':start_date' => $activity['start_date'],
            ':start_date_local' => $activity['start_date_local'],
            ':timezone' => $activity['timezone'],
            ':average_speed' => $activity['average_speed'],
            ':max_speed' => $activity['max_speed'],
            ':total_elevation_gain' => $activity['total_elevation_gain'],
            ':kudos_count' => $activity['kudos_count'],
            ':athlete_id' => $activity['athlete']['id'] ?? 0
        ]);

        echo "✔️ " . $activity['name'] . " gespeichert.\n";
        $kmGesamt += $activity['distance'];
        $count++;
    }
}

$kmGerundet = round($kmGesamt / 1000, 2);
echo "\n🏁 Fertig: $count Aktivitäten gespeichert.\n";
echo "📏 Gesamt-Kilometer: $kmGerundet km\n";
echo "</pre>";
