<?php
$client_id = '163827';
$client_secret = '21c8af73247d8876684acf4e36ec1fa1d38c9a67';
$tokenFile = __DIR__ . '/strava_tokens.json';
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";

// Tokens laden
if (!file_exists($tokenFile)) {
    die("❌ Token-Datei nicht gefunden.\n");
}
$tokens = json_decode(file_get_contents($tokenFile), true);
echo "🔑 Tokens geladen:\n";
print_r($tokens);

// Access Token ggf. erneuern
if (time() >= $tokens['expires_at']) {
    echo "🔄 Access Token abgelaufen, hole neuen...\n";

    $refresh_url = "https://www.strava.com/oauth/token?" . http_build_query([
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'refresh_token',
        'refresh_token' => $tokens['refresh_token']
    ]);

    $newResponse = file_get_contents($refresh_url);
    if ($newResponse === false) {
        die("❌ Fehler beim Abrufen des neuen Tokens.\n");
    }

    $newTokens = json_decode($newResponse, true);
    echo "✅ Neue Tokens erhalten:\n";
    print_r($newTokens);

    file_put_contents($tokenFile, json_encode($newTokens));
    $tokens = $newTokens;
} else {
    echo "✅ Access Token ist noch gültig.\n";
}

// Aktivitäten abrufen
echo "📡 Hole Aktivitäten von Strava API...\n";

$ch = curl_init("https://www.strava.com/api/v3/athlete/activities?per_page=100");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $tokens['access_token']
]);

$response = curl_exec($ch);
if ($response === false) {
    die("❌ Fehler bei der Anfrage: " . curl_error($ch) . "\n");
}
curl_close($ch);

$activities = json_decode($response, true);
if (!is_array($activities)) {
    die("❌ Ungültige Antwort von der Strava API:\n$response\n");
}


echo "✅ Aktivitäten empfangen:\n";
// print_r($activities); // optional, für große Ausgabe

echo "verbindung zu db";
try {
    $db = new PDO('mysql:host=database-5018019376.webspace-host.com;dbname=dbs14323265', 'dbu302398', 'lauftreffhomepage');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Datenbankfehler: " . $e->getMessage());
}

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

// Alle Distanzen summieren
$kmGesamt = 0;
$count = 0;
foreach ($activities as $activity) {
    if ($activity['type'] === 'Run' 
    && strpos($activity['name'], 'Spvgg. Hainstadt') !== false) 
    {
        
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

        $kmGesamt += $activity['distance']; // Meter
        echo $activity['name'];
        echo '<br>';
    }
    $count=$count+1;
}

$kmGerundet = round($kmGesamt / 1000, 2); // in km
echo "🏃‍♂️ Gesamt-Kilometer (letzte 100 Läufe): $kmGerundet km\n";
?>
