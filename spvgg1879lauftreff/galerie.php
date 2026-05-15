<?php
/**
 * Automatische Fotogalerie.
 *
 * Scannt den Ordner /Galerie (oder /galerie) und zeigt alle
 * unterstützten Bildformate als Grid an. HEIC/HEIF werden separat
 * aufgelistet, da Browser sie meist nicht darstellen.
 *
 * Sortierung: neueste zuerst (nach Datei-Änderungsdatum).
 */

$ordner = __DIR__ . '/Images/Galerie';
$webPath = '/Images/Galerie';

if (!is_dir($ordner)) {
    $ordner = __DIR__ . '/Images/galerie';
    $webPath = '/Images/galerie';
}

$bilder = [];   // anzeigbare Bilder
$heic   = [];   // nicht direkt anzeigbare HEICs

if (is_dir($ordner)) {
    foreach (scandir($ordner) as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $ordner . '/' . $file;
        if (!is_file($path)) continue;

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
            $bilder[] = [
                'src'   => $webPath . '/' . rawurlencode($file),
                'name'  => $file,
                'mtime' => filemtime($path),
            ];
        } elseif (in_array($ext, ['heic','heif'], true)) {
            $heic[] = $file;
        }
    }

    // Neueste zuerst
    usort($bilder, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
}

?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1e3a8a">
    <title>Fotogalerie – Spvgg. Hainstadt Lauftreff</title>
    <link rel="stylesheet" href="/assets/css/lauftreff-base.css">
    <link rel="stylesheet" href="/assets/css/public.css">
    <style>
        /* === Lightbox === */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 200;
            background: rgba(0,0,0,0.92);
            justify-content: center;
            align-items: center;
            padding: var(--space-4);
            cursor: zoom-out;
        }
        .lightbox.active { display: flex; }
        .lightbox img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: var(--radius-sm);
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            cursor: default;
        }
        .lightbox-close {
            position: absolute;
            top: var(--space-3);
            right: var(--space-4);
            color: #fff;
            font-size: 2rem;
            line-height: 1;
            text-decoration: none;
            opacity: 0.7;
            transition: opacity 0.15s ease;
        }
        .lightbox-close:hover { opacity: 1; }

        /* === HEIC-Hinweis === */
        .heic-note {
            background: #fff7e6;
            border-left: 4px solid #f0a500;
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-sm);
            margin-top: var(--space-5);
            font-size: 0.95rem;
            color: #553c00;
        }
        .heic-note strong { color: #7a4e00; }
    </style>
</head>
<body>

<header class="public-topbar">
    <div class="public-topbar-inner">
        <a href="/" class="public-brand">Spvgg. Hainstadt Lauftreff</a>
        <a href="/training/" class="btn btn-accent btn-sm">Mitgliederbereich →</a>
    </div>
</header>

<main class="public-page">
    <div class="container">

        <div class="page-header">
            <h1>Fotogalerie</h1>
            <p>Eindrücke aus dem Lauftreff.</p>
        </div>

<?php if (empty($bilder)): ?>
        <div class="note">
            <strong>Noch keine Bilder vorhanden.</strong><br>
            Lege auf dem Server einen Ordner <code>Images/Galerie/</code> an und lade Bilder (JPG, PNG, GIF, WEBP) per FTP hinein.
            Sie erscheinen dann automatisch hier.
        </div>
<?php else: ?>
        <div class="gallery-grid" id="gallery">
<?php foreach ($bilder as $b): ?>
            <a href="<?php echo htmlspecialchars($b['src']); ?>" class="gallery-item" data-caption="<?php echo htmlspecialchars($b['name']); ?>">
                <img src="<?php echo htmlspecialchars($b['src']); ?>" alt="<?php echo htmlspecialchars($b['name']); ?>" loading="lazy">
            </a>
<?php endforeach; ?>
        </div>
<?php endif; ?>

<?php if (!empty($heic)): ?>
        <div class="heic-note">
            <strong>⚠️ <?php echo count($heic); ?> HEIC-Bild(er) gefunden</strong><br>
            Diese werden von den meisten Browsern nicht unterstützt.
            Bitte vor dem Upload als JPG exportieren (am iPhone: „Share → Options → Automatic“ oder Foto-App → Export).
            <br><span class="muted">Betroffene Dateien: <?php echo htmlspecialchars(implode(', ', $heic)); ?></span>
        </div>
<?php endif; ?>

        <p style="margin-top: var(--space-5);">
            <a href="/" class="btn btn-ghost">← Zur Übersicht</a>
        </p>

    </div>
</main>

<footer class="public-footer">
    <p>&copy; Spvgg. 1879 Hainstadt — Lauftreff</p>
</footer>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Vergrößerte Ansicht">
    <a href="#" class="lightbox-close" id="lb-close" aria-label="Schließen">&times;</a>
    <img src="" alt="" id="lb-img">
</div>

<script>
(function() {
    const gallery = document.getElementById('gallery');
    const lightbox = document.getElementById('lightbox');
    const lbImg = document.getElementById('lb-img');
    const lbClose = document.getElementById('lb-close');

    if (!gallery) return;

    gallery.querySelectorAll('a.gallery-item').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            lbImg.src = link.href;
            lbImg.alt = link.dataset.caption || '';
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    });

    function close() {
        lightbox.classList.remove('active');
        lbImg.src = '';
        document.body.style.overflow = '';
    }

    lightbox.addEventListener('click', e => {
        if (e.target === lightbox || e.target === lbClose) close();
    });

    lbClose.addEventListener('click', e => {
        e.preventDefault();
        close();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) close();
    });
})();
</script>

</body>
</html>
