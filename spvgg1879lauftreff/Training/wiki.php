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
                Diese Seite dient sowohl als Hilfe für Nutzer als auch als technische Dokumentation des Projekts.
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
                <h3>Wofür sind die Notizen gedacht?</h3>
                <p>
                    Im Notizfeld können persönliche Eindrücke festgehalten werden, zum Beispiel Tagesform, Wetter,
                    besondere Beobachtungen, Beschwerden oder ein kurzes Fazit zur Einheit.
                </p>
            </div>

            <div class="faq-item">
                <h3>Kann ich Daten aus Strava importieren?</h3>
                <p>
                    Ja, sofern die Strava-Anbindung eingerichtet ist. Importierte Einheiten werden dann im Tool angezeigt
                    und können bei Bedarf ergänzt werden.
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
                    Das Tool ist aktuell auf die persönliche Nutzung ausgelegt. Nutzer sehen ihre eigenen Einheiten.
                    Öffentliche Profile oder soziale Vergleichsfunktionen stehen nicht im Vordergrund.
                </p>
            </div>

            <div class="faq-item">
                <h3>Wie werden neue Nutzer angelegt?</h3>
                <p>
                    Neue Nutzer werden über einen Einladungslink angelegt. Dazu wird ein Invite erzeugt, über den sich
                    ein neuer Nutzer selbst registrieren kann.
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
                <li>bewusst einfache Struktur statt hoher technischer Komplexität</li>
            </ul>

            <h3>Wichtige Dateien und Bereiche</h3>
            <ul>
                <li><code>dashboard.php</code> – Übersichtsseite</li>
                <li><code>entries.php</code> – Liste der eigenen Einheiten</li>
                <li><code>entry_form.php</code> – neue Einheit anlegen</li>
                <li><code>edit_entry.php</code> – Einheit bearbeiten</li>
                <li><code>update_quick_entry.php</code> – schnelle Änderungen direkt in der Tabelle speichern</li>
                <li><code>export_entries.php</code> – CSV-Export der Einheiten</li>
                <li><code>create_invite.php</code> – Invite-Link für neue Nutzer erzeugen</li>
                <li><code>register.php</code> – Registrierung über Invite-Link</li>
                <li><code>includes/auth.php</code> – Login-Schutz und Session-Helfer</li>
                <li><code>includes/db.php</code> – Datenbankverbindung</li>
                <li><code>includes/entry_repository.php</code> – gemeinsame Ladefunktion für sichtbare Einheiten</li>
            </ul>

            <h3>Authentifizierung</h3>
            <p>
                Geschützte Seiten verwenden einen Session-basierten Login. Seiten innerhalb des Trainingsbereichs
                werden in der Regel über <code>requireLogin()</code> abgesichert.
            </p>

            <h3>Nutzeranlage</h3>
            <p>
                Neue Nutzer sollen über den Invite-Flow angelegt werden. Dabei wird ein Registrierungslink erstellt,
                den der neue Nutzer verwenden kann, um seinen Account selbst anzulegen.
            </p>

            <h3>Admin-Funktionen</h3>
            <p>
                Das Projekt unterstützt aktuell eine einfache Rollenlogik mit den Rollen <code>admin</code> und <code>user</code>.
                Die Rollen werden direkt in der Datenbank verwaltet und nicht über das Frontend geändert.
            </p>

            <p>
                Admin-Rechte bilden die technische Grundlage dafür, bestimmte Bereiche oder Funktionen nur für berechtigte Nutzer
                freizugeben. Aktuell ist diese Logik vor allem als Basis für spätere Verwaltungsfunktionen gedacht.
            </p>

            <ul>
                <li><strong>Rollenmodell:</strong> Nutzer haben die Rolle <code>admin</code> oder <code>user</code>.</li>
                <li><strong>Rechtevergabe:</strong> Die Rolle wird direkt in der Datenbank gesetzt oder geändert.</li>
                <li><strong>Standard für neue Nutzer:</strong> Neue Registrierungen erhalten standardmäßig die Rolle <code>user</code>.</li>
                <li><strong>Admin-Schutz im Code:</strong> Über die Auth-Logik können Bereiche gezielt nur für Admins freigegeben werden.</li>
            </ul>

            <p>
                Ein größeres Admin-Frontend mit Nutzerübersicht oder Invite-Verwaltung ist als möglicher nächster Schritt vorgesehen,
                aber noch nicht vollständig Bestandteil des aktuellen produktiven Funktionsumfangs.
            </p>

            <div class="wiki-note">
                Auf dieser Seite sollten keine Passwörter, API-Keys, Secrets oder andere sensible Betriebsdaten dokumentiert werden.
            </div>

            <h3>Datenmodell</h3>
            <p>Wichtige fachliche Objekte sind unter anderem:</p>
            <ul>
                <li><strong>users</strong> – Benutzerkonten</li>
                <li><strong>training_entries</strong> – Trainingseinheiten der Nutzer</li>
                <li><strong>invites</strong> – Einladungen zur Registrierung</li>
            </ul>

            <h3>Logik der Einheiten</h3>
            <p>
                Jede Einheit gehört genau zu einem Nutzer. In <code>entries.php</code> werden nur die Einheiten des
                aktuell eingeloggten Nutzers geladen, die nicht ausgeblendet sind.
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
                <li><code>source</code></li>
            </ul>

            <h3>CSV-Export</h3>
            <p>
                Der CSV-Export stellt alle sichtbaren Einheiten des aktuell eingeloggten Nutzers als Download bereit.
                Die Datei ist maschinenfreundlich aufgebaut, damit sie in anderen Tools weiterverwendet werden kann.
            </p>

            <h3>Strava-Integration</h3>
            <p>
                Das Projekt unterstützt eine Strava-Anbindung zum Import von Aktivitäten. Importierte Daten werden
                im System erfasst und über das Feld <code>source</code> gekennzeichnet.
            </p>

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
                <li>persönliche Notizen zu Einheiten</li>
                <li>Dashboard</li>
                <li>Invite-basierte Nutzeranlage</li>
                <li>Strava-Import</li>
                <li>CSV-Export der Einheiten</li>
            </ul>

            <h3>Mögliche nächste Schritte</h3>
            <ul>
                <li>Filter für <code>Meine Einheiten</code></li>
                <li>bessere Visualisierung von Belastung und Fitness</li>
                <li>weitere Aufräumarbeiten in der Codebasis</li>
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
