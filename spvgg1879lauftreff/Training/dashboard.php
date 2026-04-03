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
| Basisdaten für kombinierte Grafik: letzte 180 Tage
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
      AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 179 DAY)
    GROUP BY activity_date
    ORDER BY activity_date ASC
");
$dailyStmt->execute(['user_id' => $userId]);
$dailyRows = $dailyStmt->fetchAll();

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

$start = new DateTime();
$start->modify('-179 days');

$loadWindow7 = [];
$loadWindow30 = [];
$fitnessWindow7 = [];

for ($i = 0; $i < 180; $i++) {
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

    $fitnessWindow7[] = $dayFitness;
    if (count($fitnessWindow7) > 7) {
        array_shift($fitnessWindow7);
    }

    $fitnessValues7 = array_values(array_filter($fitnessWindow7, static fn($v) => $v !== null));

    $load7[] = round(array_sum($loadWindow7), 1);
    $load30[] = round(array_sum($loadWindow30), 1);
    $fitness7[] = count($fitnessValues7) > 0
        ? round(array_sum($fitnessValues7) / count($fitnessValues7), 2)
        : null;
}

$chartData = [
    'labels' => $labels,
    'load7' => $load7,
    'load30' => $load30,
    'fitness7' => $fitness7,
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
        <p>Hallo <?= htmlspecialchars(currentUsername() ?? '') ?>!</p>

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
            <?php if (isAdmin()): ?>
                <a class="button" href="/training/admin_users.php">Admin Nutzer</a>
            <?php endif; ?>
            <a class="button" href="/training/logout.php">Logout</a>
        </p>

        <h2>Formkurve</h2>
        <div class="chart-toolbar">
            <button type="button" class="chart-range-btn active" data-range="7">7 Tage</button>
            <button type="button" class="chart-range-btn" data-range="30">30 Tage</button>
            <button type="button" class="chart-range-btn" data-range="90">3 Monate</button>
            <button type="button" class="chart-range-btn" data-range="180">6 Monate</button>
        </div>

        <div class="chart-card">
            <canvas id="formChart"></canvas>
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

        function sliceLast(arr, range) {
            return arr.slice(-range);
        }

        const ctx = document.getElementById('formChart');
        let formChart;

        function buildChart(range) {
            const labels = sliceLast(chartData.labels, range);
            const load7 = sliceLast(chartData.load7, range);
            const load30 = sliceLast(chartData.load30, range);
            const fitness7 = sliceLast(chartData.fitness7, range);

            if (formChart) {
                formChart.destroy();
            }

            formChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Belastung 7 Tage',
                            data: load7,
                            yAxisID: 'yLoad',
                            tension: 0.25
                        },
                        {
                            label: 'Belastung 30 Tage',
                            data: load30,
                            yAxisID: 'yLoad',
                            tension: 0.25
                        },
                        {
                            label: 'Fitness 7 Tage',
                            data: fitness7,
                            yAxisID: 'yFitness',
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
                    maintainAspectRatio: false,
                    scales: {
                        yLoad: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Belastung'
                            }
                        },
                        yFitness: {
                            type: 'linear',
                            position: 'right',
                            min: 0,
                            max: 10,
                            grid: {
                                drawOnChartArea: false
                            },
                            title: {
                                display: true,
                                text: 'Fitness'
                            }
                        }
                    }
                }
            });
        }

        document.querySelectorAll('.chart-range-btn').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.chart-range-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                buildChart(parseInt(button.dataset.range, 10));
            });
        });

        buildChart(7);
    </script>
</body>
</html>