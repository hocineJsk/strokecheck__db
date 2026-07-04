<?php
session_start();
include 'lang.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['knowledge_hub'] ?> - <?= $t['predictor_title'] ?></title>
    <link rel="stylesheet" href="style.css">
</head>


<body>
    <div class="user-bar">
        <a href="index.php" class="nav-btn"><?= $t['back'] ?></a>
        <a href="Emergency.php" class="nav-btn"><?= $t['emergency'] ?></a>
        <a href="history.php" class="nav-btn"><?= $t['history'] ?></a>
        <span class="welcome-text"><?= $t['welcome_back'] ?></span>
        <?= lang_switcher_html($lang, $lang_names) ?>
        <button id="theme-toggle" class="theme-toggle" aria-label="Toggle Dark Mode">
            <span class="icon">🌙</span>
        </button>
        <span class="username">👤 <?= htmlspecialchars($username) ?></span>
        <a href="login.php" class="logout-btn">🚪<?= $t['logout'] ?></a>
    </div>

    <div class="page-container">
        <div class="hero-card">
            <div class="hero-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z"/>
                </svg>
            </div>
            <h1 class="hero-title"><?= $t['knowledge_hub'] ?></h1>
            <p class="hero-subtitle"><?= $t['knowledge_desc'] ?></p>
        </div>

        <h2 class="section-title"><?= $t['trusted_resources'] ?></h2>

        <div class="resources-grid">
            <a href="https://www.cdc.gov/stroke/index.html" target="_blank" class="resource-card">
                <div class="resource-icon blue">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#5b4cd3" d="M19,2L14,6.5V17.5L19,13V2M6.5,5C4.55,5 2.45,5.4 1,6.5V21.16C1,21.41 1.25,21.66 1.5,21.66C1.6,21.66 1.65,21.59 1.75,21.59C3.1,20.94 5.05,20.5 6.5,20.5C8.45,20.5 10.55,20.9 12,22C13.35,21.15 15.8,20.5 17.5,20.5C19.15,20.5 20.85,20.81 22.25,21.56C22.35,21.61 22.4,21.59 22.5,21.59C22.75,21.59 23,21.34 23,21.09V6.5C22.4,6.05 21.75,5.75 21,5.5V19C19.9,18.65 18.7,18.5 17.5,18.5C15.8,18.5 13.35,19.15 12,20V8.5C12,8.5 12,8.5 12,8.5C10.55,7.4 8.45,7 6.5,7C5.05,7 3.1,7.44 1.75,8.09V6.5C3.1,5.94 5.05,5.5 6.5,5.5C8.45,5.5 10.55,5.9 12,7C12.05,7 12.1,7 12.15,7.05V5.45C10.55,4.9 8.45,4.5 6.5,4.5V5Z"/>
                    </svg>
                </div>
                <div class="resource-content">
                    <div class="resource-title"><?= $t['cdc_home'] ?></div>
                    <div class="resource-url">https://www.cdc.gov/stroke/index.html</div>
                </div>
                <div class="resource-arrow">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/>
                    </svg>
                </div>
            </a>

            <a href="https://www.cdc.gov/stroke/signs-symptoms/" target="_blank" class="resource-card">
                <div class="resource-icon red">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#ef4444" d="M12,2L1,21H23M12,6L19.53,19H4.47M11,10V14H13V10M11,16V18H13V16"/>
                    </svg>
                </div>
                <div class="resource-content">
                    <div class="resource-title"><?= $t['cdc_symptoms'] ?></div>
                    <div class="resource-url">https://www.cdc.gov/stroke/signs-symptoms/</div>
                </div>
                <div class="resource-arrow">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/>
                    </svg>
                </div>
            </a>

            <a href="https://www.cdc.gov/stroke/prevention/index.html" target="_blank" class="resource-card">
                <div class="resource-icon green">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#22c55e" d="M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1Z"/>
                    </svg>
                </div>
                <div class="resource-content">
                    <div class="resource-title"><?= $t['cdc_prevention'] ?></div>
                    <div class="resource-url">https://www.cdc.gov/stroke/prevention/index.html</div>
                </div>
                <div class="resource-arrow">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/>
                    </svg>
                </div>
            </a>

            <a href="https://www.cdc.gov/stroke/data-research/" target="_blank" class="resource-card">
                <div class="resource-icon purple">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#a855f7" d="M22,21H2V3H4V19H6V10H10V19H12V6H16V19H18V14H22V21Z"/>
                    </svg>
                </div>
                <div class="resource-content">
                    <div class="resource-title"><?= $t['cdc_data'] ?></div>
                    <div class="resource-url">https://www.cdc.gov/stroke/data-research/</div>
                </div>
                <div class="resource-arrow">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>
    <script>
        const toggleBtn = document.getElementById('theme-toggle');
        const body = document.body;
        const icon = toggleBtn.querySelector('.icon');

        //ark & light theme
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