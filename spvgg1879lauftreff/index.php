<?php
// Admin-Session aus dem Trainingsbereich prüfen
$isAdmin = false;
if (session_status() === PHP_SESSION_NONE) {
    session_name('lauftreff_training');
    session_start();
}
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'admin') {
    $isAdmin = true;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1e3a8a">
    <title>Spvgg. Hainstadt Lauftreff</title>
    <link rel="stylesheet" href="/assets/css/lauftreff-base.css">
    <link rel="stylesheet" href="/assets/css/public.css">
</head>
<body>

<header class="public-topbar">
    <div class="public-topbar-inner">
        <a href="/" class="public-brand">Spvgg. Hainstadt Lauftreff</a>
        <?php if ($isAdmin): ?>
        <a href="/termin_edit.php" class="btn btn-ghost btn-sm">Termine verwalten</a>
        <?php endif; ?>
        <a href="/training/" class="btn btn-accent btn-sm">Mitgliederbereich →</a>
    </div>
</header>

<main class="public-page">
    <div class="container">

        <section class="hero-compact">
            <img src="/Images/Flyer.JPG" alt="Lauftreff Flyer" class="hero-image-small">
            <h1>Lauftreff der Spvgg. Hainstadt</h1>
            <p class="hero-tagline">Gemeinsam laufen, gemeinsam wachsen.</p>
        </section>

        <section class="km-block" id="km-block">
            <div class="km-counter" id="km-counter" data-initial="—">—</div>
            <div class="km-label">Vereins-Kilometer · gemeinsam gelaufen</div>
        </section>

        <section class="termine-row" id="termine-row" style="display:none;">
        </section>

        <template id="termin-card-template">
            <div class="termin-card">
                <div class="termin-overline"></div>
                <div class="termin-datum"></div>
                <h2 class="termin-titel"></h2>
                <div class="termin-meta"></div>
                <p class="termin-beschreibung"></p>

                <div class="termin-actions">
                    <button type="button" class="btn btn-accent rsvp-show">Ich komme vorbei</button>
                </div>

                <form class="rsvp-form" style="display:none;" autocomplete="off">
                    <div class="rsvp-message"></div>
                    <input type="hidden" name="termin_id" value="">
                    <label>Dein Name</label>
                    <input type="text" name="name" required maxlength="60" placeholder="Vor- und Nachname">

                    <label>E-Mail (optional)</label>
                    <input type="email" name="email" maxlength="120" placeholder="für Rückfragen, freiwillig">

                    <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">

                    <div class="actions">
                        <button type="submit" class="btn btn-primary">Anmelden</button>
                        <button type="button" class="btn btn-ghost rsvp-cancel">Abbrechen</button>
                    </div>
                </form>

                <div class="rsvp-success" style="display:none;">
                    <div class="check">✓</div>
                    <h3>Bist dabei!</h3>
                    <p class="muted">Wir freuen uns auf dich.</p>
                </div>
            </div>
        </template>

        <section class="cta-row">
            <a class="btn btn-primary btn-block" href="aktivit&auml;ten.html">Alle Läufe ansehen</a>
            <a class="btn btn-ghost btn-block" href="galerie.php">Fotos</a>
            <a class="btn btn-accent btn-block" href="Archiv/HaaschterRunden26/">Haaschter Runden 2026 →</a>
        </section>

    </div>
</main>

<footer class="public-footer">
    <nav class="minilinks">
        <a href="https://www.instagram.com/spvgghainstadt_lauftreff/" target="_blank" rel="noopener">Instagram</a>
        <span aria-hidden="true">·</span>
        <a href="https://spvgg1879.de" target="_blank" rel="noopener">Spvgg. Hainstadt</a>
        <span aria-hidden="true">·</span>
        <a href="mailto:lauftreff@spvgg1879.de">Kontakt</a>
    </nav>
    <p>&copy; Spvgg. 1879 Hainstadt — Lauftreff</p>
</footer>

<script>
(function () {
    const el = document.getElementById('km-counter');
    const block = document.getElementById('km-block');

    fetch('/kilometer.php', { cache: 'no-store' })
        .then(r => r.ok ? r.text() : Promise.reject(new Error('HTTP ' + r.status)))
        .then(text => {
            const target = parseFloat(text);
            if (!isFinite(target) || target <= 0) throw new Error('invalid');
            animateCount(target);
        })
        .catch(() => {
            // Falls Strava nicht erreichbar ist und auch kein Cache existiert,
            // blenden wir den Zahlen-Block sauber aus — die Seite bleibt intakt.
            block.style.display = 'none';
        });

    function animateCount(target) {
        const start = performance.now();
        const duration = 1400;
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            const value = Math.round(target * eased);
            el.textContent = value.toLocaleString('de-DE');
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
})();

// ============================================================
// Nächste Läufe + RSVP (zwei Kategorien)
// ============================================================
(function () {
    const row      = document.getElementById('termine-row');
    const template = document.getElementById('termin-card-template');

    const LABELS = {
        laufen:        '🏃 Laufen / Joggen',
        power_walking: '🚶 Power Walking',
    };

    function buildCard(data) {
        const card = template.content.cloneNode(true).querySelector('.termin-card');

        card.querySelector('.termin-overline').textContent = '📅 ' + (LABELS[data.kategorie] || 'Nächster Lauf');
        card.querySelector('.termin-datum').textContent    = data.datum_label;
        card.querySelector('.termin-titel').textContent    = data.titel;

        const meta = card.querySelector('.termin-meta');
        if (data.treffpunkt) {
            const span = document.createElement('span');
            span.className = 'termin-meta-item';
            span.textContent = data.treffpunkt;
            meta.appendChild(span);
        }

        const beschr = card.querySelector('.termin-beschreibung');
        if (data.beschreibung) {
            beschr.textContent = data.beschreibung;
        } else {
            beschr.style.display = 'none';
        }

        card.dataset.terminId = data.id;
        card.querySelector('input[name="termin_id"]').value = data.id;

        // RSVP bereits abgegeben?
        if (localStorage.getItem('rsvp_' + data.id) === '1') {
            showSuccess(card);
        }

        wireRsvp(card);
        return card;
    }

    function wireRsvp(card) {
        const actions   = card.querySelector('.termin-actions');
        const form      = card.querySelector('.rsvp-form');
        const msgBox    = card.querySelector('.rsvp-message');
        const showBtn   = card.querySelector('.rsvp-show');
        const cancelBtn = card.querySelector('.rsvp-cancel');

        showBtn.addEventListener('click', () => {
            actions.style.display = 'none';
            form.style.display = '';
            form.querySelector('input[name="name"]').focus();
        });

        cancelBtn.addEventListener('click', () => {
            form.style.display = 'none';
            actions.style.display = '';
            msgBox.innerHTML = '';
        });

        form.addEventListener('submit', e => {
            e.preventDefault();
            msgBox.innerHTML = '';
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type=submit]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Anmelden …';

            fetch('/rsvp.php', { method: 'POST', body: formData })
                .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
                .then(({ ok, data }) => {
                    if (ok && data.ok) {
                        localStorage.setItem('rsvp_' + card.dataset.terminId, '1');
                        showSuccess(card);
                    } else {
                        msgBox.innerHTML = '<div class="rsvp-message error">'
                            + (data.error || 'Anmeldung fehlgeschlagen.') + '</div>';
                    }
                })
                .catch(() => {
                    msgBox.innerHTML = '<div class="rsvp-message error">Netzwerk-Fehler. Bitte später erneut.</div>';
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Anmelden';
                });
        });
    }

    function showSuccess(card) {
        card.querySelector('.termin-actions').style.display = 'none';
        card.querySelector('.rsvp-form').style.display      = 'none';
        card.querySelector('.rsvp-success').style.display   = '';
    }

    fetch('/naechster_lauf.php', { cache: 'no-store' })
        .then(r => r.json())
        .then(resp => {
            const aktive = [resp.laufen, resp.power_walking].filter(d => d && d.active);
            if (aktive.length === 0) return;

            aktive.forEach(data => row.appendChild(buildCard(data)));

            // Zentriert wenn nur eine Karte, nebeneinander bei zwei
            row.classList.toggle('termine-row--single', aktive.length === 1);
            row.style.display = '';
        })
        .catch(() => { /* Termin-Zeile ausblenden */ });
})();
</script>

</body>
</html>
