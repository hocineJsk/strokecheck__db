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
    <title><?= $t['emergency'] ?> - <?= $t['predictor_title'] ?></title>
    <link rel="stylesheet" href="style.css">
</head>


<body>
    <div class="user-bar">
        <a href="index.php" class="nav-btn"><?= $t['back'] ?></a>
        <a href="learn.php" class="nav-btn"><?= $t['learn'] ?></a>
        <a href="history.php" class="nav-btn"><?= $t['history'] ?></a>
        <span class="welcome-text"><?= $t['welcome_back'] ?></span>
        <?= lang_switcher_html($lang, $lang_names) ?>
        <button id="theme-toggle" class="theme-toggle" aria-label="Toggle Dark Mode">
            <span class="icon">🌙</span>
        </button>
        <span class="username">👤 <?= htmlspecialchars($username) ?></span>
        <a href="login.php" class="logout-btn"><?= $t['logout'] ?></a>
    </div>

    <div class="page-container">
        <div class="hero-card emergency-hero">
            <div class="hero-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning" viewBox="0 0 16 16">
  <path d="M5.52.359A.5.5 0 0 1 6 0h4a.5.5 0 0 1 .474.658L8.694 6H12.5a.5.5 0 0 1 .395.807l-7 9a.5.5 0 0 1-.873-.454L6.823 9.5H3.5a.5.5 0 0 1-.48-.641zM6.374 1 4.168 8.5H7.5a.5.5 0 0 1 .478.647L6.78 13.04 11.478 7H8a.5.5 0 0 1-.474-.658L9.306 1z"/>
</svg>
            </div>
            <h1 class="hero-title"><?= $t['think_fast'] ?></h1>
            <p class="hero-subtitle"><?= $t['fast_desc'] ?></p>
        </div>

        <h2 class="section-title"><?= $t['signs_watch'] ?></h2>

        <div class="resources-grid">
            <a class="resource-card">
                <div class="resource-icon blue">
                    <svg width="60" height="100" viewBox="0 0 60 100" xmlns="http://www.w3.org">
    <text x="0" y="75" font-family="Arial, sans-serif" font-size="80" fill="#c70505">F</text>
</svg>

                </div>
                <div class="resource-content">
                    <div class="resource-title"><?= $t['face_drooping'] ?></div>
                    <div class="resource-url"><?= $t['face_desc'] ?></div>
                </div>
            </a>

            <a class="resource-card">
                <div class="resource-icon red">
                    <svg width="60" height="100" viewBox="0 0 60 100" xmlns="http://www.w3.org">
    <text x="0" y="75" font-family="Arial, sans-serif" font-size="80" fill="#c70505">A</text>
</svg>
                </div>
                <div class="resource-content">
                    <div class="resource-title"><?= $t['arm_weakness'] ?></div>
                    <div class="resource-url"><?= $t['arm_desc'] ?></div>
                </div>
               
            </a>

            <a class="resource-card">
                <div class="resource-icon green">
                    <svg width="60" height="100" viewBox="0 0 60 100" xmlns="http://www.w3.org">
    <text x="0" y="75" font-family="Arial, sans-serif" font-size="80" fill="#c70505">S</text>
</svg>
                </div>
                <div class="resource-content">
                    <div class="resource-title"><?= $t['speech_difficulty'] ?></div>
                    <div class="resource-url"><?= $t['speech_desc'] ?></div>
                </div>
             
            </a>

            <a class="resource-card">
                <div class="resource-icon purple">
                    <svg width="60" height="100" viewBox="0 0 60 100" xmlns="http://www.w3.org">
    <text x="0" y="75" font-family="Arial, sans-serif" font-size="80" fill="#c70505">T</text>
</svg>
                </div>
                <div class="resource-content">
                    <div class="resource-title"><?= $t['time_call'] ?></div>
                    <div class="resource-url"><?= $t['time_desc'] ?></div>
                </div>
               
            </a>
        </div>
    </div>
    <script>
        const toggleBtn = document.getElementById('theme-toggle');
        const body = document.body;
        const icon = toggleBtn.querySelector('.icon');

        // Check local storage
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