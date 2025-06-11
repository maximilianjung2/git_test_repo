<?php
$db = new PDO('mysql:host=database-5018019376.webspace-host.com;dbname=dbs14323265', 'dbu302398', 'lauftreffhomepage');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Aktivitäten laden
$stmt = $db->query("SELECT name, DATE(start_date_local) AS datum, ROUND(distance / 1000, 2) AS km FROM strava_activities ORDER BY start_date_local DESC");

echo "<h2>Spvgg. Hainstadt Läufe</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Datum</th><th>Titel</th><th>Distanz (km)</th></tr>";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>
            <td>{$row['datum']}</td>
            <td>{$row['name']}</td>
            <td>{$row['km']}</td>
          </tr>";
}
echo "</table>";
?>
