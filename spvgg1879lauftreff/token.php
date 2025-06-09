<?php
$code = $_GET['code'];
$client_id = 163827;
$client_secret = '21c8af73247d8876684acf4e36ec1fa1d38c9a67';

$response = file_get_contents("https://www.strava.com/oauth/token", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code'
        ])
    ]
]));

$data = json_decode($response, true);
file_put_contents('strava_tokens.json', json_encode($data));
echo "Tokens gespeichert.";
