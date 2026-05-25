<?php
// Admin-Editor für die Lauftreff-Termine.
// Geschützt per .htaccess (Basic-Auth).
//
// Pflegt eine Liste von Terminen in naechster_lauf.json. Auf der
// Homepage wird immer der zeitlich nächste angezeigt.

require __DIR__ . '/includes/termine.php';

$dataFile = __DIR__ . '/naechster_lauf.json';

$message     = '';
$messageType = '';
$editTermin  = null; // Wenn gesetzt, wird das Formular damit befüllt

// === POST ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';
    $termine = termine_laden($dataFile);

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $vorher = count($termine);
        $termine = array_values(array_filter($termine, fn($t) => $t['id'] !== $id));
        if (count($termine) < $vorher) {
            if (termine_speichern($dataFile, $termine)) {
                $message = 'Termin entfernt.';
                $messageType = 'ok';
            } else {
                $message = 'Fehler beim Schreiben der Datei. Rechte prüfen (CHMOD 666?).';
                $messageType = 'err';
            }
        } else {
            $message = 'Termin nicht gefunden.';
            $messageType = 'err';
        }
    } else { // save
        $id           = trim($_POST['id']           ?? '');
        $datum        = trim($_POST['datum']        ?? '');
        $uhrzeit      = trim($_POST['uhrzeit']      ?? '');
        $titel        = trim($_POST['titel']        ?? '');
        $treffpunkt   = trim($_POST['treffpunkt']   ?? '');
        $beschreibung = trim($_POST['beschreibung'] ?? '');
        $kategorie    = in_array($_POST['kategorie'] ?? '', ['laufen', 'power_walking'])
                            ? $_POST['kategorie']
                            : 'laufen';

        // Validierung
        $errors = [];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum)) {
            $errors[] = 'Datum im Format JJJJ-MM-TT angeben.';
        } elseif (!checkdate((int)substr($datum, 5, 2), (int)substr($datum, 8, 2), (int)substr($datum, 0, 4))) {
            $errors[] = 'Ungültiges Datum.';
        }
        if ($uhrzeit !== '' && !preg_match('/^\d{2}:\d{2}$/', $uhrzeit)) {
            $errors[] = 'Uhrzeit im Format HH:MM (oder leer lassen).';
        }
        if ($titel === '') {
            $errors[] = 'Titel darf nicht leer sein.';
        }
        if (mb_strlen($titel) > 80) {
            $errors[] = 'Titel zu lang (max. 80 Zeichen).';
        }
        if (mb_strlen($beschreibung) > 600) {
            $errors[] = 'Beschreibung zu lang (max. 600 Zeichen).';
        }

        if ($errors) {
            $message = implode(' · ', $errors);
            $messageType = 'err';
            // Eingaben für Re-Anzeige bewahren
            $editTermin = compact('id', 'datum', 'uhrzeit', 'titel', 'treffpunkt', 'beschreibung', 'kategorie');
        } else {
            $payload = [
                'id'           => $id !== '' ? $id : bin2hex(random_bytes(6)),
                'datum'        => $datum,
                'uhrzeit'      => $uhrzeit,
                'titel'        => $titel,
                'treffpunkt'   => $treffpunkt,
                'beschreibung' => $beschreibung,
                'kategorie'    => $kategorie,
            ];

            // Update wenn id bereits existiert, sonst hinzufügen
            $found = false;
            foreach ($termine as $i => $t) {
                if ($t['id'] === $payload['id']) {
                    $termine[$i] = $payload;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $termine[] = $payload;
            }

            if (termine_speichern($dataFile, $termine)) {
                $message = $found ? 'Termin aktualisiert.' : 'Termin angelegt.';
                $messageType = 'ok';
            } else {
                $message = 'Fehler beim Schreiben der Datei. Rechte prüfen (CHMOD 666?).';
                $messageType = 'err';
            }
        }
    }
}

// === Liste laden ===
$termine = termine_laden($dataFile);

// === Editier-Modus per ?edit=ID ===
if ($editTermin === null && isset($_GET['edit'])) {
    $editTermin = termine_finden($termine, (string)$_GET['edit']);
}

