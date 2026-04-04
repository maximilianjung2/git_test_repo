<?php
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wiki / Hilfe - Trainingsbereich</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
    <style>
        body {
            align-items: flex-start;
            padding: 24px 0;
        }

        .wiki-section {
            margin-top: 32px;
            text-align: left;
        }

        .wiki-section h2 {
            margin-bottom: 12px;
        }

        .wiki-section h3 {
            margin-top: 24px;
            margin-bottom: 8px;
        }

        .wiki-section p,
        .wiki-section li {
            line-height: 1.6;
        }

        .wiki-intro {
            background: #f8f9fb;
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
            text-align: left;
        }

        .wiki-toc {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
            text-align: left;
        }

        .wiki-note {
            background: #fff8e6;
            border: 1px solid #f0d98c;
            border-radius: 12px;
            padding: 14px;
            margin: 18px 0;
            text-align: left;
        }

        .wiki-section code {
            background: #f3f3f3;
            padding: 2px 6px;
            border-radius: 6px;
            font-family: Consolas, Monaco, monospace;
            font-size: 0.95em;
        }

        .wiki-section ul {
            padding-left: 22px;
        }

        .wiki-links {
            margin-top: 20px;
        }

        .wiki-links a {
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .faq-item {
            margin-bottom: 22px;
        }

        .muted {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container wide">
        <h1>Wiki / Hilfe</h1>
        <p class="muted">Nutzerinfos, Projektdoku und ein Ort zum Nachschlagen.</p>

        <p class="wiki-links">
            <a class="button" href="/training/dashboard.php">Dashboard</a>
            <a class="button" href="/training/entries.php">Meine Einheiten</a>
            <a class="button" href="/training/login.php">Login</a>
        </p>

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
                <li><a href="#roadmap">Status und nächste Ideen</a></li>
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
                <li>Strava-OAuth-Anbindung mit Import letzter Aktivitäten</li>
            </ul>

            <h3>Wichtige Dateien und Bereiche</h3>
            <ul>
                <li><code>dashboard.php</code> – Übersichtsseite mit Kennzahlen, Verlaufsgrafik und letzten Einheiten</li>
                <li><code>entries.php</code> – Liste der sichtbaren eigenen Einheiten inkl. Quick-Update</li>
                <li><code>entry_form.php</code> – neue Einheit anlegen</li>
                <li><code>edit_entry.php</code> – vollständige Bearbeitung einer Einheit</li>
                <li><code>update_quick_entry.php</code> – schnelle Änderungen direkt in der Tabelle speichern</li>
                <li><code>delete_entry.php</code> – Eintrag löschen</li>
                <li><code>export_entries.php</code> – CSV-Export der Einheiten</li>
                <li><code>admin_users.php</code> – Admin-Übersicht zu Nutzern, Aktivstatus, Strava-Status und Eintragsanzahl</li>
                <li><code>admin_invites.php</code> – Admin-Oberfläche zum Erzeugen und Verwalten von Invites</li>
                <li><code>register.php</code> – Registrierung über Invite-Link</li>
                <li><code>login.php</code> / <code>logout.php</code> – Anmeldung und Abmeldung</li>
                <li><code>strava_connect.php</code> – Start des OAuth-Flows zu Strava</li>
                <li><code>strava_callback.php</code> – Verarbeitung des OAuth-Callbacks und Speichern der Tokens</li>
                <li><code>strava_import.php</code> – Laden und Importieren aktueller Strava-Aktivitäten</li>
                <li><code>includes/auth.php</code> – Login-Schutz, Session-Helfer und Admin-Prüfungen</li>
                <li><code>includes/db.php</code> – Datenbankverbindung</li>
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

            <h3>Admin-Funktionen</h3>
            <p>
                Die Admin-Funktionen sind nicht mehr nur vorbereitete Basislogik, sondern als eigene Bereiche im Projekt vorhanden.
            </p>

            <ul>
                <li><strong>Nutzerverwaltung:</strong> <code>admin_users.php</code> zeigt Benutzername, E-Mail, Rolle, Aktivstatus,
                    Registrierungsdatum, Strava-Verbindungsstatus und Anzahl sichtbarer Einheiten.</li>
                <li><strong>Statuswechsel:</strong> Admins können Nutzer aktivieren oder deaktivieren.</li>
                <li><strong>Selbstschutz:</strong> Der eigene Admin-Account kann über die Oberfläche nicht versehentlich deaktiviert werden.</li>
                <li><strong>Invite-Verwaltung:</strong> <code>admin_invites.php</code> zeigt offene, genutzte und abgelaufene Invites.</li>
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
                <li><code>notes</code></li>
                <li><code>source</code> (z. B. <code>manual</code> oder <code>strava</code>)</li>
                <li><code>source_activity_id</code> für referenzierte Importquellen</li>
                <li><code>is_hidden</code> zur Ausblendung statt Anzeige in den Standardlisten</li>
            </ul>

            <h3>Direktes Bearbeiten in der Übersicht</h3>
            <p>
                In <code>entries.php</code> können einige Felder direkt in der Tabelle geändert werden. Gespeichert werden dabei
                <code>training_type</code>, <code>rpe</code> und <code>fitness_feeling</code> über <code>update_quick_entry.php</code>.
                Die Eingaben werden serverseitig validiert, bevor das Update durchgeführt wird.
            </p>

            <h3>Dashboard und Kennzahlen</h3>
            <p>
                Das Dashboard bildet nicht nur eine Startseite, sondern bereits die zentrale Auswertungslogik des Projekts.
            </p>
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

            <h3>CSV-Export</h3>
            <p>
                Der CSV-Export stellt alle sichtbaren Einheiten des aktuell eingeloggten Nutzers als Download bereit.
                Exportiert werden unter anderem Datum, Titel, Sportart, Einheitstyp, Distanz, Dauer, RPE, Fitnessgefühl,
                Quelle und Notizen.
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
                <li>Importiert werden aktuelle Aktivitäten über die Strava-API.</li>
                <li>Bereits importierte Aktivitäten werden über <code>source = 'strava'</code> und <code>source_activity_id</code> erkannt.</li>
                <li>Doppelte Imports werden still abgefangen.</li>
            </ul>

            <h3>Datenmodell</h3>
            <p>Wichtige fachliche Objekte sind unter anderem:</p>
            <ul>
                <li><strong>users</strong> – Benutzerkonten mit Rolle, Aktivstatus und Login-Daten</li>
                <li><strong>training_entries</strong> – Trainingseinheiten der Nutzer</li>
                <li><strong>invites</strong> – Einladungen zur Registrierung</li>
                <li><strong>strava_connections</strong> – OAuth-Verbindung und Token-Daten je Nutzer</li>
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
            <h2>Status und nächste Ideen</h2>

            <h3>Bereits umgesetzt</h3>
            <ul>
                <li>Einheiten erfassen und bearbeiten</li>
                <li>direktes Quick-Update in der Einheitenübersicht</li>
                <li>persönliche Notizen zu Einheiten</li>
                <li>Dashboard mit Kennzahlen und Formkurve</li>
                <li>Invite-basierte Nutzeranlage</li>
                <li>Admin-Nutzerverwaltung</li>
                <li>Admin-Invite-Verwaltung</li>
                <li>Strava-Import</li>
                <li>CSV-Export der Einheiten</li>
            </ul>

            <h3>Mögliche nächste Schritte</h3>
            <ul>
                <li>Filter und Suche für <code>Meine Einheiten</code></li>
                <li>klarere Trennung zwischen Altpfaden und produktiven Admin-Pfaden</li>
                <li>Absicherung technischer Hilfsskripte und Aufräumen veralteter Dateien</li>
                <li>optionale Historie oder Soft-Delete-Verwaltung für ausgeblendete Einträge</li>
                <li>schrittweise Ausbau der Projektdokumentation</li>
            </ul>

            <h3>Leitgedanke</h3>
            <p>
                Das Tool soll bewusst schlank bleiben. Es ist nicht als Ersatz für große Plattformen gedacht,
                sondern als einfache und nützliche Möglichkeit, das eigene Training für sich selbst festzuhalten.
            </p>
        </div>
    </div>
</body>
</html>
