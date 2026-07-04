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

$doctor_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['username'];

$stmt = $conn->prepare(
    "SELECT id, username, email, created_at FROM users
     WHERE Doctor_id = ? AND role = 'Normal_User'
     ORDER BY username ASC"
);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$patients_result = $stmt->get_result();
$patients = [];
while ($row = $patients_result->fetch_assoc()) {
    $patients[] = $row;
}
$stmt->close();

$summaries = [];
foreach ($patients as $p) {
    $pid = $p['id'];
    $s = $conn->prepare(
        "SELECT COUNT(*) as total,
                MAX(Prediction_date) as last_date,
                (SELECT Risk_Level FROM predictions WHERE User_ID = ? ORDER BY Prediction_date DESC LIMIT 1) as last_level
         FROM predictions WHERE User_ID = ?"
    );
    $s->bind_param("ii", $pid, $pid);
    $s->execute();
    $summaries[$pid] = $s->get_result()->fetch_assoc();
    $s->close();
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['my_patients'] ?> – StrokeCheck</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-header {
            margin: 110px auto 40px;
            max-width: 1000px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .dashboard-header h1 {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--text-primary);
        }
        .badge-count {
            background: #5b4cd3;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        /* ── Patient Grid ── */
        .patients-grid {
            max-width: 1000px;
            margin: 0 auto 60px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
            gap: 20px;
        }
        .patient-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 28px 24px 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
            transition: transform 0.22s cubic-bezier(.4,0,.2,1), box-shadow 0.22s;
        }
        .patient-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
        }
        .patient-avatar {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #5b4cd3, #7c6ce0);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            font-weight: 700;
        }
        .patient-name {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-primary);
        }
        .patient-email {
            font-size: 12px;
            color: var(--text-secondary);
            word-break: break-all;
        }
        .patient-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 4px;
        }
        .meta-chip {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            background: var(--input-bg);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .risk-chip-High   { background: rgba(255,107,107,.15); color: #e53e3e; border-color: #feb2b2; }
        .risk-chip-Medium { background: rgba(255,152,0,.15);   color: #c07010; border-color: #fbd38d; }
        .risk-chip-Low    { background: rgba(40,167,69,.15);   color: #276749; border-color: #9ae6b4; }
        .risk-chip-none   { background: var(--input-bg);       color: var(--text-secondary); }

        .view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 8px;
            padding: 10px 0;
            background: var(--button-bg);
            color: var(--button-text);
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.2s;
            letter-spacing: 0.5px;
        }
        .view-btn:hover {
            opacity: 0.85;
            transform: translateY(-1px);
        }

        /* ── Empty State ── */
        .empty-state {
            max-width: 480px;
            margin: 0 auto 60px;
            text-align: center;
            padding: 60px 24px;
            background: var(--bg-card);
            border: 1px dashed var(--border-color);
            border-radius: 20px;
        }
        .empty-emoji { font-size: 56px; margin-bottom: 20px; }
        .empty-state h2 { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 10px; }
        .empty-state p  { font-size: 14px; color: var(--text-secondary); line-height: 1.7; }

        .doctor-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(91,76,211,0.1);
            color: #5b4cd3;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
            border: 1px solid rgba(91,76,211,0.25);
        }
    </style>
</head>
<body>

<div class="user-bar">
    <a href="Emergency.php" class="emergency"><?= $t['emergency'] ?></a>
    <a href="learn.php" class="nav-btn"><?= $t['learn'] ?></a>

    <div style="flex:1; display:flex; justify-content:flex-end; align-items:center; gap:10px;">
        <?= lang_switcher_html($lang, $lang_names) ?>
        <span class="doctor-badge">🩺 <?= $t['doctor'] ?></span>
        <span class="welcome-text"><?= $t['dr'] ?></span>
        <span class="username"><?= htmlspecialchars($doctor_name) ?></span>
        <button id="theme-toggle" class="theme-toggle" aria-label="Toggle Dark Mode">
            <span class="icon">🌙</span>
        </button>
        <a href="login.php" class="logout-btn" title="<?= $t['logout'] ?>">🚪 <?= $t['logout'] ?></a>
    </div>
</div>
<div class="dashboard-header">
    <div>
        <h1><?= $t['my_patients'] ?></h1>
        <p style="margin-top:6px; font-size:14px; color:var(--text-secondary);">
            <?= $t['patients_assigned'] ?>
        </p>
    </div>
    <?php if (count($patients) > 0): ?>
        <span class="badge-count"><?= count($patients) ?> <?= count($patients) > 1 ? $t['patients'] : $t['patient'] ?></span>
    <?php endif; ?>
</div>

<?php if (count($patients) > 0): ?>
<div class="patients-grid">
    <?php foreach ($patients as $p):
        $psum  = $summaries[$p['id']] ?? [];
        $total = $psum['total'] ?? 0;
        $level = $psum['last_level'] ?? null;
        $last  = $psum['last_date']  ?? null;
        $initial = strtoupper(substr($p['username'], 0, 1));
        $riskClass = $level ? "risk-chip-{$level}" : 'risk-chip-none';
    ?>
    <div class="patient-card">
        <div class="patient-avatar"><?= htmlspecialchars($initial) ?></div>
        <div>
            <div class="patient-name"><?= htmlspecialchars($p['username']) ?></div>
            <div class="patient-email"><?= htmlspecialchars($p['email']) ?></div>
        </div>
        <div class="patient-meta">
            <span class="meta-chip"> <?= (int)$total ?> <?= $total != 1 ? $t['predictions'] : $t['prediction'] ?></span>
            <?php if ($level): ?>
                <span class="meta-chip <?= htmlspecialchars($riskClass) ?>">
                    <?= $level === 'High' ? '🔴' : ($level === 'Medium' ? '🟡' : '🟢') ?>
                    <?= htmlspecialchars($level) ?> <?= $t['risk'] ?>
                </span>
            <?php endif; ?>
            <?php if ($last): ?>
                <span class="meta-chip">🕐 <?= date("M d, Y", strtotime($last)) ?></span>
            <?php endif; ?>
        </div>
        <a href="patient_history.php?patient_id=<?= (int)$p['id'] ?>" class="view-btn">
            <?= $t['view_history'] ?>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<?php else: ?>
<div class="empty-state" style="max-width:480px; margin: 0 auto 60px; padding: 0 24px;">
    <div style="text-align:center; padding:60px 24px; background:var(--bg-card); border:1px dashed var(--border-color); border-radius:20px;">
        <div class="empty-emoji">👥</div>
        <h2><?= $t['no_patients'] ?></h2>
        <p><?= $t['no_patients_desc'] ?><br>
           <?= $t['contact_admin'] ?> (Doctor_id = <?= (int)$doctor_id ?>).</p>
    </div>
</div>
<?php endif; ?>

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
        } else {
            body.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            icon.textContent = '☀️';
        }
    });
</script>
</body>
</html>
