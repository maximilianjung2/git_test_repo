<?php

require __DIR__ . '/includes/auth.php';
requireLogin();
require __DIR__ . '/includes/db.php';

$userId = currentUserId();

/*
|--------------------------------------------------------------------------
| Kennzahlen für Karten
|--------------------------------------------------------------------------
*/
$stats7Stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(distance_km), 0) AS km_7,
        COALESCE(SUM(
            CASE
                WHEN duration_min IS NOT NULL AND rpe IS NOT NULL THEN duration_min * rpe
                ELSE 0
            END
        ), 0) AS load_7,
        ROUND(AVG(fitness_feeling), 1) AS fitness_7
    FROM training_entries
    WHERE user_id = :user_id
      AND is_hidden = 0
      AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
");
$stats7Stmt->execute(['user_id' => $userId]);
$stats7 = $stats7Stmt->fetch();

$stats30Stmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(distance_km), 0) AS km_30,
        COALESCE(SUM(
            CASE
                WHEN duration_min IS NOT NULL AND rpe IS NOT NULL THEN duration_min * rpe
                ELSE 0
            END
        ), 0) AS load_30,
        ROUND(AVG(fitness_feeling), 1) AS fitness_30
    FROM training_entries
    WHERE user_id = :user_id
      AND is_hidden = 0
      AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
");
$stats30Stmt->execute(['user_id' => $userId]);
$stats30 = $stats30Stmt->fetch();

