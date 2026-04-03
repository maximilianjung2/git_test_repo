<?php

declare(strict_types=1);

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/entry_repository.php';

$entries = getVisibleEntriesForUser($pdo, currentUserId());
$username = (string) ($_SESSION['username'] ?? 'user');
$sanitizedUsername = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $username) ?? 'user';
$sanitizedUsername = trim($sanitizedUsername, '-_');
if ($sanitizedUsername === '') {
    $sanitizedUsername = 'user';
}

$timestamp = (new DateTimeImmutable())->format('Y-m-d_H-i-s');
$filename = sprintf('%s_meine-einheiten_%s.csv', $sanitizedUsername, $timestamp);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'wb');
if ($output === false) {
    http_response_code(500);
    exit('CSV-Export konnte nicht erstellt werden.');
}

fputcsv($output, [
    'activity_date',
    'title',
    'training_type',
    'distance_km',
    'duration_min',
    'rpe',
    'fitness_feeling',
    'source',
    'notes',
]);

foreach ($entries as $entry) {
    fputcsv($output, [
        (string) ($entry['activity_date'] ?? ''),
        (string) ($entry['title'] ?? ''),
        (string) ($entry['training_type'] ?? ''),
        $entry['distance_km'] !== null ? number_format((float) $entry['distance_km'], 2, '.', '') : '',
        $entry['duration_min'] !== null ? (string) $entry['duration_min'] : '',
        $entry['rpe'] !== null ? (string) $entry['rpe'] : '',
        $entry['fitness_feeling'] !== null ? (string) $entry['fitness_feeling'] : '',
        (string) ($entry['source'] ?? ''),
        (string) ($entry['notes'] ?? ''),
    ]);
}

fclose($output);
exit;
