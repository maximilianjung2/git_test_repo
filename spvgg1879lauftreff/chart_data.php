<?php
header('Content-Type: application/json');

try {
    $db = new PDO('mysql:host=database-5018019376.webspace-host.com;dbname=dbs14323265', 'dbu302398', 'lauftreffhomepage');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Nur Aktivitäten vom Typ 'Run' mit Name 'Spvgg. Hainstadt' (optional)
    $stmt = $db->query("
        SELECT id, name, ROUND(distance/1000, 2) AS kilometer,
               DATE_FORMAT(start_date_local, '%d.%m.%Y') AS datum
        FROM strava_activities
        WHERE type = 'Run'
        ORDER BY start_date_local DESC
        LIMIT 100
    ");

    $labels = [];
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Name + Datum als Label
        $labels[] = $row['datum'];
        $data[] = $row['kilometer'];
    }

    echo json_encode(['labels' => $labels, 'data' => $data]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
