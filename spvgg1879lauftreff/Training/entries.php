<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/entry_repository.php';

$entries = getVisibleEntriesForUser($pdo, currentUserId());

$trainingTypes = [
    'Locker',
    'Intervall',
    'Tempolauf',
    'Langer Lauf',
    'Regeneration',
    'Wettkampf',
    'Alternativtraining'
];

$pageTitle = 'Meine Einheiten';
require __DIR__ . '/includes/header.php';
?>
<div class="container stretch">
    <h1>Meine Einheiten</h1>

    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success">Eintrag wurde gespeichert.</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Eintrag wurde aktualisiert.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Eintrag wurde gelöscht.</div>
    <?php endif; ?>
    <?php if (isset($_GET['imported'])): ?>
        <div class="alert alert-success">Strava-Läufe wurden importiert.</div>
    <?php endif; ?>
    <?php if (isset($_GET['quickupdated'])): ?>
        <div class="alert alert-success">Eintrag wurde direkt aktualisiert.</div>
    <?php endif; ?>
    <?php if (isset($_GET['quickerror'])): ?>
        <div class="alert alert-error">Direktes Speichern war nicht möglich.</div>
    <?php endif; ?>

    <div class="page-actions">
        <a class="button" href="/training/entry_form.php">+ Neue Einheit</a>
        <a class="button btn-secondary" href="/training/export_entries.php">CSV-Export</a>
    </div>

    <?php if (!$entries): ?>
        <p>Noch keine Einträge vorhanden.</p>
    <?php else: ?>

        <!-- Column visibility toggles + page width control -->
        <div class="col-controls" id="colControls">
            <span class="col-controls-label">Spalten:</span>
            <label class="col-toggle-item"><input type="checkbox" data-toggle="typ" checked> Typ</label>
            <label class="col-toggle-item"><input type="checkbox" data-toggle="km" checked> km</label>
            <label class="col-toggle-item"><input type="checkbox" data-toggle="min" checked> Min</label>
            <label class="col-toggle-item"><input type="checkbox" data-toggle="rpe" checked> RPE</label>
            <label class="col-toggle-item"><input type="checkbox" data-toggle="fitness" checked> Fitness</label>
            <label class="col-toggle-item"><input type="checkbox" data-toggle="puls" checked> Ø Puls</label>
            <label class="col-toggle-item"><input type="checkbox" data-toggle="quelle" checked> Quelle</label>
            <span class="col-controls-sep"></span>
            <span class="col-controls-label">Breite:</span>
            <div class="width-presets" id="widthPresets">
                <button data-width="70">70%</button>
                <button data-width="80">80%</button>
                <button data-width="90">90%</button>
                <button data-width="100">100%</button>
            </div>
            <button id="resetColBtn" style="margin-left:auto;background:none;border:none;color:#94a3b8;font-size:0.78rem;cursor:pointer;padding:2px 4px;" title="Einstellungen zurücksetzen">↺ Zurücksetzen</button>
        </div>

        <div class="table-wrapper">
            <table id="entriesTable" class="resizable-table">
                <colgroup>
                    <col data-col="datum"    style="width:92px">
                    <col data-col="titel"    style="width:340px">
                    <col data-col="typ"      style="width:112px">
                    <col data-col="km"       style="width:62px">
                    <col data-col="min"      style="width:58px">
                    <col data-col="rpe"      style="width:100px">
                    <col data-col="fitness"  style="width:100px">
                    <col data-col="puls"     style="width:72px">
                    <col data-col="quelle"   style="width:72px">
                    <col data-col="aktionen" style="width:162px">
                </colgroup>
                <thead>
                    <tr>
                        <th data-col="datum">Datum<div class="resize-handle"></div></th>
                        <th data-col="titel">Titel<div class="resize-handle"></div></th>
                        <th data-col="typ">Typ<div class="resize-handle"></div></th>
                        <th data-col="km">km<div class="resize-handle"></div></th>
                        <th data-col="min">Min<div class="resize-handle"></div></th>
                        <th data-col="rpe">RPE<div class="resize-handle"></div></th>
                        <th data-col="fitness">Fitness<div class="resize-handle"></div></th>
                        <th data-col="puls">Ø Puls<div class="resize-handle"></div></th>
                        <th data-col="quelle">Quelle<div class="resize-handle"></div></th>
                        <th data-col="aktionen">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                        <?php $formId = 'quick-form-' . (int)$entry['id']; ?>
                        <tr>
                            <td data-col="datum"><?= htmlspecialchars($entry['activity_date']) ?></td>

                            <td data-col="titel">
                                <?= htmlspecialchars($entry['title']) ?>
                                <?php if (!empty($entry['notes'])): ?>
                                    <br><small><?= nl2br(htmlspecialchars($entry['notes'])) ?></small>
                                <?php endif; ?>
                            </td>

                            <td data-col="typ">
                                <select name="training_type" form="<?= $formId ?>">
                                    <option value="">Wählen</option>
                                    <?php foreach ($trainingTypes as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>" <?= (($entry['training_type'] ?? '') === $type) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>

                            <td data-col="km"><?= $entry['distance_km'] !== null ? htmlspecialchars(number_format((float)$entry['distance_km'], 2, ',', '.')) : '-' ?></td>
                            <td data-col="min"><?= $entry['duration_min'] !== null ? htmlspecialchars((string)$entry['duration_min']) : '-' ?></td>

                            <td data-col="rpe">
                                <input type="range" name="rpe" form="<?= $formId ?>" min="1" max="10"
                                    value="<?= $entry['rpe'] !== null ? (int)$entry['rpe'] : 5 ?>"
                                    oninput="this.nextElementSibling.textContent = this.value">
                                <span class="range-value"><?= $entry['rpe'] !== null ? (int)$entry['rpe'] : 5 ?></span>
                            </td>

                            <td data-col="fitness">
                                <input type="range" name="fitness_feeling" form="<?= $formId ?>" min="1" max="10"
                                    value="<?= $entry['fitness_feeling'] !== null ? (int)$entry['fitness_feeling'] : 5 ?>"
                                    oninput="this.nextElementSibling.textContent = this.value">
                                <span class="range-value"><?= $entry['fitness_feeling'] !== null ? (int)$entry['fitness_feeling'] : 5 ?></span>
                            </td>

                            <td data-col="puls"><?= $entry['avg_heart_rate'] !== null ? htmlspecialchars((string)$entry['avg_heart_rate']) : '-' ?></td>

                            <td data-col="quelle"><?= htmlspecialchars($entry['source']) ?></td>

                            <td data-col="aktionen" class="actions-cell">
                                <form id="<?= $formId ?>" method="post" action="/training/update_quick_entry.php" class="inline-form">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$entry['id'] ?>">
                                    <button type="submit" class="action-link-button">Speichern</button>
                                </form>
                                <a href="/training/edit_entry.php?id=<?= (int)$entry['id'] ?>">Details</a>
                                <form method="post" action="/training/delete_entry.php" class="inline-form" onsubmit="return confirm('Eintrag wirklich löschen?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$entry['id'] ?>">
                                    <button type="submit" class="action-link-button action-link-danger">Löschen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    const WIDTHS_KEY     = 'entries_col_widths_v2';
    const HIDDEN_KEY     = 'entries_col_hidden_v2';
    const PAGE_WIDTH_KEY = 'entries_page_width_v1';

    const table     = document.getElementById('entriesTable');
    const container = table ? table.closest('.container') : null;
    if (!table || !container) return;

    // ── 0. Page width presets ────────────────────────────────────────────────
    const savedPageWidth = localStorage.getItem(PAGE_WIDTH_KEY) || '80';
    container.style.width = savedPageWidth + '%';

    function setActiveWidthBtn(w) {
        document.querySelectorAll('#widthPresets button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.width === String(w));
        });
    }

    document.querySelectorAll('#widthPresets button').forEach(btn => {
        btn.addEventListener('click', () => {
            const w = btn.dataset.width;
            container.style.width = w + '%';
            localStorage.setItem(PAGE_WIDTH_KEY, w);
            setActiveWidthBtn(w);
        });
    });

    setActiveWidthBtn(savedPageWidth);

    const cols    = Array.from(table.querySelectorAll('col'));
    const headers = Array.from(table.querySelectorAll('thead th'));

    // ── 1. Restore saved column widths ──────────────────────────────────────
    const savedWidths = JSON.parse(localStorage.getItem(WIDTHS_KEY) || 'null');
    if (savedWidths) {
        cols.forEach((col, i) => {
            if (savedWidths[i]) col.style.width = savedWidths[i];
        });
    }

    function persistWidths() {
        localStorage.setItem(WIDTHS_KEY, JSON.stringify(cols.map(c => c.style.width)));
    }

    // ── 2. Drag-to-resize ────────────────────────────────────────────────────
    headers.forEach((th, i) => {
        const handle = th.querySelector('.resize-handle');
        if (!handle) return;

        handle.addEventListener('mousedown', e => {
            e.preventDefault();
            const startX     = e.pageX;
            const startWidth = cols[i].offsetWidth || parseInt(cols[i].style.width, 10) || 100;

            handle.classList.add('is-resizing');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';

            const onMove = e => {
                const newW = Math.max(48, startWidth + e.pageX - startX);
                cols[i].style.width = newW + 'px';
            };

            const onUp = () => {
                handle.classList.remove('is-resizing');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                persistWidths();
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup',   onUp);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup',   onUp);
        });
    });

    // ── 3. Column visibility toggle ──────────────────────────────────────────
    const hiddenCols = new Set(JSON.parse(localStorage.getItem(HIDDEN_KEY) || '[]'));
    const styleEl = document.createElement('style');
    document.head.appendChild(styleEl);

    function applyVisibility() {
        // Build CSS rules that hide matching th, td, and col elements
        const rules = Array.from(hiddenCols).map(key =>
            `#entriesTable th[data-col="${key}"],
             #entriesTable td[data-col="${key}"],
             #entriesTable col[data-col="${key}"] { display: none; }`
        ).join('\n');
        styleEl.textContent = rules;
        localStorage.setItem(HIDDEN_KEY, JSON.stringify([...hiddenCols]));
    }

    // Wire up checkboxes
    document.querySelectorAll('#colControls input[data-toggle]').forEach(cb => {
        const key = cb.dataset.toggle;
        cb.checked = !hiddenCols.has(key);
        cb.addEventListener('change', () => {
            if (cb.checked) hiddenCols.delete(key);
            else            hiddenCols.add(key);
            applyVisibility();
        });
    });

    // Reset button
    const resetBtn = document.getElementById('resetColBtn');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            localStorage.removeItem(WIDTHS_KEY);
            localStorage.removeItem(HIDDEN_KEY);
            localStorage.removeItem(PAGE_WIDTH_KEY);
            location.reload();
        });
    }

    // Apply on load
    applyVisibility();
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
