<?php
// Liefert die Lauftreff-Aktivitäten als HTML-Tabelle, passend zu
// public.css (.public-table mit data-label-Attributen für Mobile-Cards).
// Wird typischerweise von aktivitäten.html per fetch() eingebunden.

$secrets = require __DIR__ . '/secrets.php';
$db = new PDO(
    "mysql:host={$secrets['db']['host']};dbname={$secrets['db']['name']};charset={$secrets['db']['charset']}",
    $secrets['db']['user'],
    $secrets['db']['pass']
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $db->query("
    SELECT
        name,
        DATE_FORMAT(start_date_local, '%d.%m.%Y') AS datum,
        ROUND(distance / 1000, 2) AS km
    FROM strava_activities
    ORDER BY start_date_local DESC
");

echo '<table class="public-table">';
echo '  <thead><tr><th>Datum</th><th>Titel</th><th>Distanz</th></tr></thead>';
echo '  <tbody>';
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $datum = htmlspecialchars($row['datum']);
    $name  = htmlspecialchars($row['name']);
    $km    = number_format((float)$row['km'], 2, ',', '.') . ' km';
    echo "  <tr>";
    echo "      <td data-label=\"Datum\">$datum</td>";
    echo "      <td data-label=\"Titel\">$name</td>";
    echo "      <td data-label=\"Distanz\">$km</td>";
    echo "  </tr>";
}
echo '  </tbody>';
echo '</table>';