/*
|--------------------------------------------------------------------------
| Letzte Einheiten
|--------------------------------------------------------------------------
*/
$recentStmt = $pdo->prepare("
    SELECT activity_date, title, distance_km, duration_min, rpe, fitness_feeling
    FROM training_entries
    WHERE user_id = :user_id
      AND is_hidden = 0
    ORDER BY activity_date DESC, id DESC
    LIMIT 5
");
$recentStmt->execute(['user_id' => $userId]);
$recentEntries = $recentStmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Basisdaten für Charts: Tageswerte der letzten 120 Tage
|--------------------------------------------------------------------------
*/
$dailyStmt = $pdo->prepare("
    SELECT
        activity_date,
        COALESCE(SUM(distance_km), 0) AS day_km,
        COALESCE(SUM(
            CASE
                WHEN duration_min IS NOT NULL AND rpe IS NOT NULL THEN duration_min * rpe
                ELSE 0
            END
        ), 0) AS day_load,
        AVG(fitness_feeling) AS day_fitness
    FROM training_entries
    WHERE user_id = :user_id
      AND is_hidden = 0
      AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 119 DAY)
    GROUP BY activity_date
    ORDER BY activity_date ASC
");
$dailyStmt->execute(['user_id' => $userId]);
$dailyRows = $dailyStmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Lückenlose Zeitreihe erzeugen
|--------------------------------------------------------------------------
*/
$dailyMap = [];
foreach ($dailyRows as $row) {
    $dailyMap[$row['activity_date']] = [
        'load' => (float)$row['day_load'],
        'fitness' => $row['day_fitness'] !== null ? (float)$row['day_fitness'] : null,
    ];
}

$labels = [];
$load7 = [];
$load30 = [];
$fitness7 = [];
$fitness30 = [];

$start = new DateTime();
$start->modify('-119 days');

$loadWindow7 = [];
$loadWindow30 = [];
$fitnessWindow7 = [];
$fitnessWindow30 = [];

for ($i = 0; $i < 120; $i++) {
    $date = clone $start;
    $date->modify("+{$i} days");
    $dateKey = $date->format('Y-m-d');

    $dayLoad = $dailyMap[$dateKey]['load'] ?? 0.0;
    $dayFitness = $dailyMap[$dateKey]['fitness'] ?? null;

    $labels[] = $date->format('d.m.');

    $loadWindow7[] = $dayLoad;
    if (count($loadWindow7) > 7) {
        array_shift($loadWindow7);
    }

    $loadWindow30[] = $dayLoad;
    if (count($loadWindow30) > 30) {
        array_shift($loadWindow30);
    }

    if ($dayFitness !== null) {
        $fitnessWindow7[] = $dayFitness;
        $fitnessWindow30[] = $dayFitness;
    }

    if (count($fitnessWindow7) > 7) {
        array_shift($fitnessWindow7);
    }

    if (count($fitnessWindow30) > 30) {
        array_shift($fitnessWindow30);
    }

    $load7[] = round(array_sum($loadWindow7), 1);
    $load30[] = round(array_sum($loadWindow30), 1);

    $fitness7[] = count($fitnessWindow7) > 0
        ? round(array_sum($fitnessWindow7) / count($fitnessWindow7), 2)
        : null;

    $fitness30[] = count($fitnessWindow30) > 0
        ? round(array_sum($fitnessWindow30) / count($fitnessWindow30), 2)
        : null;
}

$chartData = [
    'labels' => $labels,
    'load7' => $load7,
    'load30' => $load30,
    'fitness7' => $fitness7,
    'fitness30' => $fitness30,
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Trainingsbereich</title>
    <link rel="stylesheet" href="/training/assets/css/training.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container wide">
        <h1>Willkommen im Trainingsbereich</h1>
        <p>Hallo <?= htmlspecialchars($_SESSION['username']) ?>!</p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Kilometer 7 Tage</h3>
                <p><?= number_format((float)$stats7['km_7'], 2, ',', '.') ?> km</p>
            </div>
            <div class="stat-card">
                <h3>Belastung 7 Tage</h3>
                <p><?= (int)$stats7['load_7'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Ø Fitness 7 Tage</h3>
                <p><?= $stats7['fitness_7'] !== null ? htmlspecialchars((string)$stats7['fitness_7']) : '-' ?></p>
            </div>
            <div class="stat-card">
                <h3>Kilometer 30 Tage</h3>
                <p><?= number_format((float)$stats30['km_30'], 2, ',', '.') ?> km</p>
            </div>
            <div class="stat-card">
                <h3>Belastung 30 Tage</h3>
                <p><?= (int)$stats30['load_30'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Ø Fitness 30 Tage</h3>
                <p><?= $stats30['fitness_30'] !== null ? htmlspecialchars((string)$stats30['fitness_30']) : '-' ?></p>
            </div>
        </div>

        <p>
            <a class="button" href="/training/entry_form.php">Neue Einheit eintragen</a>
            <a class="button" href="/training/entries.php">Meine Einheiten</a>
            <a class="button" href="/training/strava_import.php">Strava-Import</a>
            <a class="button" href="/training/logout.php">Logout</a>
        </p>

        <h2>Belastungsverlauf</h2>
        <div class="chart-card">
            <canvas id="loadChart"></canvas>
        </div>

        <h2>Fitnessgefühl-Verlauf</h2>
        <div class="chart-card">
            <canvas id="fitnessChart"></canvas>
        </div>

        <h2>Letzte Einheiten</h2>

        <?php if (!$recentEntries): ?>
            <p>Noch keine Einträge vorhanden.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Titel</th>
                            <th>km</th>
                            <th>Min</th>
                            <th>RPE</th>
                            <th>Fitness</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentEntries as $entry): ?>
                            <tr>
                                <td><?= htmlspecialchars($entry['activity_date']) ?></td>
                                <td><?= htmlspecialchars($entry['title']) ?></td>
                                <td><?= $entry['distance_km'] !== null ? htmlspecialchars(number_format((float)$entry['distance_km'], 2, ',', '.')) : '-' ?></td>
                                <td><?= $entry['duration_min'] !== null ? htmlspecialchars((string)$entry['duration_min']) : '-' ?></td>
                                <td><?= $entry['rpe'] !== null ? htmlspecialchars((string)$entry['rpe']) : '-' ?></td>
                                <td><?= $entry['fitness_feeling'] !== null ? htmlspecialchars((string)$entry['fitness_feeling']) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const chartData = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE) ?>;

        new Chart(document.getElementById('loadChart'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Belastung 7 Tage',
                        data: chartData.load7,
                        tension: 0.25
                    },
                    {
                        label: 'Belastung 30 Tage',
                        data: chartData.load30,
                        tension: 0.25
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        new Chart(document.getElementById('fitnessChart'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Fitnessgefühl 7 Tage',
                        data: chartData.fitness7,
                        tension: 0.25
                    },
                    {
                        label: 'Fitnessgefühl 30 Tage',
                        data: chartData.fitness30,
                        tension: 0.25
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        min: 0,
                        max: 10
                    }
                }
            }
        });
    </script>
</body>
</html>