// Formular-Defaults
$form = $editTermin ?? ['id'=>'', 'datum'=>'', 'uhrzeit'=>'', 'titel'=>'', 'treffpunkt'=>'', 'beschreibung'=>'', 'kategorie'=>'laufen'];

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function termin_anzeige(array $t): string {
    $wochentage = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
    $monate     = ['Januar','Februar','März','April','Mai','Juni','Juli',
                   'August','September','Oktober','November','Dezember'];
    $ts = strtotime($t['datum'] . ' ' . ($t['uhrzeit'] ?: '23:59'));
    if ($ts === false) return $t['datum'];
    $p = getdate($ts);
    $s = sprintf('%s · %d. %s', $wochentage[$p['wday']], $p['mday'], $monate[$p['mon'] - 1]);
    if ($t['uhrzeit']) $s .= ' · ' . $t['uhrzeit'] . ' Uhr';
    return $s;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Termin-Editor – Lauftreff</title>
    <link rel="stylesheet" href="/assets/css/lauftreff-base.css">
    <link rel="stylesheet" href="/assets/css/public.css">
    <style>
        .editor-wrap { max-width: 720px; margin: 32px auto; padding: 0 20px; }
        h1 { margin-bottom: 6px; }
        .msg { padding: 12px 14px; border-radius: var(--radius-sm); margin-bottom: 22px; font-size: 0.92rem; }
        .msg-ok  { background: #ecfdf5; color: #166534; border-left: 3px solid #16a34a; }
        .msg-err { background: #fef2f2; color: #991b1b; border-left: 3px solid #dc2626; }

        .termin-list { margin: 24px 0; }
        .termin-row {
            display: flex; flex-wrap: wrap; align-items: center; gap: 12px;
            background: #fff; padding: 14px 16px;
            border-radius: var(--radius); border: 1px solid var(--border);
            margin-bottom: 10px;
        }
        .termin-row.past { opacity: 0.55; }
        .termin-row .meta { flex: 1; min-width: 220px; }
        .termin-row .meta-date { font-weight: 600; color: var(--blue); }
        .termin-row .meta-title { color: var(--text); }
        .termin-row .meta-treff { color: var(--muted); font-size: 0.86rem; }
        .termin-row .actions { display: flex; gap: 6px; }
        .empty-state {
            padding: 24px; text-align: center; color: var(--muted);
            background: #fff; border: 2px dashed var(--border); border-radius: var(--radius);
        }

        .form-section {
            margin-top: 28px; padding-top: 24px;
            border-top: 1px solid var(--border);
        }
        .form-field { margin-bottom: 16px; }
        .form-field label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 6px; }
        .form-field input, .form-field textarea {
            width: 100%; padding: 10px 12px;
            border: 1px solid var(--border); border-radius: var(--radius-sm);
            font-family: inherit; font-size: 0.95rem;
            background: #fff; box-sizing: border-box;
        }
        .form-field input:focus, .form-field textarea:focus {
            outline: 2px solid var(--blue); outline-offset: -1px; border-color: var(--blue);
        }
        .form-field textarea { min-height: 90px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        @media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } }
        .field-hint { font-size: 0.82rem; color: var(--muted); margin-top: 4px; }
        .form-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 18px; }
        .badge-kat { font-size: 0.78rem; padding: 2px 8px; border-radius: 99px; font-weight: 500; }
        .badge-kat.laufen { background: #dbeafe; color: #1e40af; }
        .badge-kat.pw { background: #dcfce7; color: #166534; }
        .form-field select {
            width: 100%; padding: 10px 12px;
            border: 1px solid var(--border); border-radius: var(--radius-sm);
            font-family: inherit; font-size: 0.95rem;
            background: #fff; box-sizing: border-box;
        }
        .form-field select:focus {
            outline: 2px solid var(--blue); outline-offset: -1px; border-color: var(--blue);
        }
    </style>
</head>
<body>

<header class="public-topbar">
    <div class="public-topbar-inner">
        <a href="/" class="public-brand">Spvgg. Hainstadt Lauftreff</a>
        <a href="/wiki.php" class="btn btn-ghost btn-sm" style="background:#fff;">Wiki →</a>
    </div>
</header>

<main class="editor-wrap">
    <h1>Termine</h1>
    <p class="muted">Alle hier gepflegten Termine werden auf der Startseite verfügbar — angezeigt wird immer der zeitlich nächste. Vergangene Termine verschwinden 1 Stunde nach Beginn automatisch von der Homepage.</p>

    <?php if ($message !== ''): ?>
        <div class="msg msg-<?= h($messageType) ?>"><?= h($message) ?></div>
    <?php endif; ?>

    <div class="termin-list">
        <?php if (empty($termine)): ?>
            <div class="empty-state">Noch keine Termine angelegt.</div>
        <?php else: ?>
            <?php foreach ($termine as $t):
                $ts = strtotime($t['datum'] . ' ' . ($t['uhrzeit'] ?: '23:59'));
                $past = $ts !== false && $ts < time() - 3600;
            ?>
            <div class="termin-row<?= $past ? ' past' : '' ?>">
                <div class="meta">
                    <div class="meta-date"><?= h(termin_anzeige($t)) ?><?= $past ? ' (vorbei)' : '' ?>
                        <?php
                            $kat = $t['kategorie'] ?? 'laufen';
                            echo $kat === 'power_walking'
                                ? ' <span class="badge badge-kat pw">🚶 Power Walking</span>'
                                : ' <span class="badge badge-kat laufen">🏃 Laufen</span>';
                        ?>
                    </div>
                    <div class="meta-title"><?= h($t['titel']) ?></div>
                    <?php if ($t['treffpunkt']): ?>
                        <div class="meta-treff">📍 <?= h($t['treffpunkt']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="actions">
                    <a href="?edit=<?= h($t['id']) ?>" class="btn btn-ghost btn-sm">Bearbeiten</a>
                    <form method="POST" action="termin_edit.php" style="display:inline;"
                          onsubmit="return confirm('Termin „<?= h($t['titel']) ?>" löschen?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= h($t['id']) ?>">
                        <button type="submit" class="btn btn-accent btn-sm">Löschen</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <section class="form-section">
        <h2><?= $form['id'] !== '' ? 'Termin bearbeiten' : 'Neuen Termin anlegen' ?></h2>

        <form method="POST" action="termin_edit.php">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= h($form['id']) ?>">

            <div class="form-field">
                <label for="kategorie">Kategorie</label>
                <select id="kategorie" name="kategorie">
                    <option value="laufen"       <?= ($form['kategorie'] ?? 'laufen') === 'laufen'       ? 'selected' : '' ?>>🏃 Laufen / Joggen</option>
                    <option value="power_walking" <?= ($form['kategorie'] ?? '') === 'power_walking' ? 'selected' : '' ?>>🚶 Power Walking</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label for="datum">Datum</label>
                    <input type="date" id="datum" name="datum" value="<?= h($form['datum']) ?>" required>
                    <div class="field-hint">Format: JJJJ-MM-TT</div>
                </div>
                <div class="form-field">
                    <label for="uhrzeit">Uhrzeit (optional)</label>
                    <input type="time" id="uhrzeit" name="uhrzeit" value="<?= h($form['uhrzeit']) ?>">
                    <div class="field-hint">Format: HH:MM</div>
                </div>
            </div>

            <div class="form-field">
                <label for="titel">Titel</label>
                <input type="text" id="titel" name="titel" value="<?= h($form['titel']) ?>" maxlength="80" required placeholder="z.B. Frühlings-Trainingsrunde">
            </div>

            <div class="form-field">
                <label for="treffpunkt">Treffpunkt (optional)</label>
                <input type="text" id="treffpunkt" name="treffpunkt" value="<?= h($form['treffpunkt']) ?>" maxlength="100" placeholder="z.B. Sportplatz Hainstadt">
            </div>

            <div class="form-field">
                <label for="beschreibung">Beschreibung (optional)</label>
                <textarea id="beschreibung" name="beschreibung" maxlength="600" placeholder="Kurze Info zur Strecke, Tempo, Anschluss-Treffen ..."><?= h($form['beschreibung']) ?></textarea>
                <div class="field-hint">Max. 600 Zeichen.</div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $form['id'] !== '' ? 'Änderungen speichern' : 'Termin anlegen' ?>
                </button>
                <?php if ($form['id'] !== ''): ?>
                    <a href="termin_edit.php" class="btn btn-ghost">Abbrechen</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

</main>

</body>
</html>
