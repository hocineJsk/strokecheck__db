<?php
session_start();
include 'db.php';
include 'lang.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

$sql = "SELECT ID, Risk_probability, Risk_Level, Top_Factors, Prediction_date FROM predictions WHERE User_ID = ? ORDER BY Prediction_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
$labels = [];
$data_points = [];

while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
$chart_history = array_reverse($history);
foreach ($chart_history as $row) {
    if (isset($row['Prediction_date'])) {
        $labels[] = date("M d, H:i", strtotime($row['Prediction_date']));
    }
    if (isset($row['Risk_probability'])) {
        $data_points[] = $row['Risk_probability'];
    }
}

?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['prediction_history'] ?> - <?= $t['predictor_title'] ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .table-container {
            overflow-x: auto;
            margin-top: 30px;
            background: var(--bg-card);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow-card);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-primary);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 13px;
        }

        tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-High { background: rgba(255, 107, 107, 0.2); color: #ff6b6b; }
        .status-Medium { background: rgba(255, 152, 0, 0.2); color: #ff9800; }
        .status-Low { background: rgba(40, 167, 69, 0.2); color: #28a745; }

        .chart-container {
            background: var(--bg-card);
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--shadow-card);
            margin-bottom: 30px;
            height: 400px;
        }

        .factors-cell {
            font-size: 12px;
            color: var(--text-secondary);
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="user-bar">
        <a href="learn.php" class="nav-btn"><?= $t['learn'] ?></a>
        <a href="Emergency.php" class="emergency"><?= $t['emergency'] ?></a>
        <a href="index.php" class="nav-btn"><?= $t['dashboard'] ?></a>
        
        <div style="flex: 1; display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
            <?= lang_switcher_html($lang, $lang_names) ?>
            <span class="welcome-text"><?= $t['welcome'] ?></span>
            <span class="username"><?= htmlspecialchars($username) ?></span>
            <button id="theme-toggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <span class="icon">🌙</span>
            </button>
            <a href="login.php" class="logout-btn" title="<?= $t['logout'] ?>">🚪<?= $t['logout'] ?></a>
        </div>
    </div>

    <div class="page-container">
        <h1 class="page-title"><?= $t['prediction_history'] ?></h1>

        <?php if (count($history) > 0): ?>
            <div class="chart-container">
                <canvas id="riskChart"></canvas>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><?= $t['date'] ?></th>
                            <th><?= $t['result'] ?></th>
                            <th><?= $t['probability'] ?></th>
                            <th><?= $t['top_factors'] ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td><?= date("M d, Y H:i", strtotime($row['Prediction_date'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $row['Risk_Level'] ?>">
                                        <?= htmlspecialchars($row['Risk_Level']) ?>
                                    </span>
                                </td>
                                <td><?= number_format($row['Risk_probability'], 1) ?>%</td>
                                <td class="factors-cell"><?= htmlspecialchars($row['Top_Factors'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="hero-card" style="background: var(--bg-card); color: var(--text-primary);">
                <h2><?= $t['no_history'] ?></h2>
                <p><?= $t['no_history_desc'] ?></p>
                <br>
                <a href="index.php" class="nav-btn" style="background: var(--button-bg); color: var(--button-text); border: none;"><?= $t['go_predictor'] ?></a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Theme Toggle Logic
        const toggleBtn = document.getElementById('theme-toggle');
        const body = document.body;
        const icon = toggleBtn.querySelector('.icon');
        const currentTheme = localStorage.getItem('theme');
        
        if (currentTheme === 'dark') {
            body.setAttribute('data-theme', 'dark');
            icon.textContent = '☀️';
        } else {
            icon.textContent = '🌙';
        }

        toggleBtn.addEventListener('click', () => {
            if (body.hasAttribute('data-theme')) {
                body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                icon.textContent = '🌙';
                updateChartColor('light');
            } else {
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                icon.textContent = '☀️';
                updateChartColor('dark');
            }
        });

        <?php if (count($history) > 0): ?>
        const ctx = document.getElementById('riskChart').getContext('2d');
        
        let chartColor = (currentTheme === 'dark') ? '#ffffff' : '#1a1a1a';
        let gridColor = (currentTheme === 'dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

        const riskChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: <?= json_encode($t['risk_probability_chart']) ?>,
                    data: <?= json_encode($data_points) ?>,
                    borderColor: '#5b4cd3',
                    backgroundColor: 'rgba(91, 76, 211, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#5b4cd3',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: gridColor },
                        ticks: { color: chartColor }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: chartColor }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: chartColor }
                    }
                }
            }
        });

        function updateChartColor(theme) {
            const color = (theme === 'dark') ? '#ffffff' : '#1a1a1a';
            const grid = (theme === 'dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            
            riskChart.options.scales.y.ticks.color = color;
            riskChart.options.scales.x.ticks.color = color;
            riskChart.options.scales.y.grid.color = grid;
            riskChart.options.plugins.legend.labels.color = color;
            riskChart.update();
        }
        <?php endif; ?>
    </script>
</body>
</html>
