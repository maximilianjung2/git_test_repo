<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "1. Start strava_import.php\n";

require __DIR__ . '/includes/auth.php';
requireLogin();
echo "2. Auth ok\n";

require __DIR__ . '/includes/db.php';
echo "3. DB ok\n";

require __DIR__ . '/includes/strava_client.php';
echo "4. strava_client geladen\n";

$userId = currentUserId();
echo "5. User ID: " . $userId . "\n";

$connection = getStravaConnection($pdo, $userId);
echo "6. Verbindung geladen:\n";
var_dump($connection);

if ($connection) {
    $runs = getRecentStravaRuns($pdo, $userId, 5);
    echo "7. Läufe geladen:\n";
    var_dump($runs);
} else {
    echo "7. Keine Strava-Verbindung gefunden.\n";
}

echo "</pre>";
exit;