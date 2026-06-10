<?php 
if (!isset($_SESSION)) {
    session_start();
}
$allowed_languages = ['en', 'pt'];
$default_language = 'en';

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed_languages)) {
    $_SESSION['lang'] = $_GET['lang'];
}

$current_lang = $_SESSION['lang'] ?? $default_language;
$translations = include("/works/Lib/lang/$current_lang.php");

function lang($key) {
    global $translations;
    return htmlspecialchars($translations[$key] ?? $key);
}
?>