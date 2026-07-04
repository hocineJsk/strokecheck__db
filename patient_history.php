<?php
session_start();
require 'db.php';
include 'lang.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: index.php");
    exit();
}

$doctor_id  = (int)$_SESSION['user_id'];
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

if ($patient_id <= 0) {
    header("Location: doctor_dashboard.php");
    exit();
}

$check = $conn->prepare(
    "SELECT id, username, email FROM users
     WHERE id = ? AND Doctor_id = ? AND role = 'Normal_User'"
);
$check->bind_param("ii", $patient_id, $doctor_id);
$check->execute();
$patient = $check->get_result()->fetch_assoc();
$check->close();

if (!$patient) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="<?= $lang ?>" dir="<?= $dir ?>"><head><meta charset="UTF-8"><title><?= $t['access_denied'] ?></title>
    <link rel="stylesheet" href="style.css"></head>
    <body class="center-content">
        <div style="text-align:center; padding:60px 24px;">
            <div style="font-size:64px; margin-bottom:20px;">🚫</div>
            <h1 style="font-size:28px; margin-bottom:12px;"><?= $t['access_denied'] ?></h1>
            <p style="color:var(--text-secondary); margin-bottom:28px;">
                <?= $t['access_denied_desc'] ?>
            </p>
            <a href="doctor_dashboard.php" style="
                display:inline-block; padding:12px 28px; background:#000; color:#fff;
                border-radius:10px; text-decoration:none; font-weight:700;">
                <?= $t['back_dashboard'] ?>
            </a>
        </div>
    </body></html>
    <?php
    exit();
}

$sql  = "SELECT ID, Risk_probability, Risk_Level, Top_Factors, Prediction_date
         FROM predictions WHERE User_ID = ? ORDER BY Prediction_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$history      = [];
$labels       = [];
$data_points  = [];

while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();

