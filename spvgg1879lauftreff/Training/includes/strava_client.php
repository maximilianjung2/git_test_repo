<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function getStravaConfig(): array
{
    $config = require __DIR__ . '/config.php';
    return $config['strava'];
}

function getStravaConnection(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare("\n        SELECT *\n        FROM strava_connections\n        WHERE user_id = :user_id\n        LIMIT 1\n    ");
    $stmt->execute([
        'user_id' => $userId,
    ]);

    $connection = $stmt->fetch();

    return $connection ?: null;
}

function deleteStravaConnection(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("\n        DELETE FROM strava_connections\n        WHERE user_id = :user_id\n    ");
    $stmt->execute([
        'user_id' => $userId,
    ]);
}

function saveStravaConnection(PDO $pdo, int $userId, array $tokenData): void
{
    $existingConnection = getStravaConnection($pdo, $userId);

    $athleteId = $tokenData['athlete']['id']
        ?? $existingConnection['strava_athlete_id']
        ?? null;

    if ($athleteId === null) {
        throw new RuntimeException('Strava Athlete ID konnte nicht ermittelt werden.');
    }

    if (
        empty($tokenData['access_token']) ||
        empty($tokenData['refresh_token']) ||
        empty($tokenData['expires_at'])
    ) {
        throw new RuntimeException('Unvollständige Strava-Token-Daten.');
    }

    $stmt = $pdo->prepare("\n        INSERT INTO strava_connections (\n            user_id,\n            strava_athlete_id,\n            access_token,\n            refresh_token,\n            expires_at\n        ) VALUES (\n            :user_id,\n            :strava_athlete_id,\n            :access_token,\n            :refresh_token,\n            :expires_at\n        )\n        ON DUPLICATE KEY UPDATE\n            strava_athlete_id = VALUES(strava_athlete_id),\n            access_token = VALUES(access_token),\n            refresh_token = VALUES(refresh_token),\n            expires_at = VALUES(expires_at),\n            updated_at = CURRENT_TIMESTAMP\n    ");

    $stmt->execute([
        'user_id' => $userId,
        'strava_athlete_id' => $athleteId,
        'access_token' => $tokenData['access_token'],
        'refresh_token' => $tokenData['refresh_token'],
        'expires_at' => (int)$tokenData['expires_at'],
    ]);
}

function refreshStravaTokenIfNeeded(PDO $pdo, int $userId): ?array
{
    $connection = getStravaConnection($pdo, $userId);

    if (!$connection) {
        return null;
    }

    if (time() < ((int)$connection['expires_at'] - 60)) {
        return $connection;
    }

    $strava = getStravaConfig();

    $ch = curl_init('https://www.strava.com/oauth/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $strava['client_id'],
        'client_secret' => $strava['client_secret'],
        'grant_type' => 'refresh_token',
        'refresh_token' => $connection['refresh_token'],
    ]));

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Fehler beim Strava-Token-Refresh: ' . $error);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if (
        $httpCode !== 200 ||
        !is_array($data) ||
        empty($data['access_token']) ||
        empty($data['refresh_token']) ||
        empty($data['expires_at'])
    ) {
        if (in_array($httpCode, [400, 401], true)) {
            deleteStravaConnection($pdo, $userId);
        }

        throw new RuntimeException('Ungültige Antwort beim Strava-Token-Refresh: ' . $response);
    }

    saveStravaConnection($pdo, $userId, $data);

    return getStravaConnection($pdo, $userId);
}

function stravaApiGet(string $endpoint, string $accessToken, array $query = [], ?int &$httpCode = null): array
{
    $url = 'https://www.strava.com/api/v3' . $endpoint;

    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Fehler bei Strava-API-Anfrage: ' . $error);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode !== 200 || !is_array($data)) {
        throw new RuntimeException('Ungültige Antwort von der Strava-API: ' . $response);
    }

    return $data;
}

function getRecentStravaRuns(PDO $pdo, int $userId, int $limit = 30): array
{
    $connection = refreshStravaTokenIfNeeded($pdo, $userId);

    if (!$connection) {
        return [];
    }

    $httpCode = null;

    try {
        $activities = stravaApiGet('/athlete/activities', $connection['access_token'], [
            'per_page' => $limit,
            'page' => 1,
        ], $httpCode);
    } catch (RuntimeException $e) {
        if ($httpCode === 401) {
            deleteStravaConnection($pdo, $userId);
        }

        throw $e;
    }

    $runs = [];

    foreach ($activities as $activity) {
        $sportType = $activity['sport_type'] ?? ($activity['type'] ?? '');

        if ($sportType !== 'Run') {
            continue;
        }

        $runs[] = [
            'id' => (int)$activity['id'],
            'name' => $activity['name'] ?? 'Ohne Titel',
            'sport_type' => $sportType,
            'distance_km' => isset($activity['distance'])
                ? round(((float)$activity['distance']) / 1000, 2)
                : null,
            'duration_min' => isset($activity['moving_time'])
                ? (int)round(((int)$activity['moving_time']) / 60)
                : null,
            'activity_date' => isset($activity['start_date_local'])
                ? substr($activity['start_date_local'], 0, 10)
                : null,
            'avg_heart_rate' => isset($activity['average_heartrate'])
                ? (int)round((float)$activity['average_heartrate'])
                : null,
        ];
    }

    return $runs;
}

function getImportedStravaIds(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("\n        SELECT source_activity_id\n        FROM training_entries\n        WHERE user_id = :user_id\n          AND source = 'strava'\n          AND source_activity_id IS NOT NULL\n    ");
    $stmt->execute([
        'user_id' => $userId,
    ]);

    $rows = $stmt->fetchAll();

    return array_map('intval', array_column($rows, 'source_activity_id'));
}
