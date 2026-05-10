<?php
/**
 * Health-/Status-Checks für das öffentliche Wiki-Dashboard.
 *
 * Alle Funktionen geben strukturierte Status-Einträge mit den Feldern:
 *   ['title' => string, 'state' => 'ok'|'warn'|'error'|'unknown',
 *    'label' => string, 'detail' => string]
 *
 * Wichtig: keine externen API-Calls — alle Checks arbeiten nur auf
 * lokalem Zustand (Token-Datei, Cache-Datei, DB). Damit ist das
 * Dashboard schnell und schont das Strava-Rate-Limit.
 */

if (!function_exists('relative_time_ago')) {
    function relative_time_ago(int $timestamp): string
    {
        $diff = time() - $timestamp;
        if ($diff < 0)      return 'in der Zukunft';
        if ($diff < 60)     return 'gerade eben';
        if ($diff < 3600)   return 'vor ' . (int)floor($diff / 60) . ' Min';
        if ($diff < 86400)  return 'vor ' . (int)floor($diff / 3600) . ' Std';
        return 'vor ' . (int)floor($diff / 86400) . ' Tag(en)';
    }

    function relative_time_until(int $timestamp): string
    {
        $diff = $timestamp - time();
        if ($diff <= 0) return 'abgelaufen';
        if ($diff < 60) return 'in ' . $diff . ' Sek';
        if ($diff < 3600) return 'in ' . (int)floor($diff / 60) . ' Min';
        if ($diff < 86400) {
            $h = (int)floor($diff / 3600);
            $m = (int)floor(($diff % 3600) / 60);
            return "in {$h} Std {$m} Min";
        }
        return 'in ' . (int)floor($diff / 86400) . ' Tag(en)';
    }

    function health_check_token(string $tokenFile): array
    {
        if (!file_exists($tokenFile)) {
            return [
                'title' => 'Strava-Token',
                'state' => 'error',
                'label' => 'Nicht autorisiert',
                'detail' => 'strava_tokens.json nicht vorhanden – Re-Auth nötig.',
            ];
        }
        $tokens = json_decode(file_get_contents($tokenFile), true);
        if (!is_array($tokens) || empty($tokens['access_token']) || empty($tokens['refresh_token'])) {
            return [
                'title' => 'Strava-Token',
                'state' => 'error',
                'label' => 'Token-Datei kaputt',
                'detail' => 'Inhalt ist leer oder ungültig – Re-Auth nötig.',
            ];
        }
        $expiresAt = (int)($tokens['expires_at'] ?? 0);
        $now       = time();

        if ($expiresAt <= $now) {
            return [
                'title' => 'Strava-Token',
                'state' => 'warn',
                'label' => 'Access-Token abgelaufen',
                'detail' => 'Wird beim nächsten Aufruf automatisch refresht.',
            ];
        }
        if ($expiresAt - $now < 3600) {
            return [
                'title' => 'Strava-Token',
                'state' => 'warn',
                'label' => 'Läuft bald ab',
                'detail' => 'Access-Token endet ' . relative_time_until($expiresAt) . '.',
            ];
        }
        return [
            'title' => 'Strava-Token',
            'state' => 'ok',
            'label' => 'Gültig',
            'detail' => 'Endet ' . relative_time_until($expiresAt) . '.',
        ];
    }

    function health_check_cache(string $cacheFile, int $ttl): array
    {
        if (!file_exists($cacheFile)) {
            return [
                'title' => 'Letzter Sync',
                'state' => 'unknown',
                'label' => 'Noch kein Cache',
                'detail' => 'kilometer.php wurde noch nicht aufgerufen.',
            ];
        }
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (!is_array($cached) || !isset($cached['updated_at'])) {
            return [
                'title' => 'Letzter Sync',
                'state' => 'warn',
                'label' => 'Cache unbrauchbar',
                'detail' => 'Datei vorhanden, aber kein gültiges JSON.',
            ];
        }
        $age = time() - (int)$cached['updated_at'];
        $km  = $cached['km'] ?? '?';

        // Innerhalb TTL = grün, bis 4*TTL = gelb (graceful), älter = rot.
        if ($age <= $ttl) {
            $state = 'ok';
        } elseif ($age <= $ttl * 4) {
            $state = 'warn';
        } else {
            $state = 'error';
        }
        return [
            'title' => 'Letzter Sync',
            'state' => $state,
            'label' => $km . ' km',
            'detail' => 'Aktualisiert ' . relative_time_ago((int)$cached['updated_at']) . '.',
        ];
    }

    function health_check_db(array $dbConfig): array
    {
        try {
            $pdo = new PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}",
                $dbConfig['user'],
                $dbConfig['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 3]
            );
            $count = (int)$pdo->query('SELECT COUNT(*) FROM strava_activities')->fetchColumn();
            return [
                'title' => 'Datenbank',
                'state' => 'ok',
                'label' => 'Verbunden',
                'detail' => number_format($count, 0, ',', '.') . ' Aktivitäten gespeichert.',
            ];
        } catch (Throwable $e) {
            return [
                'title' => 'Datenbank',
                'state' => 'error',
                'label' => 'Nicht erreichbar',
                'detail' => $e->getMessage(),
            ];
        }
    }

    function render_status_card(array $entry): string
    {
        $stateClass = 'status-' . htmlspecialchars($entry['state']);
        return sprintf(
            '<div class="status-card %s"><div class="status-dot"></div>'
                . '<div class="status-body"><div class="status-title">%s</div>'
                . '<div class="status-label">%s</div>'
                . '<div class="status-detail">%s</div></div></div>',
            $stateClass,
            htmlspecialchars($entry['title']),
            htmlspecialchars($entry['label']),
            htmlspecialchars($entry['detail'])
        );
    }
}
