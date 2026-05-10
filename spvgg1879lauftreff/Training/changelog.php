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
    <!-- Version 1.3.0 — Backend-Konsolidierung & Reliability  -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div class="changelog-entry" id="v1-3-0">
        <div class="changelog-header">
            <div class="changelog-meta">
                <span class="changelog-version">v1.3.0</span>
                <span class="changelog-date">10. Mai 2026</span>
            </div>
            <div class="changelog-badges">
                <span class="badge badge-blue">Refactor</span>
                <span class="badge badge-red">Security</span>
            </div>
        </div>

        <h2 class="changelog-title">Backend-Konsolidierung, Caching &amp; Reliability</h2>
        <p class="changelog-summary">
            Der öffentliche Vereinsbereich und der Mitgliederbereich teilen sich jetzt
            eine gemeinsame Strava-Client-Bibliothek und einen zentralen Konfigurations-
            und Secret-Speicher. Damit ist beim nächsten API-Update oder Secret-Rotate
            nur noch eine Stelle anzufassen statt vier. Außerdem: automatischer
            Token-Backup, Admin-Mails bei Fehlern und ein Status-Dashboard für die
            öffentliche Vereins-Anzeige. Funktionalität für Mitglieder bleibt
            unverändert — alle Änderungen sind unter der Haube.
        </p>

        <div class="changelog-fixes">

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Refactor</span>
                    <strong>Geteilter Strava-Client</strong>
                </div>
                <p>
                    Token-Refresh, OAuth-Code-Exchange und API-GETs leben jetzt zentral
                    in <code>includes/strava.php</code>. Der Mitglieder-Client
                    (<code>strava_client.php</code>) wrappt diese Primitive und kümmert
                    sich nur noch um die DB-spezifische Token-Persistenz pro Mitglied.
                    Wenn Strava die API ändert oder ein Bug auftaucht, ist nur noch
                    eine Datei betroffen statt zuvor vier.
                </p>
                <div class="changelog-files">
                    <span>Neu:</span>
                    <code>../includes/strava.php</code>
                    <span style="margin-left:8px;">Geändert:</span>
                    <code>includes/strava_client.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Refactor</span>
                    <strong>Zentrale <code>secrets.php</code> für beide Bereiche</strong>
                </div>
                <p>
                    Strava-Credentials und DB-Zugang stehen jetzt in einer einzigen
                    <code>secrets.php</code> im Web-Root, die von beiden Bereichen
                    gemeinsam geladen wird. Die <code>.env</code> wird weiterhin für
                    Training-spezifische Werte (Redirect-URI, App-URL) genutzt, dient
                    aber nur noch als Fallback für die anderen Werte. Beim
                    Secret-Rotieren reicht jetzt eine Stelle.
                </p>
                <div class="changelog-files">
                    <span>Neu:</span>
                    <code>../secrets.php</code>
                    <span style="margin-left:8px;">Geändert:</span>
                    <code>includes/config.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Reliability</span>
                    <strong>Caching der öffentlichen Kilometer-Anzeige</strong>
                </div>
                <p>
                    Die Vereins-Kilometer-Anzeige cached die berechnete Summe für 10
                    Minuten. Damit reduziert sich die Last auf der Strava-API deutlich
                    (Rate-Limit: 100 Requests / 15 Min) und die öffentliche Anzeige lädt
                    spürbar schneller. Bei API-Fehlern wird der zuletzt gecachte Wert
                    zurückgegeben — die Anzeige bleibt also stabil, auch wenn Strava
                    mal kurz nicht erreichbar ist.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>../kilometer.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Reliability</span>
                    <strong>Automatisches Token-Backup</strong>
                </div>
                <p>
                    Bei jedem erfolgreichen Token-Refresh wird die alte
                    <code>strava_tokens.json</code> nach
                    <code>strava_tokens.json.bak</code> kopiert, bevor sie überschrieben
                    wird. Bei einer Störung lässt sich so die letzte funktionierende
                    Version wiederherstellen, ohne sofort eine vollständige
                    Re-Authorisierung zu brauchen. (Wirksam für die öffentliche
                    Vereins-Anbindung; Mitglieder-Tokens liegen in der DB und sind
                    über deren Backups gesichert.)
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>../includes/strava.php</code>
                    <code>../callback.php</code>
                    <code>../kilometer.php</code>
                    <code>../kilometer_debug.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-red">Security</span>
                    <strong>CSRF-Schutz für die öffentliche OAuth-Anbindung</strong>
                </div>
                <p>
                    Der Mitgliederbereich hatte seit v1.1.0 bereits State-Parameter-
                    Validierung im OAuth-Flow. Die öffentliche Vereins-Anbindung war
                    bisher nicht so abgesichert. Mit dem neuen
                    <code>strava_connect.php</code> wird auch hier ein zufälliger
                    State-Token gesetzt, in der Session abgelegt und beim Callback
                    per <code>hash_equals</code> geprüft.
                </p>
                <div class="changelog-files">
                    <span>Neu:</span>
                    <code>../strava_connect.php</code>
                    <span style="margin-left:8px;">Geändert:</span>
                    <code>../callback.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-red">Security</span>
                    <strong>Public-Token-Refresh: POST statt GET</strong>
                </div>
                <p>
                    Der Token-Refresh des öffentlichen Vereins-Kontos hatte den
                    <code>client_secret</code> als URL-Parameter via GET an Strava
                    geschickt — der Endpoint akzeptiert aber nur POST, und der
                    Secret landete dabei zusätzlich in Server- und Browser-Logs.
                    Ist jetzt gefixt: Token-Endpoint wird ausschließlich per POST
                    aufgerufen, Credentials nur im Body.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>../kilometer.php</code>
                    <code>../kilometer_debug.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Admin-Mails bei Token- und API-Fehlern</strong>
                </div>
                <p>
                    Wenn der Token-Refresh oder ein Strava-API-Aufruf der öffentlichen
                    Anzeige fehlschlägt, schickt das System automatisch eine kurze
                    E-Mail an die in <code>secrets.php</code> hinterlegte Admin-Adresse.
                    Pro Subject höchstens eine Mail pro Stunde — damit ein dauerhaft
                    kaputter Token nicht in 144 Mails am Tag resultiert. Probleme
                    werden so erkannt, bevor jemand die „0 km"-Anzeige meldet.
                </p>
                <div class="changelog-files">
                    <span>Neu:</span>
                    <code>../includes/notify.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Status-Dashboard für die Vereins-Anzeige</strong>
                </div>
                <p>
                    Das öffentliche Admin-Wiki hat oben jetzt drei Status-Karten —
                    Strava-Token, letzter Sync, DB-Verbindung — mit grüner/gelber/roter
                    Ampel. Damit ist auf einen Blick erkennbar, ob die Vereins-
                    Kilometer-Anzeige läuft oder hakt, ohne durch Logs wühlen zu müssen.
                </p>
                <div class="changelog-files">
                    <span>Neu:</span>
                    <code>../includes/health.php</code>
                    <span style="margin-left:8px;">Geändert:</span>
                    <code>../wiki.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Mitgliederbereich von der Vereins-Startseite verlinkt</strong>
                </div>
                <p>
                    Die öffentliche <code>index.html</code> hat jetzt einen deutlich
                    sichtbaren Button „Mitgliederbereich" in Strava-Orange, der direkt
                    zu <code>/training/</code> verlinkt. Mitglieder finden den Weg
                    in den geschützten Bereich vom üblichen Vereins-Einstieg aus,
                    ohne sich die URL zu merken.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>../index.html</code>
                </div>
            </div>

        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════ -->
    <!-- Version 1.2.0 — Mobile UX & PWA                       -->
    <!-- ═══════════════════════════════════════════════════════ -->
    <div class="changelog-entry" id="v1-2-0">
        <div class="changelog-header">
            <div class="changelog-meta">
                <span class="changelog-version">v1.2.0</span>
                <span class="changelog-date">15. April 2026</span>
            </div>
            <div class="changelog-badges">
                <span class="badge badge-blue">Feature</span>
            </div>
        </div>

        <h2 class="changelog-title">Mobile UX & Progressive Web App</h2>
        <p class="changelog-summary">
            Die App ist jetzt vollständig auf die mobile Nutzung ausgerichtet — insbesondere
            für den typischen Workflow: Lauf beenden, Strava importieren, Notiz und Bewertung eintragen.
            Zusätzlich ist die App als PWA installierbar und verhält sich auf dem Homescreen
            wie eine native App.
        </p>

        <div class="changelog-fixes">

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Progressive Web App (PWA)</strong>
                </div>
                <p>
                    Die App kann auf iOS und Android über „Zum Home-Bildschirm hinzufügen" installiert werden.
                    Sie öffnet sich dann im Vollbild ohne Browser-Leiste und verhält sich wie eine native App.
                    Ein Service Worker cached statische Assets für schnellere Ladezeiten.
                    Kein App-Store, kein Update-Prozess — Änderungen am Server sind sofort aktiv.
                </p>
                <div class="changelog-files">
                    <span>Neu:</span>
                    <code>manifest.json</code>
                    <code>sw.js</code>
                    <code>assets/icons/icon-192.png</code>
                    <code>assets/icons/icon-512.png</code>
                    <span style="margin-left:8px;">Geändert:</span>
                    <code>includes/header.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Bottom Navigation</strong>
                </div>
                <p>
                    Auf mobilen Geräten ersetzt eine fixe Bottom-Navigation die Desktop-Menüleiste.
                    Die vier Hauptfunktionen — Dashboard, Einheiten, Strava, Neu — sind direkt am
                    Daumenrand erreichbar. Ein „···"-Button öffnet ein Bottom-Sheet mit Wiki,
                    Changelog und (für Admins) den Admin-Bereichen.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>includes/header.php</code>
                    <code>assets/css/training-modern.css</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Card-Layout für Einheiten und Dashboard</strong>
                </div>
                <p>
                    Die Einheitentabelle und die „Letzte Einheiten"-Tabelle im Dashboard werden
                    auf Mobile durch kompakte Karten ersetzt. Jede Karte zeigt Titel, Datum
                    und die wichtigsten Kennzahlen auf einen Blick. Antippen öffnet direkt
                    die Bearbeitungsseite. Auf Desktop bleibt die Tabellenansicht unverändert.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>entries.php</code>
                    <code>dashboard.php</code>
                    <code>assets/css/training-modern.css</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Strava-Import als Card-Liste</strong>
                </div>
                <p>
                    Die Import-Tabelle in <code>strava_import.php</code> wird auf Mobile durch
                    eine Card-Liste ersetzt: jede Aktivität zeigt Checkbox, Titel und Eckdaten
                    in einer kompakten, gut tippbaren Karte.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>strava_import.php</code>
                </div>
            </div>

            <div class="changelog-fix">
                <div class="changelog-fix-header">
                    <span class="badge badge-blue">Feature</span>
                    <strong>Einheit bearbeiten — mobil optimiert</strong>
                </div>
                <p>
                    Das Bearbeitungsformular ist auf Mobile neu priorisiert: Notizen, RPE
                    und Fitnessgefühl erscheinen zuerst — genau die Felder, die nach einem
                    Strava-Import noch fehlen. RPE und Fitness werden als Schaltflächen-Reihe
                    (1–10) dargestellt statt als Zahlenfeld. Die technischen Felder
                    (Distanz, Dauer, Puls — meist von Strava befüllt) rutschen nach unten.
                    Auf Desktop bleibt die Reihenfolge unverändert.
                </p>
                <div class="changelog-files">
                    <span>Geändert:</span>
                    <code>edit_entry.php</code>
                    <code>assets/css/training-modern.css</code>
                </div>
            </div>

        </div>
    </div><!-- /.changelog-entry v1.2.0 -->

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
