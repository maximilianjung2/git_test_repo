<?php
$client_id = '163827';
$client_secret = '21c8af73247d8876684acf4e36ec1fa1d38c9a67';
$tokenFile = __DIR__ . '/strava_tokens.json';

echo "<pre>";

// Tokens laden
if (!file_exists($tokenFile)) {
    die("❌ Token-Datei nicht gefunden.\n");
}
$tokens = json_decode(file_get_contents($tokenFile), true);
echo "🔑 Tokens geladen:\n";
print_r($tokens);

// Wenn Access Token abgelaufen → erneuern
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

    // Tokens aktualisieren
    file_put_contents($tokenFile, json_encode($newTokens));
    $tokens = $newTokens;
} else {
    echo "✅ Access Token ist noch gültig.\n";
}

// Aktivitäten abrufen
echo "📡 Hole Aktivitäten von Strava API...\n";
$ch = curl_init("https://www.strava.com/api/v3/athlete/activities?per_page=50");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $tokens['access_token']
]);
$response = c
