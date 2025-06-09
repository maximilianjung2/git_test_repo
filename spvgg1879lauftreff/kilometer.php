<?php
$client_id = '163827';
$client_secret = '21c8af73247d8876684acf4e36ec1fa1d38c9a67';
$tokenFile = __DIR__ . '/strava_tokens.json';

// Tokens laden
$tokens = json_decode(file_get_contents($tokenFile), true);

// Wenn Access Token abgelaufen → erneuern
if (time() >= $tokens['expires_at']) {
    $refresh_url = "https://www.strava.com/oauth/token?" . http_build_query([
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'refresh_token',
        'refresh_token' => $tokens['refresh_token']
    ]);

    $newResponse = file_get_contents($refresh_url);
    $newTokens = json_decode($newResponse, true);

    // Tokens aktualisieren
    file_put_contents($tokenFile, json_encode($newTokens));
    $tokens = $newTokens;
}

// Aktivitäten abrufen
$ch = curl_init("https://www.strava.com/api/v3/athlete/activities?per_page=50");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $tokens['access_token']
]);
$response = curl_exec($ch);
curl_close($ch);

$activities = json_decode($response, true);

$totalDistance = 0;

foreach ($activities as $activity) {
    if ($activity['type'] === 'Run') {
        $totalDistance += $activity['distance']; // Meter
    }
}

$km = round($totalDistance / 1000, 1);

// Ausgabe
header('Content-Type: text/plain');
echo $km;
?>
