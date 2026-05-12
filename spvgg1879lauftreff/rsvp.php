<?php
// Nimmt RSVPs entgegen und schickt eine Mail an den Admin.
// Der Termin wird per termin_id identifiziert — so funktioniert das
// auch dann sauber, wenn mehrere Termine parallel in der JSON stehen.

require __DIR__ . '/includes/notify.php';
require __DIR__ . '/includes/termine.php';

header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Nur POST erlaubt.']);
    exit;
}

// 1. Honeypot — Bots gucken in versteckte Felder.
if (!empty($_POST['website'] ?? '')) {
    echo json_encode(['ok' => true]);
    exit;
}

// 2. Name validieren
$name = trim($_POST['name'] ?? '');
$name = strip_tags($name);
$name = preg_replace('/\s+/', ' ', $name);
if ($name === '' || mb_strlen($name) < 2) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Bitte gib deinen Namen an.']);
    exit;
}
if (mb_strlen($name) > 60) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Name zu lang (max. 60 Zeichen).']);
    exit;
}

// 3. Optional: E-Mail
$email = trim($_POST['email'] ?? '');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ungültige E-Mail-Adresse.']);
    exit;
}

// 4. Termin per ID finden
$terminId = trim($_POST['termin_id'] ?? '');
if ($terminId === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Termin-ID fehlt.']);
    exit;
}

$termine = termine_laden(__DIR__ . '/naechster_lauf.json');
$termin  = termine_finden($termine, $terminId);
if ($termin === null) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Dieser Termin existiert nicht (mehr).']);
    exit;
}

// 5. Rate-Limit per IP + Termin (max. 1 Anmeldung pro Termin pro 30 Tage)
$rsvpDir = __DIR__ . '/var/rsvp';
if (!is_dir($rsvpDir)) {
    @mkdir($rsvpDir, 0755, true);
}
$ipHash = substr(md5(($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') . '|salt-rsvp'), 0, 16);
$marker = $rsvpDir . "/{$termin['id']}_{$ipHash}";
if (file_exists($marker) && (time() - filemtime($marker)) < 60 * 60 * 24 * 30) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Du hast dich bereits für diesen Termin angemeldet.']);
    exit;
}

// 6. Mail zusammenstellen
$subject = sprintf('Anmeldung: %s zum %s %s',
    $name, $termin['datum'], $termin['uhrzeit']);

$body  = "Neue Anmeldung zum Lauftreff:\n\n";
$body .= "Name:        $name\n";
if ($email !== '') {
    $body .= "E-Mail:      $email\n";
}
$body .= "\nTermin:\n";
$body .= "  Datum:       " . $termin['datum'] . "\n";
$body .= "  Uhrzeit:     " . ($termin['uhrzeit']    ?: '–') . "\n";
$body .= "  Titel:       " . $termin['titel'] . "\n";
$body .= "  Treffpunkt:  " . ($termin['treffpunkt'] ?: '–') . "\n";

if (!notify_admin($subject, $body, 0)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Anmeldung konnte nicht zugestellt werden. Bitte später erneut.']);
    exit;
}

@touch($marker);
echo json_encode(['ok' => true]);
