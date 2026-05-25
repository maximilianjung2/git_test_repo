<?php
// Liefert die nächsten Lauftreff-Termine pro Kategorie als JSON für die Homepage.
// Gibt je den zeitlich nächsten Termin für 'laufen' und 'power_walking' zurück.

require __DIR__ . '/includes/termine.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');

$dataFile = __DIR__ . '/naechster_lauf.json';
$termine  = termine_laden($dataFile);

// Nächsten Termin pro Kategorie bestimmen (inline, unabhängig von includes/termine.php)
$now     = time();
$naechste = ['laufen' => null, 'power_walking' => null];
$bestTs   = ['laufen' => PHP_INT_MAX, 'power_walking' => PHP_INT_MAX];
foreach ($termine as $t) {
    $kat = $t['kategorie'] ?? 'laufen';
    if (!array_key_exists($kat, $naechste)) continue;
    $ts = strtotime($t['datum'] . ' ' . ($t['uhrzeit'] ?: '23:59'));
    if ($ts !== false && $ts >= $now - 3600 && $ts < $bestTs[$kat]) {
        $bestTs[$kat]   = $ts;
        $naechste[$kat] = $t;
    }
}


$wochentage = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
$monate     = ['Januar','Februar','März','April','Mai','Juni','Juli',
               'August','September','Oktober','November','Dezember'];

function termin_aufbereiten(?array $t, array $wochentage, array $monate): array
{
    if ($t === null) {
        return ['active' => false];
    }
    $ts = strtotime($t['datum'] . ' ' . ($t['uhrzeit'] ?: '23:59'));
    $parts = getdate($ts);
    $dateLabel = sprintf('%s · %d. %s',
        $wochentage[$parts['wday']],
        $parts['mday'],
        $monate[$parts['mon'] - 1]);
    if ($t['uhrzeit']) {
        $dateLabel .= ' · ' . $t['uhrzeit'] . ' Uhr';
    }
    return [
        'active'       => true,
        'id'           => $t['id'],
        'datum'        => $t['datum'],
        'uhrzeit'      => $t['uhrzeit'],
        'datum_label'  => $dateLabel,
        'titel'        => $t['titel'],
        'treffpunkt'   => $t['treffpunkt'],
        'beschreibung' => $t['beschreibung'],
        'kategorie'    => $t['kategorie'],
    ];
}

echo json_encode([
    'laufen'       => termin_aufbereiten($naechste['laufen'],       $wochentage, $monate),
    'power_walking' => termin_aufbereiten($naechste['power_walking'], $wochentage, $monate),
]);
