<?php
include_once("../../Lib/lib.php");
include_once("../../Lib/lang/translator.php");
if (!isset($_SESSION)) session_start();
$isLoggedIn = isset($_SESSION['id']);
$_langSwitch = ($current_lang === 'en') ? 'pt' : 'en';
$_qp = $_GET; $_qp['lang'] = $_langSwitch;
$_langToggleUrl = '?' . http_build_query($_qp);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fórum — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../wiki/styles/wiki.css?v=4">
<link rel="stylesheet" href="styles/discussions.css">
<link rel="stylesheet" href="styles/composer.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<?php include('../wiki/header.php'); ?>

<div class="container-lg py-4">
  <?php include __DIR__ . '/forum-embed.php'; ?>
</div>

<script>
function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
