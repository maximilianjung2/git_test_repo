-- ============================================================
-- Setup für die Haaschter Runden 2026
-- ============================================================
-- Dieses SQL-Skript erstellt die benötigte Tabelle.
-- Bitte in der Datenbank dbs14323265 ausführen (z. B. über phpMyAdmin).

CREATE TABLE IF NOT EXISTS haaschterrunden2026_teilnehmer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Vorname VARCHAR(100) NOT NULL,
    Typ TINYINT NOT NULL COMMENT '1 = laufen, 2 = walken',
    runden INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
