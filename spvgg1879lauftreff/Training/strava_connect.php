<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';

$config = require __DIR__ . '/includes/config.php';

$scope = 'activity:read_all';
$state = bin2hex(random_bytes(16));

$_SESSION['strava_oauth_state'] = $state;

$params = http_build_query([
    'client_id' => $config['strava']['client_id'],
    'response_type' => 'code',
    'redirect_uri' => $config['strava']['redirect_uri'],
    'approval_prompt' => 'auto',
    'scope' => $scope,
    'state' => $state,
]);

header('Location: https://www.strava.com/oauth/authorize?' . $params);
exit;