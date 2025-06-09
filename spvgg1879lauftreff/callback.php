<?php
echo "<pre>";

// 1. Code prüfen
if (!isset($_GET['code'])) {
    die("❌ Kein 'code' erhalten. Bitte über Strava-Login starten.\n");
}

$code = $_GET['code'];
echo "✅ Code empfangen: $code\n";

// 2. App-Daten
$client_id = '163827';
$client_secret = '21c8af73247d8876684acf4e36ec1fa1d38c9a67';

// 3. Anfrage an Strava senden
$url = "https://www.strava.com/oauth/token";
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
        'content' => http_build_
?>