<?php
$pageTitle = 'Wiki / Hilfe';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/header.php';
?>
<div class="container wide">
    <h1>Wiki / Hilfe</h1>
    <p class="muted">Nutzerinfos, Projektdoku und ein Ort zum Nachschlagen.</p>

    <div class="wiki-intro">
        <p>
            Dieses Tool wurde für den Lauftreff gebaut, um Trainingseinheiten einfach und bewusst zu dokumentieren.
            Im Mittelpunkt stehen keine überladenen Social- oder Analysefunktionen, sondern eine schlichte Möglichkeit,
            das eigene Training mit ein paar Eckdaten und persönlichen Notizen festzuhalten.
        </p>
        <p>
            Diese Seite dient sowohl als Hilfe für Nutzer als auch als technische Dokumentation des aktuellen Projektstands.
        </p>
    </div>

    <div class="wiki-toc">
        <strong>Inhalt</strong>
        <ul>
            <li><a href="#faq">Nutzer-FAQ</a></li>
            <li><a href="#technik">Technische Dokumentation</a></li>
            <li><a href="#roadmap">Backlog &amp; Prioritäten</a></li>
            <li><a href="/training/changelog.php">Changelog →</a></li>
        </ul>
    </div>

    <div class="wiki-section" id="faq">
        <h2>Nutzer-FAQ</h2>

        <div class="faq-item">
            <h3>Was ist der Zweck des Tools?</h3>
            <p>
                Das Tool soll helfen, Trainingseinheiten einfach zu dokumentieren. Der Fokus liegt darauf,
                für sich selbst kurz festzuhalten, was man gemacht hat und wie sich die Einheit angefühlt hat.
            </p>
        </div>

        <div class="faq-item">
            <h3>Was unterscheidet das Tool von Strava?</h3>
            <p>
                Dieses Tool ist bewusst einfacher gehalten. Es soll keine große Plattform mit sehr vielen Funktionen sein,
                sondern eher ein persönliches Trainingstagebuch mit klarer Struktur.
            </p>
        </div>

        <div class="faq-item">
            <h3>Welche Informationen kann ich speichern?</h3>
            <p>Zu einer Einheit können unter anderem folgende Informationen gespeichert werden:</p>
            <ul>
                <li>Datum</li>
                <li>Titel</li>
                <li>Sportart</li>
                <li>Typ der Einheit</li>
                <li>Distanz in Kilometern</li>
                <li>Dauer in Minuten</li>
                <li>RPE / subjektive Anstrengung</li>
                <li>Fitnessgefühl</li>
                <li>Durchschnittlicher Puls (bpm)</li>
                <li>persönliche Notizen</li>
                <li>Quelle, z. B. manuell oder importiert</li>
            </ul>
        </div>

        <div class="faq-item">
            <h3>Muss ich alles ausfüllen?</h3>
            <p>
                Nein. Das Tool soll bewusst unkompliziert sein. Es reicht, nur die Informationen zu erfassen,
                die für die eigene Dokumentation sinnvoll sind.
            </p>
        </div>

        <div class="faq-item">
            <h3>Kann ich Einheiten später noch anpassen?</h3>
            <p>
                Ja. Einträge können vollständig über die Detailbearbeitung geändert werden. Zusätzlich lassen sich in
                <strong>Meine Einheiten</strong> einige Felder direkt in der Tabellenansicht speichern, zum Beispiel
                Einheitstyp, RPE und Fitnessgefühl.
            </p>
        </div>

        <div class="faq-item">
            <h3>Wofür sind die Notizen gedacht?</h3>
            <p>
                Im Notizfeld können persönliche Eindrücke festgehalten werden, zum Beispiel Tagesform, Wetter,
                besondere Beobachtungen, Beschwerden oder ein kurzes Fazit zur Einheit.
            </p>
        </div>

        <div class="faq-item">
            <h3>Kann ich Daten aus Strava importieren?</h3>
            <p>
                Ja. Über die Strava-Anbindung kann das Konto verbunden und anschließend eine Liste der letzten Aktivitäten
                importiert werden. Bereits importierte Aktivitäten werden erkannt und nicht erneut als neue Einheiten angeboten.
                Der durchschnittliche Puls wird dabei automatisch aus Strava übernommen.
            </p>
        </div>

        <div class="faq-item">
            <h3>Kann ich meine Einheiten exportieren?</h3>
            <p>
                Ja. Auf der Seite <strong>Meine Einheiten</strong> gibt es einen CSV-Export. Damit lassen sich
                die eigenen Daten in anderen Tools weiterverwenden oder analysieren.
            </p>
        </div>

        <div class="faq-item">
            <h3>Wer kann meine Daten sehen?</h3>
            <p>
                Nutzer sehen ihre eigenen Einheiten. Admins haben eine Verwaltungsübersicht über registrierte Nutzer,
                ihren Status, die Anzahl sichtbarer Einträge und den Strava-Verbindungsstatus. Die eigentlichen Einheitsdetails
                sind weiterhin auf die jeweilige Nutzeransicht ausgerichtet.
            </p>
        </div>

        <div class="faq-item">
            <h3>Wie werden neue Nutzer angelegt?</h3>
            <p>
                Neue Nutzer werden über einen Einladungslink angelegt. Ein Admin erzeugt einen Invite, der für eine konkrete
                E-Mail-Adresse und eine begrenzte Zeit gültig ist. Über diesen Link kann der neue Nutzer seinen Account selbst erstellen.
            </p>
        </div>
    </div>

    <div class="wiki-section" id="technik">
        <h2>Technische Dokumentation</h2>

        <h3>Projektidee</h3>
        <p>
            Das Projekt ist eine leichtgewichtige Webanwendung zur Dokumentation von Trainingseinheiten.
            Der Schwerpunkt liegt auf Verständlichkeit, einfacher Pflege und einer klaren Codebasis.
        </p>

        <h3>Technischer Aufbau</h3>
        <ul>
            <li>PHP-Anwendung ohne großes Framework</li>
            <li>serverseitig gerenderte Seiten</li>
            <li>Datenbankzugriff über PDO</li>
            <li>Session-basierte Anmeldung</li>
            <li>kleine Funktionsmodule über <code>includes/</code></li>
            <li>bewusst einfache Struktur statt hoher technischer Komplexität</li>
        </ul>

        <h3>Aktueller Funktionsstand</h3>
        <ul>
            <li>manuelles Anlegen und Bearbeiten von Trainingseinheiten</li>
            <li>direktes Schnell-Update von Einheitstyp, RPE und Fitnessgefühl in der Tabellenansicht</li>
            <li>Dashboard mit Kennzahlen für 7 und 30 Tage</li>
            <li>Formkurven-Grafik auf Basis der letzten 180 Tage</li>
            <li>CSV-Export der sichtbaren Einheiten</li>
            <li>Invite-basierte Registrierung neuer Nutzer</li>
            <li>Admin-Bereiche für Nutzerverwaltung und Invite-Verwaltung</li>
            <li>Strava-OAuth-Anbindung mit Import letzter Aktivitäten inkl. Pulsdaten</li>
            <li>Progressive Web App (PWA): installierbar auf iOS und Android, Service Worker für Asset-Caching</li>
            <li>Mobile UX: Bottom Navigation, Card-Layouts für Einheiten und Dashboard, mobil optimiertes Bearbeitungsformular</li>
        </ul>

        <h3>Wichtige Dateien und Bereiche</h3>
        <ul>
            <li><code>dashboard.php</code> – Übersichtsseite mit Kennzahlen, Verlaufsgrafik und letzten Einheiten</li>
            <li><code>entries.php</code> – Liste der sichtbaren eigenen Einheiten inkl. Quick-Update (Desktop) und Card-View (Mobile)</li>
            <li><code>entry_form.php</code> – neue Einheit anlegen</li>
            <li><code>edit_entry.php</code> – vollständige Bearbeitung einer Einheit; auf Mobile priorisiert: Notizen, RPE, Fitness zuerst</li>
            <li><code>update_quick_entry.php</code> – schnelle Änderungen direkt in der Tabelle speichern</li>
            <li><code>delete_entry.php</code> – Eintrag löschen</li>
            <li><code>export_entries.php</code> – CSV-Export der Einheiten</li>
            <li><code>admin_users.php</code> – Admin-Übersicht zu Nutzern, Aktivstatus, Strava-Status und Eintragsanzahl</li>
            <li><code>admin_invites.php</code> – Admin-Oberfläche zum Erzeugen und Verwalten von Invites</li>
            <li><code>register.php</code> – Registrierung über Invite-Link</li>
            <li><code>login.php</code> / <code>logout.php</code> – Anmeldung und Abmeldung</li>
            <li><code>strava_connect.php</code> – Start des OAuth-Flows zu Strava</li>
            <li><code>strava_callback.php</code> – Verarbeitung des OAuth-Callbacks und Speichern der Tokens</li>
            <li><code>strava_import.php</code> – Laden und Importieren aktueller Strava-Aktivitäten; Card-View auf Mobile</li>
            <li><code>manifest.json</code> – PWA-Manifest: App-Name, Icons, Startseite, Vollbild-Modus</li>
            <li><code>sw.js</code> – Service Worker: Asset-Caching für schnellere Ladezeiten</li>
            <li><code>assets/icons/</code> – App-Icons (192×192 und 512×512) für PWA und Home-Bildschirm</li>
            <li><code>assets/css/training-modern.css</code> – Haupt-Stylesheet inkl. Mobile-UX (Bottom Nav, Cards, Rating-Buttons)</li>
            <li><code>includes/auth.php</code> – Login-Schutz, Session-Helfer und Admin-Prüfungen</li>
            <li><code>includes/db.php</code> – Datenbankverbindung</li>
            <li><code>includes/header.php</code> / <code>includes/footer.php</code> – gemeinsames Layout, Desktop-Nav und mobile Bottom-Nav inkl. Mehr-Overlay</li>
            <li><code>includes/entry_repository.php</code> – gemeinsame Ladefunktion für sichtbare Einheiten</li>
            <li><code>includes/strava_client.php</code> – Laden, Refresh und API-Zugriff für Strava</li>
        </ul>

        <h3>Authentifizierung und Rollen</h3>
        <p>
            Geschützte Seiten verwenden einen Session-basierten Login. In <code>includes/auth.php</code> wird der
            Session-Name aus der Konfiguration geladen und es stehen Hilfsfunktionen wie <code>requireLogin()</code>,
            <code>currentUserId()</code>, <code>isAdmin()</code> und <code>requireAdmin()</code> zur Verfügung.
        </p>
        <ul>
            <li><strong>Rollenmodell:</strong> Nutzer haben die Rolle <code>admin</code> oder <code>user</code>.</li>
            <li><strong>Login:</strong> Anmeldung ist per Benutzername oder E-Mail möglich.</li>
            <li><strong>Aktivstatus:</strong> Nur aktive Nutzer können sich anmelden.</li>
            <li><strong>Admin-Schutz:</strong> Admin-Seiten werden serverseitig über <code>requireAdmin()</code> geschützt.</li>
        </ul>

        <h3>Nutzeranlage und Invite-Flow</h3>
        <p>
            Die produktive Nutzeranlage erfolgt über Einladungslinks. Ein Invite enthält keinen offen gespeicherten Token,
            sondern nur einen SHA-256-Hash des Tokens in der Datenbank. Der echte Registrierungslink wird nur direkt nach dem
            Erzeugen angezeigt.
        </p>
        <ul>
            <li>Invites werden für eine konkrete E-Mail-Adresse erzeugt.</li>
            <li>Ein Invite ist standardmäßig 2 Tage gültig.</li>
            <li><code>register.php</code> akzeptiert nur ungenutzte und nicht abgelaufene Invites.</li>
            <li>Bei erfolgreicher Registrierung wird der Invite über <code>used_at</code> als verbraucht markiert.</li>
            <li>Neue Registrierungen erhalten standardmäßig die Rolle <code>user</code>.</li>
        </ul>

        <h3>Logik der Einheiten</h3>
        <p>
            Jede Einheit gehört genau zu einem Nutzer. Sichtbare Einheiten werden über <code>getVisibleEntriesForUser()</code>
            geladen. Dabei werden aktuell nur Einträge mit <code>is_hidden = 0</code> berücksichtigt.
        </p>
        <p>Typische Felder einer Einheit sind:</p>
        <ul>
            <li><code>activity_date</code></li>
            <li><code>title</code></li>
            <li><code>sport_type</code></li>
            <li><code>training_type</code></li>
            <li><code>distance_km</code></li>
            <li><code>duration_min</code></li>
            <li><code>rpe</code></li>
            <li><code>fitness_feeling</code></li>
            <li><code>avg_heart_rate</code></li>
            <li><code>notes</code></li>
            <li><code>source</code> (z. B. <code>manual</code> oder <code>strava</code>)</li>
            <li><code>source_activity_id</code> für referenzierte Importquellen</li>
            <li><code>is_hidden</code> zur Ausblendung statt Anzeige in den Standardlisten</li>
        </ul>

        <h3>Dashboard und Kennzahlen</h3>
        <p>Das Dashboard bildet nicht nur eine Startseite, sondern bereits die zentrale Auswertungslogik des Projekts.</p>
        <ul>
            <li>Summen für Kilometer und Belastung der letzten 7 und 30 Tage</li>
            <li>durchschnittliches Fitnessgefühl für 7 und 30 Tage</li>
            <li>Liste der letzten 5 sichtbaren Einheiten</li>
            <li>Formkurve über 180 Tage mit gleitenden Fenstern</li>
        </ul>
        <p>
            Die Belastung wird aktuell als <code>duration_min * rpe</code> berechnet, sofern beide Werte vorhanden sind.
            Für die Formkurve werden Tageswerte aggregiert und daraus rollierende 7- und 30-Tage-Werte gebildet.
        </p>

        <h3>Strava-Integration</h3>
        <p>
            Die Strava-Anbindung ist als vollständiger OAuth-Flow umgesetzt. Nutzer verbinden ihr Konto über
            <code>strava_connect.php</code>, anschließend verarbeitet <code>strava_callback.php</code> den Rückruf,
            prüft den OAuth-State und speichert die Verbindung.
        </p>
        <ul>
            <li>Strava-Tokens werden nutzerbezogen in <code>strava_connections</code> gespeichert.</li>
            <li>Vor API-Zugriffen wird ein ablaufendes Token automatisch aktualisiert.</li>
            <li>Importiert werden Distanz, Dauer und durchschnittlicher Puls (<code>average_heartrate</code>) aus der Strava-API.</li>
            <li>Bereits importierte Aktivitäten werden über <code>source = 'strava'</code> und <code>source_activity_id</code> erkannt.</li>
            <li>Doppelte Imports werden still abgefangen.</li>
        </ul>

        <h3>Konfiguration</h3>
        <p>
            Zentrale Konfigurationswerte liegen in <code>includes/config.php</code>. Dort sind unter anderem der Session-Name,
            Datenbankzugang und die Strava-Konfiguration hinterlegt. Sensible Werte gehören ausschließlich in die Konfiguration
            bzw. in geschützte Serverumgebungen und nicht in diese Wiki-Seite.
        </p>

        <h3>Interne Hinweise / technische Schulden</h3>
        <ul>
            <li><code>create_user.php</code> wirkt wie ein manuelles Hilfs- oder Setup-Skript und sollte nicht produktiv öffentlich erreichbar sein.</li>
            <li><code>create_invite.php</code> ist funktional teilweise durch <code>admin_invites.php</code> überholt und sollte mittelfristig entweder entfernt oder klar als Altpfad markiert werden.</li>
            <li>Die Wiki beschreibt absichtlich keine Secrets, Passwörter oder API-Keys.</li>
        </ul>

        <div class="wiki-note">
            Auf dieser Seite sollten keine Passwörter, API-Keys, Secrets oder andere sensible Betriebsdaten dokumentiert werden.
        </div>

        <h3>Grundprinzipien</h3>
        <ul>
            <li>einfach statt überladen</li>
            <li>praktisch statt maximal komplex</li>
            <li>persönliche Dokumentation statt sozialer Plattform</li>
            <li>lesbare und verständliche Codebasis</li>
        </ul>
    </div>

    <div class="wiki-section" id="roadmap">
        <h2>Backlog &amp; Prioritäten</h2>
        <p>
            Hier wird festgehalten, was bereits umgesetzt wurde, was als nächstes geplant ist und welche
            technischen Themen noch offen sind. Die Liste wird laufend aktualisiert.
            Abgeschlossene Änderungen sind im <a href="/training/changelog.php">Changelog</a> dokumentiert.
        </p>

        <h3>Bereits umgesetzt</h3>
        <ul>
            <li>Einheiten erfassen und bearbeiten</li>
            <li>direktes Quick-Update in der Einheitenübersicht</li>
            <li>persönliche Notizen zu Einheiten</li>
            <li>Dashboard mit Kennzahlen und Formkurve</li>
            <li>Invite-basierte Nutzeranlage</li>
            <li>Admin-Nutzerverwaltung</li>
            <li>Admin-Invite-Verwaltung</li>
            <li>Strava-Import inkl. Pulsdaten</li>
            <li>CSV-Export der Einheiten</li>
            <li>Modernes Frontend mit Theme-Umschalter (Modern/Classic)</li>
            <li>Einheiten-Tabelle mit konfigurierbaren Spalten, Drag-to-resize und Breiteneinstellung</li>
            <li>Security Patch v1.1.0 (CSRF, DELETE via POST, Open Redirect, Host-Header, .env)</li>
            <li>Progressive Web App (PWA): installierbar auf iOS und Android, Service Worker</li>
            <li>Mobile UX v1.2.0: Bottom Navigation, Card-Layouts, optimiertes Bearbeitungsformular, Strava-Import als Cards</li>
        </ul>

        <h3>Priorität 1 — Nächste Schritte (hoher Nutzen)</h3>
        <p>Diese Punkte haben den größten direkten Mehrwert für die tägliche Nutzung.</p>
        <ul>
            <li>
                <strong>Notizen im Dashboard</strong> —
                Notizen sind das Kernstück der Einheiten-Dokumentation, tauchen auf dem Dashboard
                aber bisher nicht auf. Geplant ist ein Bereich der die zuletzt geschriebenen Notizen
                direkt sichtbar macht.
            </li>
            <li>
                <strong>Einheiten als Highlight markieren</strong> —
                Wichtige Läufe (Wettkämpfe, Bestleistungen, besondere Einheiten) sollen mit einem
                Flag oder Stern markiert werden können. In der Einheitenliste kann dann gezielt
                nach Highlights gefiltert werden.
            </li>
            <li>
                <strong>Filter und Suche in „Meine Einheiten"</strong> —
                Mit wachsender Datenmenge wird die Tabelle ohne Filter schwer zu navigieren.
                Geplant sind mindestens ein Zeitraumfilter und ein Filter nach Einheitstyp.
            </li>
        </ul>

        <h3>Priorität 2 — Nächster Patch</h3>
        <p>Sinnvolle Verbesserungen die etwas mehr Aufwand erfordern oder noch offen diskutiert werden.</p>
        <ul>
            <li>
                <strong>RPE und Fitness — Eingabe überarbeiten</strong> —
                Die aktuellen Schieberegler (Slider) funktionieren, sind aber in der Darstellung
                unübersichtlich und in der Bedienung unnötig, da die Zahl sowieso manuell eingegeben wird.
                Geplant ist eine schlankere Variante, z. B. ein einfaches Zahlenfeld oder eine
                Schaltflächen-Reihe (1–10).
            </li>
            <li>
                <strong>Serverseitige Eingabe-Validierung</strong> —
                Felder wie <code>activity_date</code>, <code>sport_type</code> und Wertebereiche
                (z. B. RPE 1–10) werden aktuell clientseitig geprüft, aber nicht konsequent serverseitig
                validiert. Betrifft <code>entry_form.php</code> und <code>edit_entry.php</code>.
            </li>
            <li>
                <strong>Strava Token Retry-Logik</strong> —
                Wenn ein Strava-Zugriffstoken während eines laufenden Imports abläuft, schlägt der
                Request fehl. Geplant ist eine automatische Retry-Logik nach erfolgreichem Token-Refresh.
            </li>
            <li>
                <strong>Soft-Delete / Papierkorb</strong> —
                Gelöschte Einheiten sind aktuell unwiderruflich weg. Ein Papierkorb oder eine
                Ausblend-Funktion mit Wiederherstellungsoption würde versehentliche Löschungen abfangen.
            </li>
        </ul>

        <h3>Priorität 3 — Technische Schuld (kein Zeitdruck)</h3>
        <p>
            Diese Punkte haben keinen direkten Nutzereinfluss, verbessern aber die Wartbarkeit
            und Sauberkeit der Codebasis langfristig.
        </p>
        <ul>
            <li>
                <strong>Code-Duplikation bereinigen</strong> —
                <code>create_invite.php</code> und <code>admin_invites.php</code> enthalten identische
                Logik zur Invite-Erstellung. Mittelfristig sollte das in eine gemeinsame Funktion
                ausgelagert und <code>create_invite.php</code> als Altpfad entfernt werden.
            </li>
            <li>
                <strong>Inline-JavaScript auslagern</strong> —
                Das JavaScript in <code>entries.php</code> (Spalten-Toggle, Drag-Resize, Breitensteuerung)
                sollte in eine externe Datei <code>assets/js/entries.js</code> ausgelagert werden.
                Macht den Code übersichtlicher und ermöglicht Browser-Caching.
            </li>
            <li>
                <strong>Altpfade und Hilfsskripte aufräumen</strong> —
                Dateien wie <code>create_user.php</code> oder ältere Setup-Skripte sollten geprüft
                und wenn nicht mehr nötig entfernt oder explizit gesichert werden.
            </li>
            <li>
                <strong>GROUP BY Kompatibilität in admin_users.php</strong> —
                Die aktuelle Abfrage könnte auf MySQL-Instanzen mit striktem
                <code>ONLY_FULL_GROUP_BY</code>-Modus Fehler werfen. Betrifft nur den Admin-Bereich
                und ist aktuell wahrscheinlich kein aktives Problem.
            </li>
        </ul>

        <h3>Leitgedanke</h3>
        <p>
            Das Tool soll bewusst schlank bleiben. Es ist nicht als Ersatz für große Plattformen gedacht,
            sondern als einfache und nützliche Möglichkeit, das eigene Training für sich selbst festzuhalten.
        </p>
    </div>

    <div class="wiki-note" style="margin-top: 32px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div>
            <strong>Changelog</strong><br>
            <span style="font-size:0.9rem; color:#64748b;">Alle Versionen, Features und Sicherheitspatches im Überblick.</span>
        </div>
        <a class="button" href="/training/changelog.php">Changelog öffnen →</a>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
