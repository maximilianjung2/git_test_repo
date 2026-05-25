<?php
/**
 * Helper für die Verwaltung mehrerer Lauftreff-Termine.
 *
 * Speicherort: ../naechster_lauf.json
 * Neues Format: ein Array von Termin-Objekten mit stabiler ID.
 * Altes Format (einzelnes Objekt) wird beim Lesen automatisch migriert,
 * sodass bestehende Daten nicht verloren gehen.
 */

if (!function_exists('termine_laden')) {

    function termine_laden(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }
        $raw = file_get_contents($file);
        $data = json_decode($raw, true);
        if ($data === null) {
            return [];
        }

        // Migration: einzelnes Objekt im Alt-Format → Array
        if (is_array($data) && !empty($data['datum']) && !isset($data[0])) {
            $data = [$data];
        }
        // Leeres Objekt = keine Termine
        if (is_array($data) && empty($data)) {
            return [];
        }
        // Sicher: nur Array, keine assoziativen Strukturen
        if (!is_array($data) || !array_is_list($data)) {
            return [];
        }

        // Jeden Termin normalisieren: ID vergeben falls fehlt
        $termine = [];
        foreach ($data as $t) {
            if (!is_array($t) || empty($t['datum']) || empty($t['titel'])) {
                continue;
            }
            if (empty($t['id'])) {
                $t['id'] = bin2hex(random_bytes(6));
            }
            $termine[] = [
                'id'           => (string)$t['id'],
                'datum'        => (string)$t['datum'],
                'uhrzeit'      => (string)($t['uhrzeit']      ?? ''),
                'titel'        => (string)$t['titel'],
                'treffpunkt'   => (string)($t['treffpunkt']   ?? ''),
                'beschreibung' => (string)($t['beschreibung'] ?? ''),
                'kategorie'    => in_array($t['kategorie'] ?? '', ['laufen', 'power_walking'])
                                    ? (string)$t['kategorie']
                                    : 'laufen',
            ];
        }
        return $termine;
    }

    function termine_speichern(string $file, array $termine): bool
    {
        // Sortieren nach Datum + Uhrzeit aufsteigend, damit das File
        // lesbar bleibt und der Reader keine Sortier-Logik braucht.
        usort($termine, function ($a, $b) {
            $aTs = strtotime(($a['datum'] ?? '') . ' ' . ($a['uhrzeit'] ?? '00:00'));
            $bTs = strtotime(($b['datum'] ?? '') . ' ' . ($b['uhrzeit'] ?? '00:00'));
            return ($aTs ?: 0) - ($bTs ?: 0);
        });
        $json = json_encode($termine, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        return file_put_contents($file, $json, LOCK_EX) !== false;
    }

    /**
     * Gibt den nächsten zukünftigen Termin zurück (1h Karenz nach Beginn),
     * oder null wenn keiner mehr ansteht.
     */
    function termine_naechster(array $termine): ?array
    {
        $now = time();
        $candidates = [];
        foreach ($termine as $t) {
            $ts = strtotime($t['datum'] . ' ' . ($t['uhrzeit'] ?: '23:59'));
            if ($ts !== false && $ts >= $now - 3600) {
                $candidates[] = ['ts' => $ts, 'termin' => $t];
            }
        }
        if (empty($candidates)) return null;
        usort($candidates, fn($a, $b) => $a['ts'] - $b['ts']);
        return $candidates[0]['termin'];
    }

    /**
     * Gibt je den nächsten Termin pro Kategorie zurück.
     * Ergebnis: ['laufen' => array|null, 'power_walking' => array|null]
     */
    function termine_naechster_pro_kategorie(array $termine): array
    {
        $now = time();
        $result = ['laufen' => null, 'power_walking' => null];
        $best   = ['laufen' => PHP_INT_MAX, 'power_walking' => PHP_INT_MAX];

        foreach ($termine as $t) {
            $kat = $t['kategorie'] ?? 'laufen';
            if (!isset($result[$kat])) continue;
            $ts = strtotime($t['datum'] . ' ' . ($t['uhrzeit'] ?: '23:59'));
            if ($ts !== false && $ts >= $now - 3600 && $ts < $best[$kat]) {
                $best[$kat]   = $ts;
                $result[$kat] = $t;
            }
        }
        return $result;
    }

    function termine_finden(array $termine, string $id): ?array
    {
        foreach ($termine as $t) {
            if (($t['id'] ?? '') === $id) return $t;
        }
        return null;
    }
}
