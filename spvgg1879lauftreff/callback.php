<?php
echo "<pre>";

// 1. Prüfen ob Code da ist
if (!isset($_GET['code'])) {
    die("❌ Kein 'code' erhalten. Diese Seite darf nur über Strava aufgerufen werden.\n");
}

$code = $_GET['code'];
echo "✅ Code empfangen: $code\n";

// 2. Strava App-Daten
$client_id = '163827';
$client_secret = '21c8af73247d8876684acf4e36ec1fa1d38c9a67';

// 3. Token-Anfrage an Strava
$token_url = "https://www.strava.com/oauth/token";
$params = [
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'code' => $code,
    'grant_type' => 'authorization_code'
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query($params),
    ]
];

$response = file_get_contents($token_url, false, stream_context_create($options));

if ($response === false) {
    die("❌ Fehler beim Abrufen des Tokens.\n");
}

$data = json_decode($response, true);
echo "✅ Antwort von Strava:\n";
print_r($data);

// 4. Speichern
if (!isset($data['access_token'])) {
    die("❌ Kein Access Token enthalten – möglicherweise wurde der Code schon verwendet.\n");
}

file_put_contents('strava_tokens.json', json_encode($data));
echo "✅ Tokens erfolgreich gespeichert in strava_tokens.json\n";
?>