$chart_history = array_reverse($history);
foreach ($chart_history as $row) {
    if (isset($row['Prediction_date']))  $labels[]      = date("M d, H:i", strtotime($row['Prediction_date']));
    if (isset($row['Risk_probability'])) $data_points[] = $row['Risk_probability'];
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['prediction_history'] ?> – <?= htmlspecialchars($patient['username']) ?> – StrokeCheck</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .page-header {
            max-width: 900px;
            margin: 110px auto 32px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.2s;
        }
        .back-btn:hover {
            background: var(--text-primary);
            color: var(--bg-card);
        }
        .patient-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(91,76,211,0.1);
            border: 1px solid rgba(91,76,211,0.2);
            color: #5b4cd3;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 14px;
            font-weight: 700;
        }
        .page-content {
            max-width: 900px;
            margin: 0 auto 60px;
            padding: 0 24px;
        }
        .chart-box {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,.06);
            margin-bottom: 28px;
            height: 340px;
            border: 1px solid var(--border-color);
        }
        .table-box {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,.06);
            overflow-x: auto;
            border: 1px solid var(--border-color);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-primary);
        }
        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }
        th {
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-secondary);
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--input-bg); }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-High   { background: rgba(255,107,107,.15); color: #c53030; }
        .badge-Medium { background: rgba(255,152,0,.15);   color: #b7791f; }
        .badge-Low    { background: rgba(40,167,69,.15);   color: #276749; }

        .factors-cell {
            font-size: 12px;
            color: var(--text-secondary);
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .doctor-badge {
            display:inline-flex; align-items:center; gap:6px;
            background:rgba(91,76,211,.1); color:#5b4cd3;
            font-size:12px; font-weight:700; padding:5px 14px;
            border-radius:20px; border:1px solid rgba(91,76,211,.25);
        }
        .section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            margin-bottom: 14px;
        }
    </style>
</head>
<body>

<!--navbar-->
<div class="user-bar">
    <a href="Emergency.php" class="emergency"><?= $t['emergency'] ?></a>
    <a href="learn.php" class="nav-btn"><?= $t['learn'] ?></a>
    <div style="flex:1; display:flex; justify-content:flex-end; align-items:center; gap:10px;">
        <?= lang_switcher_html($lang, $lang_names) ?>
        <span class="doctor-badge">🩺 <?= $t['doctor'] ?></span>
        <span class="username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        <button id="theme-toggle" class="theme-toggle" aria-label="Toggle Dark Mode">
            <span class="icon">🌙</span>
        </button>
        <a href="login.php" class="logout-btn" title="<?= $t['logout'] ?>">🚪 <?= $t['logout'] ?></a>
    </div>
</div>

<!--Page Header-->
<div class="page-header">
    <a href="doctor_dashboard.php" class="back-btn"><?= $t['back_dashboard'] ?></a>
    <div class="patient-pill">
        👤 <?= htmlspecialchars($patient['username']) ?>
    </div>
    <span style="font-size:13px; color:var(--text-secondary);">
        <?= htmlspecialchars($patient['email']) ?>
    </span>
</div>

<!--Content-->
<div class="page-content">
    <?php if (count($history) > 0): ?>
        <!-- Chart -->
        <div class="section-label"><?= $t['risk_trend'] ?></div>
        <div class="chart-box">
            <canvas id="riskChart"></canvas>
        </div>

        <!--Table -->
        <div class="section-label"><?= $t['prediction_records'] ?> (<?= count($history) ?>)</div>
        <div class="table-box">
            <table>
                <thead>
                    <tr>
                        <th><?= $t['date'] ?></th>
                        <th><?= $t['risk_level'] ?></th>
                        <th><?= $t['probability'] ?></th>
                        <th><?= $t['top_factors'] ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $row): ?>
                    <tr>
                        <td><?= date("M d, Y · H:i", strtotime($row['Prediction_date'])) ?></td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($row['Risk_Level'] ?? 'Low') ?>">
                                <?= $row['Risk_Level'] === 'High' ? '🔴' : ($row['Risk_Level'] === 'Medium' ? '🟡' : '🟢') ?>
                                <?= htmlspecialchars($row['Risk_Level'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td><?= number_format((float)$row['Risk_probability'], 1) ?>%</td>
                        <td class="factors-cell" title="<?= htmlspecialchars($row['Top_Factors'] ?? '') ?>">
                            <?= htmlspecialchars($row['Top_Factors'] ?? '—') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- No predictions yet -->
        <div style="text-align:center; padding:60px; background:var(--bg-card); border-radius:16px;
                    border:1px dashed var(--border-color);">
            <div style="font-size:48px; margin-bottom:16px;">📭</div>
            <h2 style="font-size:20px; margin-bottom:10px;"><?= $t['no_predictions'] ?></h2>
            <p style="color:var(--text-secondary);">
                <?= $t['no_predictions_desc'] ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<script>
    const toggleBtn = document.getElementById('theme-toggle');
    const body = document.body;
    const icon = toggleBtn.querySelector('.icon');
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'dark') { body.setAttribute('data-theme', 'dark'); icon.textContent = '☀️'; }
    else { icon.textContent = '🌙'; }
    toggleBtn.addEventListener('click', () => {
        if (body.hasAttribute('data-theme')) {
            body.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
            icon.textContent = '🌙';
            if (typeof riskChart !== 'undefined') updateChart('light');
        } else {
            body.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            icon.textContent = '☀️';
            if (typeof riskChart !== 'undefined') updateChart('dark');
        }
    });

<?php if (count($history) > 0): ?>
    const ctx = document.getElementById('riskChart').getContext('2d');
    let chartColor = (currentTheme === 'dark') ? '#ffffff' : '#1a1a1a';
    let gridColor  = (currentTheme === 'dark') ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.07)';

    const riskChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: <?= json_encode($t['risk_probability_chart']) ?>,
                data: <?= json_encode($data_points) ?>,
                borderColor: '#5b4cd3',
                backgroundColor: 'rgba(91,76,211,0.08)',
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
                    grid:  { color: gridColor },
                    ticks: { color: chartColor }
                },
                x: {
                    grid:  { display: false },
                    ticks: { color: chartColor }
                }
            },
            plugins: {
                legend: { labels: { color: chartColor } }
            }
        }
    });

    function updateChart(theme) {
        const c = theme === 'dark' ? '#ffffff' : '#1a1a1a';
        const g = theme === 'dark' ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.07)';
        riskChart.options.scales.y.ticks.color = c;
        riskChart.options.scales.x.ticks.color = c;
        riskChart.options.scales.y.grid.color  = g;
        riskChart.options.plugins.legend.labels.color = c;
        riskChart.update();
    }
<?php endif; ?>
</script>
</body>
</html>
