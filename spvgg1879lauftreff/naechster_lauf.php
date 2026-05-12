<?php
// Liefert den nächsten Lauftreff-Termin als JSON für die Homepage.
// Liest die Termin-Liste, filtert vergangene Termine raus, gibt den
// zeitlich nächsten zurück. Wenn keiner ansteht: active=false.

require __DIR__ . '/includes/termine.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');

$termine = termine_laden(__DIR__ . '/naechster_lauf.json');
$next    = termine_naechster($termine);

if ($next === null) {
    echo json_encode(['active' => false]);
    exit;
}

// Für die Anzeige aufbereiten
$ts = strtotime($next['datum'] . ' ' . ($next['uhrzeit'] ?: '23:59'));
$wochentage = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
$monate     = ['Januar','Februar','März','April','Mai','Juni','Juli',
               'August','September','Oktober','November','Dezember'];
$parts = getdate($ts);

$dateLabel = sprintf('%s · %d. %s',
    $wochentage[$parts['wday']],
    $parts['mday'],
    $monate[$parts['mon'] - 1]);
if ($next['uhrzeit']) {
    $dateLabel .= ' · ' . $next['uhrzeit'] . ' Uhr';
}

echo json_encode([
    'active'       => true,
    'id'           => $next['id'],
    'datum'        => $next['datum'],
    'uhrzeit'      => $next['uhrzeit'],
    'datum_label'  => $dateLabel,
    'titel'        => $next['titel'],
    'treffpunkt'   => $next['treffpunkt'],
    'beschreibung' => $next['beschreibung'],
]);
