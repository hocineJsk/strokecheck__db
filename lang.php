<?php


$supported_langs = ['en', 'fr', 'ar'];
$lang_names = ['en' => 'English', 'fr' => 'Français', 'ar' => 'العربية'];

// Determine language
if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_langs)) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + 365 * 24 * 3600, '/');
} elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $supported_langs)) {
    $lang = $_SESSION['lang'];
} elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $supported_langs)) {
    $lang = $_COOKIE['lang'];
    $_SESSION['lang'] = $lang;
} else {
    $lang = 'en';
}

// Direction for Arabic
$dir = ($lang === 'ar') ? 'rtl' : 'ltr';

// Load translations
$t = require __DIR__ . "/lang/{$lang}.php";


function lang_switcher_html($lang, $lang_names) {
    $current_url = strtok($_SERVER['REQUEST_URI'], '?');
    // Rebuild query string without 'lang'
    $params = $_GET;
    $html  = '<div class="lang-switcher">';
    foreach ($lang_names as $code => $name) {
        $params['lang'] = $code;
        $qs = http_build_query($params);
        $active = ($code === $lang) ? ' active' : '';
        $html .= '<a href="' . htmlspecialchars($current_url . '?' . $qs) . '" class="lang-btn' . $active . '">' . $name . '</a>';
    }
    $html .= '</div>';
    return $html;
}
