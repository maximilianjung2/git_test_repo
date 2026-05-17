# Haaschter Runden 2026 – Setup-Anleitung

## 1. Datenbank-Tabelle anlegen

- phpMyAdmin (oder ähnliches Tool) öffnen
- Datenbank `dbs14323265` wählen
- SQL-Tab öffnen
- Inhalt von `SETUP.sql` kopieren und ausführen

Ergebnis: Tabelle `haaschterrunden2026_teilnehmer` wird erstellt.

## 2. Dateien auf den Server laden

Per FTP (oder Git) den Ordner `HaaschterRunden26/` komplett in das Archiv-Verzeichnis hochladen:

```
/htdocs/Archiv/HaaschterRunden26/
  ├── index.html
  ├── anmeldung.html
  ├── anmeldung.php
  ├── runden.php
  ├── update_runden.php
  └── SETUP.sql
```

## 3. Zugriff testen

- Landing Page: `https://deinedomain.de/Archiv/HaaschterRunden26/`
- Anmeldung: `.../Archiv/HaaschterRunden26/anmeldung.html`
- Teilnehmerliste: `.../Archiv/HaaschterRunden26/runden.php`

## 4. Optional: Link von der Hauptseite

In der `index.html` (Root) kann ein Button zur neuen Seite hinzugefügt werden:

```html
<a class="btn btn-ghost btn-block" href="Archiv/HaaschterRunden26/">Haaschter Runden 2026</a>
```

## Datenbank-Config

Die PHP-Dateien laden automatisch `../../secrets.php` und nutzen die dort hinterlegten DB-Zugangsdaten. Keine Passwörter mehr im Code.
