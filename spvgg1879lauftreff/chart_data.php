<?php
header('Content-Type: application/json');

try {
    $db = new PDO('mysql:host=database-5018019376.webspace-host.com;dbname=dbs14323265', 'dbu302398', 'lauftreffhomepage');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Monatliche Kilometer summieren
    $query = $db->query("
        SELECT DATE_FORMAT(start_date_local, '%Y-%m') AS monat,
               ROUND(SUM(distance)/1000, 1) AS kilometer
        FROM strava_activities
        GROUP BY monat
        ORDER BY monat ASC
    ");

    $labels = [];
    $data = [];

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = $row['monat'];
        $data[] = $row['kilometer'];
    }

    echo json_encode(['labels' => $labels, 'data' => $data]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
