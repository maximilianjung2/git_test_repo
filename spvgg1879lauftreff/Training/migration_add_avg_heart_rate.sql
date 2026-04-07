-- Migration: Durchschnittlichen Puls zu training_entries hinzufügen
-- Datum: 2026-04-07
-- Einmalig auf dem Server ausführen (z.B. via phpMyAdmin oder SSH)

ALTER TABLE training_entries
    ADD COLUMN avg_heart_rate SMALLINT UNSIGNED NULL DEFAULT NULL
        COMMENT 'Durchschnittlicher Puls in bpm'
        AFTER fitness_feeling;
