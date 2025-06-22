<?php
$client_id = '163827';
$client_secret = '21c8af73247d8876684acf4e36ec1fa1d38c9a67';
$tokenFile = __DIR__ . '/strava_tokens.json';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>🔄 Starte Strava-Sync...\n";

// Tokens laden
if (!file_exists($tokenFile)) {
    die("❌ Token-Datei nicht gefunden.\n");
}
$tokens = json_decode(file_get_contents($tokenFile), true);
echo "🔑 Tokens geladen.\n";

// Token ggf. erneuern
if (time() >= $tokens['expires_at']) {
    echo "🔄 Token abgelaufen, hole neuen...\n";

    $refresh_url = "https://www.strava.com/oauth/token?" . http_build_query([
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'refresh_token',
        'refresh_token' => $tokens['refresh_token']
    ]);

    $newResponse = file_get_contents($refresh_url);
    if ($newResponse === false) {
        die("❌ Fehler beim Token-Refresh.\n");
    }

    $newTokens = json_decode($newResponse, true);
    file_put_contents($tokenFile, json_encode($newTokens));
    $tokens = $newTokens;
    echo "✅ Token aktualisiert.\n";
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
        'after' => $yearAgo,
        'per_page' => 200,
        'page' => $page
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $tokens['access_token']
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        die("❌ Fehler bei API-Anfrage (Seite $page): " . curl_error($ch) . "\n");
    }
    curl_close($ch);

    $activities = json_decode($response, true);
    if (!is_array($activities)) {
        die("❌ Ungültige Antwort (Seite $page):\n$response\n");
    }

    $allActivities = array_merge($allActivities, $activities);
    echo "📄 Seite $page: " . count($activities) . " Aktivitäten geladen.\n";
    $page++;
} while (count($activities) === 200);

// Verbindung zur Datenbank
echo "🛠️ Verbinde mit Datenbank...\n";
try {
    $db = new PDO('mysql:host=database-5018019376.webspace-host.com;dbname=dbs14323265', 'dbu302398', 'lauftreffhomepage');
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
