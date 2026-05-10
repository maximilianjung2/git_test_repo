<?php
/**
 * Geteilter Strava-Client.
 *
 * Wird vom Public-Bereich (callback.php, kilometer.php, kilometer_debug.php)
 * UND vom /training/-Bereich (Training/includes/strava_client.php) verwendet.
 *
 * Enthält ausschließlich netzwerk-bezogene Primitive:
 *   - Token-Anfrage (Refresh + Authorization-Code)
 *   - Authentifizierter API-GET
 *   - Ablauf-Check
 *
 * Wie Tokens gespeichert werden (Datei vs. DB) bleibt Sache der Aufrufer.
 *
 * Alle Funktionen geben strukturierte Ergebnis-Arrays zurück:
 *   ['ok' => bool, 'data' => mixed, 'http_code' => int, 'error' => ?string]
 * Aufrufer prüfen 'ok' und reagieren passend (Fallback, Fehler-Log, etc.).
 */

if (!function_exists('strava_token_request')) {

    /**
     * POST gegen den Strava-Token-Endpoint.
     * $extra muss mindestens 'grant_type' und je nach Flow Code/Refresh-Token enthalten.
     */
    function strava_token_request(string $clientId, string $clientSecret, array $extra): array
    {
        $ch = curl_init('https://www.strava.com/oauth/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(array_merge([
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
            ], $extra)),
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'data' => null, 'http_code' => 0, 'error' => $curlError ?: 'cURL-Fehler'];
        }

        $data = json_decode($response, true);

        if (
            $httpCode !== 200
            || !is_array($data)
            || empty($data['access_token'])
            || empty($data['refresh_token'])
        ) {
            return [
                'ok'        => false,
                'data'      => $data,
                'http_code' => $httpCode,
                'error'     => is_string($response) ? $response : 'Unbekannter Fehler',
            ];
        }

        return ['ok' => true, 'data' => $data, 'http_code' => $httpCode, 'error' => null];
    }

    /**
     * Tausch eines Refresh-Tokens gegen frische Tokens.
     */
    function strava_refresh_tokens(string $clientId, string $clientSecret, string $refreshToken): array
    {
        return strava_token_request($clientId, $clientSecret, [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * Tausch eines OAuth-Authorization-Codes gegen Tokens (Erst-Anbindung).
     */
    function strava_exchange_code(string $clientId, string $clientSecret, string $code): array
    {
        return strava_token_request($clientId, $clientSecret, [
            'grant_type' => 'authorization_code',
            'code'       => $code,
        ]);
    }

    /**
     * Authentifizierter GET gegen die Strava-API.
     * Gibt strukturiertes Ergebnis-Array zurück; auf 'ok' prüfen.
     */
    function strava_api_get(string $accessToken, string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'data' => null, 'http_code' => 0, 'error' => $curlError ?: 'cURL-Fehler'];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !is_array($data)) {
            return [
                'ok'        => false,
                'data'      => $data,
                'http_code' => $httpCode,
                'error'     => is_string($response) ? $response : 'Unbekannter Fehler',
            ];
        }

        return ['ok' => true, 'data' => $data, 'http_code' => $httpCode, 'error' => null];
    }

    /**
     * Prüft, ob ein Token-Refresh ansteht.
     * Standard-Buffer: 60s vor Ablauf, um Race-Conditions zu vermeiden.
     */
    function strava_token_needs_refresh(array $tokens, int $bufferSeconds = 60): bool
    {
        return time() >= ((int)($tokens['expires_at'] ?? 0) - $bufferSeconds);
    }

    /**
     * Atomares Speichern der Tokens-Datei mit Snapshot der vorherigen
     * Version unter <file>.bak. Bei Datenkorruption / versehentlichem
     * Überschreiben hat man so immer die letzte funktionierende Version.
     *
     * Wenn JSON-Encoding fehlschlägt oder file_put_contents 0 / false
     * zurückgibt, wird false zurückgegeben — der Aufrufer kann reagieren.
     */
    function strava_save_tokens_with_backup(string $file, array $tokens): bool
    {
        // Vor dem Überschreiben: alte Version sichern.
        if (file_exists($file)) {
            @copy($file, $file . '.bak');
        }
        $json = json_encode($tokens);
        if ($json === false) {
            return false;
        }
        $bytes = @file_put_contents($file, $json, LOCK_EX);
        return $bytes !== false && $bytes > 0;
    }
}
