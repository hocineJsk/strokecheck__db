<?php
session_start();
include 'db.php';
include 'lang.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Doctor') {
    header("Location: doctor_dashboard.php");
    exit();
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['predictor_title']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="center-content">
    <div class="user-bar">
        <a href="learn.php" class="nav-btn"><?= $t['learn'] ?></a>
        <a href="Emergency.php" class="emergency"><?= $t['emergency'] ?></a>
        <a href="history.php" class="nav-btn"><?= $t['history'] ?></a>
        
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

    <div class="predictor-card">
        <header>
            <h1><?= $t['predictor_title'] ?></h1>
            <p class="subtitle"><?= $t['predictor_subtitle'] ?></p>
        </header>
 
        <?php
        $result_data = null;
        $ai_advice = null;

        if (isset($_GET['prediction'])) {
            $result_data = json_decode(urldecode($_GET['prediction']), true);
        }
        if (isset($_GET['response'])) {
            $ai_advice = urldecode($_GET['response']);
        }
        ?>
        <form action="process.php" method="POST">
            <div class="section-title"><?= $t['health_metrics'] ?></div>
            <div class="form-row">
                <div class="form-group">
                    <label for="age"><?= $t['age'] ?></label>
                    <input type="number" id="age" name="age" required max="120">
                </div>
                <div class="form-group">
                    <label for="glucose"><?= $t['glucose'] ?></label>
                    <input type="number" step="0.01" min="0.5" max="5" id="glucose" name="glucose" placeholder="e.g. 1.85" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="height"><?= $t['height'] ?></label>
                    <input type="number" step="0.1" min="50" max="250" id="height" name="height" placeholder="e.g. 170" required>
                </div>
                <div class="form-group">
                    <label for="weight"><?= $t['weight'] ?></label>
                    <input type="number" step="0.1" min="10" max="300" id="weight" name="weight" placeholder="e.g. 70" required>
                </div>
            </div>

            <div class="divider"></div>
            <div class="section-title"><?= $t['demographics'] ?></div>
            <div class="select-group">
                <div class="form-group">
                    <label for="gender"><?= $t['gender'] ?></label>
                    <select id="gender" name="gender">
                        <option value="0"><?= $t['female'] ?></option>
                        <option value="1"><?= $t['male'] ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="residence"><?= $t['residence'] ?></label>
                    <select id="residence" name="residence">
                        <option value="0"><?= $t['rural'] ?></option>
                        <option value="1"><?= $t['urban'] ?></option>
                    </select>
                </div>
            </div>

            <div class="divider"></div>
            <div class="section-title"><?= $t['medical_history'] ?></div>
            <div class="select-group">
                <div class="form-group">
                    <label for="hypertension"><?= $t['hypertension'] ?></label>
                    <select id="hypertension" name="hypertension">
                        <option value="0"><?= $t['no'] ?></option>
                        <option value="1"><?= $t['yes'] ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="disease"><?= $t['heart_disease'] ?></label>
                    <select id="disease" name="disease">
                        <option value="0"><?= $t['no'] ?></option>
                        <option value="1"><?= $t['yes'] ?></option>
                    </select>
                </div>
            </div>

            <div class="select-group">
                <div class="form-group">
                    <label for="married"><?= $t['ever_married'] ?></label>
                    <select id="married" name="married">
                        <option value="0"><?= $t['no'] ?></option>
                        <option value="1"><?= $t['yes'] ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="work"><?= $t['work_type'] ?></label>
                    <select id="work" name="work">
                        <option value="0"><?= $t['never_worked'] ?></option>
                        <option value="1"><?= $t['private'] ?></option>
                        <option value="2"><?= $t['self_employed'] ?></option>
                        <option value="3"><?= $t['children'] ?></option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="smoking"><?= $t['smoking_status'] ?></label>
                <select id="smoking" name="smoking">
                    <option value="0"><?= $t['unknown'] ?></option>
                    <option value="1"><?= $t['formerly_smoked'] ?></option>
                    <option value="2"><?= $t['never_smoked'] ?></option>
                    <option value="3"><?= $t['smokes'] ?></option>
                </select>
            </div>

            <div class="select-group">
                <div class="form-group">
                    <label for="action"><?= $t['ai_action'] ?></label>
                    <select id="action" name="action">
                        <option value="predict"><?= $t['predict_only'] ?></option>
                        <option value="advice"><?= $t['predict_advice'] ?></option>
                    </select>
                </div>
            </div>

            <button type="submit"><?= $t['process_request'] ?></button>
        </form>

        <?php if ($result_data): ?>
            <?php
                $prob = $result_data['probability'] * 100;
                
                if (isset($result_data['error'])) {
                    $risk_class = 'error';
                    $risk_level = 'error';
                    $label = "⚠️ " . $result_data['error'];
                } elseif ($prob >= 60) {
                    $risk_class = 'high-risk';
                    $risk_level = 'high';
                    $label = $t['high_risk'];
                } elseif ($prob >= 30) {
                    $risk_class = 'medium-risk';
                    $risk_level = 'medium';
                    $label = $t['medium_risk'];
                } else {
                    $risk_class = 'low-risk';
                    $risk_level = 'low';
                    $label = $t['low_risk'];
                }
            ?>
            <div class="result show <?= $risk_class; ?>">
                <div class="risk-label <?= $risk_level; ?>"><?= htmlspecialchars($label) ?></div>
                <?php if (!isset($result_data['error'])): ?>
                    <div class="probability"><?= $t['probability'] ?>: <?= number_format($prob, 2) ?>%</div>
                    
                    <?php if (!empty($result_data['top_features'])): ?>
                        <div class="explanation-section">
                            <div class="explanation-title"><?= $t['key_factors'] ?></div>
                            <?php foreach ($result_data['top_features'] as $feature): ?>
                                <div class="feature-item <?= $feature['impact'] ?>">
                                    <div class="feature-name">
                                        <?= htmlspecialchars($feature['name']) ?>
                                        <span class="feature-value">(<?= $t['value'] ?>: <?= htmlspecialchars($feature['value']) ?>)</span>
                                    </div>
                                    <div class="feature-impact <?= $feature['impact'] ?>">
                                        <?= $feature['impact'] === 'increases' ? $t['increases_risk'] : $t['decreases_risk'] ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($ai_advice): ?>
            <div class="result show ai-advice-card">
                <div class="explanation-title"><?= $t['ai_advice_title'] ?></div>
                <div class="ai-content">
                    <?= nl2br(htmlspecialchars($ai_advice)) ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <script>
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
            } else {
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                icon.textContent = '☀️';
            }
        });
    </script>
</body>

</html>