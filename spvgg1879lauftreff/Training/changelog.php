<?php
$pageTitle = 'Changelog';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/header.php';
?>
<div class="container wide">
    <h1>Changelog</h1>
    <p class="muted">Alle Änderungen am Trainingsbereich — neueste Version zuerst.</p>

    <div class="wiki-intro">
        <p>
            Hier werden alle nennenswerten Änderungen an der Anwendung dokumentiert:
            neue Features, Bugfixes und Sicherheitspatches. So lässt sich jederzeit
            nachvollziehen, was wann geändert wurde und warum.
        </p>
    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- Version 1.1.0 — Security Patch                        -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div class="changelog-entry" id="v1-1-0">
        <div class="changelog-header">
            <div class="changelog-meta">
                <span class="changelog-version">v1.1.0</span>
                <span class="changelog-date">11. April 2026</span>
            </div>
            <div class="changelog-badges">
                <span class="badge badge-red">Security</span>
            </div>
        </div>

        <h2 class="changelog-title">Security Patch — Härtung der Webanwendung</h2>
        <p class="changelog-summary">
            Im Rahmen eines technischen Audits wurden fünf kritische Sicherheitslücken
            identifiziert und behoben. Die Funktionalität der Anwendung bleibt vollständig erhalten.
        </p>

        <div class="changelog-fixes">

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-red">Kritisch</span>
                    <strong>DELETE via POST erzwingen</strong>
                </div>
                <p>
                    Einträge konnten bisher über einen einfachen GET-Request (Link/URL) gelöscht werden —
                    auch unbemerkt durch Browser-Prefetch oder Links in E-Mails. Löschen erfordert
                    jetzt zwingend eine POST-Anfrage mit Formular. Der JavaScript-Confirm bleibt als
                    zusätzliche Bestätigung erhalten.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>delete_entry.php</code>
                    <code>entries.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-red">Kritisch</span>
                    <strong>Host-Header-Injection in Einladungs-URLs</strong>
                </div>
                <p>
                    Einladungslinks wurden dynamisch aus dem HTTP-Host-Header des Browsers gebaut.
                    Ein Angreifer hätte diesen Header manipulieren und so Phishing-Links auf eine
                    fremde Domain generieren können. Die Basis-URL ist jetzt fest in der
                    Konfiguration hinterlegt.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>config.php</code>
                    <code>admin_invites.php</code>
                    <code>create_invite.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-red">Kritisch</span>
                    <strong>Open Redirect in toggle_theme.php geschlossen</strong>
                </div>
                <p>
                    Nach dem Theme-Wechsel wurde auf den HTTP-Referer-Header weitergeleitet,
                    der vom Browser kontrolliert wird. Ein manipulierter Link hätte Nutzer
                    auf eine externe Seite umleiten können. Die Weiterleitung erfolgt jetzt
                    ausschließlich auf eine Whitelist bekannter interner Seiten.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>toggle_theme.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-red">Kritisch</span>
                    <strong>CSRF-Schutz auf allen POST-Formularen</strong>
                </div>
                <p>
                    Keines der Formulare war gegen Cross-Site Request Forgery (CSRF) abgesichert.
                    Eine fremde Website hätte eingeloggte Nutzer dazu bringen können, unbemerkt
                    Aktionen auszulösen (Einträge löschen, Nutzer deaktivieren etc.).
                    Alle POST-Formulare enthalten jetzt ein kryptografisches Token, das der
                    Server bei jeder Anfrage prüft.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>auth.php</code>
                    <code>login.php</code>
                    <code>register.php</code>
                    <code>entry_form.php</code>
                    <code>edit_entry.php</code>
                    <code>entries.php</code>
                    <code>delete_entry.php</code>
                    <code>update_quick_entry.php</code>
                    <code>admin_users.php</code>
                    <code>admin_invites.php</code>
                    <code>strava_import.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-red">Kritisch</span>
                    <strong>Credentials aus Code ausgelagert (.env)</strong>
                </div>
                <p>
                    Datenbank-Passwort und Strava Client Secret standen als Klartext direkt
                    in <code>config.php</code>. Diese Datei hätte in einem Git-Repository landen
                    oder durch einen Serverfehler sichtbar werden können. Alle Secrets wurden
                    in eine <code>.env</code>-Datei ausgelagert, die außerhalb der
                    Versionskontrolle bleibt.
                </p>
                <div class="changelog-files">
                    <span>Geändert / neu:</span>
                    <code>config.php</code>
                    <code>.env</code>
                    <code>.env.example</code>
                </div>
            </div>

        </div><!-- /.changelog-fixes -->
    </div><!-- /.changelog-entry -->

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- Version 1.0.0 — Initiales Release                     -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div class="changelog-entry" id="v1-0-0">
        <div class="changelog-header">
            <div class="changelog-meta">
                <span class="changelog-version">v1.0.0</span>
                <span class="changelog-date">März 2026</span>
            </div>
            <div class="changelog-badges">
                <span class="badge badge-blue">Feature</span>
            </div>
        </div>

        <h2 class="changelog-title">Initiales Release</h2>
        <p class="changelog-summary">
            Erstveröffentlichung des Trainingsbereichs für SPVGG 1879 Lauftreff.
        </p>

        <div class="changelog-fixes">

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Kernfunktionen</strong>
                </div>
                <p>
                    Einloggen, Einheiten erfassen und bearbeiten, Dashboard mit Chart,
                    CSV-Export, Wiki/Hilfe-Seite.
                </p>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Strava-Integration</strong>
                </div>
                <p>
                    OAuth2-Verbindung mit Strava, Import der letzten Läufe inkl.
                    durchschnittlichem Puls (<code>avg_heart_rate</code>).
                </p>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Modernes Frontend</strong>
                </div>
                <p>
                    Neues CSS-Design (training-modern.css) mit dunkler Navigation,
                    geteiltem Header/Footer-System und Theme-Umschalter (Modern/Classic).
                    Einheiten-Tabelle mit konfigurierbaren Spalten und Drag-to-resize.
                </p>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Admin-Bereich</strong>
                </div>
                <p>
                    Nutzerverwaltung, Invite-System mit zeitlich begrenzten Einladungslinks,
                    rollenbasierter Zugriffsschutz (admin/user).
                </p>
            </div>

        </div>
    </div><!-- /.changelog-entry v1.0.0 -->

</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
