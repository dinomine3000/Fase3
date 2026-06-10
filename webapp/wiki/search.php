<?php
include_once("../../Lib/lib.php");
require_once("../../Lib/wikiLib.php");

if (!isset($_SESSION)) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
$name = $isLoggedIn ? $_SESSION['username'] : null;

$q  = trim($_GET['q'] ?? '');

$catResults = $subcatResults = $artResults = $userResults = [];

if ($q !== '') {
    dbConnect(ConfigFile);
    $db   = $GLOBALS['configDataBase']->db;
    $conn = $GLOBALS['ligacao'];
    mysqli_select_db($conn, $db);
    $esc = mysqli_real_escape_string($conn, $q);

    $r = mysqli_query($conn, "SELECT `primaryCategory` FROM `$db`.`category-primary` WHERE `primaryCategory` LIKE '%$esc%' ORDER BY `primaryCategory`");
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) $catResults[] = ['name' => $row['primaryCategory']];
        mysqli_free_result($r);
    }

    $r = mysqli_query($conn, "SELECT `secondaryCategory`, `primaryCategory` FROM `$db`.`category-secondary` WHERE `secondaryCategory` LIKE '%$esc%' OR `primaryCategory` LIKE '%$esc%' ORDER BY `primaryCategory`, `secondaryCategory`");
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) $subcatResults[] = ['name' => $row['secondaryCategory'], 'cat_name' => $row['primaryCategory'], 'description' => ''];
        mysqli_free_result($r);
    }

    $r = mysqli_query($conn, "SELECT `pageTitle`, `primaryCategory`, `secondaryCategory` FROM `$db`.`page` WHERE `pageTitle` LIKE '%$esc%' ORDER BY `pageTitle`");
    if ($r) {
        while ($row = mysqli_fetch_assoc($r)) $artResults[] = [
            'title'     => $row['pageTitle'],
            'pageTitle' => $row['pageTitle'],
            'tag'       => $row['primaryCategory'],
            'path'      => $row['primaryCategory'] . ' › ' . $row['secondaryCategory'],
            'excerpt'   => '',
            'date'      => '',
            'read'      => '',
        ];
        mysqli_free_result($r);
    }

    dbDisconnect();

    $userResults = getResultsMatching($q, 'name', 'auth-basic', 'name', 10) ?? [];
}

$total = count($catResults) + count($subcatResults) + count($artResults) + count($userResults);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $q ? htmlspecialchars($q).' — ' : ''; ?>Search — Portal Wiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css?v=4">
<link rel="stylesheet" href="styles/search.css?v=3">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<?php include('/works/webapp/wiki/header.php')?>

<div class="container-lg py-4">

  <?php if ($q === ''): ?>
    <div class="search-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <div class="search-empty-title"><?php echo lang('search_the_wiki'); ?></div>
      <div class="search-empty-sub"><?php echo lang('search_hint'); ?></div>
    </div>

  <?php elseif ($total === 0): ?>
    <div class="search-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <div class="search-empty-title"><?php echo lang('no_results_for'); ?> &ldquo;<?php echo htmlspecialchars($q); ?>&rdquo;</div>
      <div class="search-empty-sub"><?php echo lang('try_different'); ?></div>
    </div>

  <?php else: ?>
    <div class="search-summary">
      <?php echo $total; ?> <?php echo $total !== 1 ? lang('result_plural') : lang('result_singular'); ?> &ldquo;<?php echo htmlspecialchars($q); ?>&rdquo;
    </div>

    <?php if ($catResults): ?>
    <div class="section-heading">
      <?php echo lang('categories'); ?> <span class="res-count"><?php echo count($catResults); ?></span>
    </div>
    <div class="d-flex flex-column gap-1 mb-4">
      <?php foreach ($catResults as $c): ?>
      <a class="result-bar" href="viewPage.php?primaryCategory=<?php echo urlencode($c['name']); ?>">
        <span class="res-chip"><?php echo lang('category_chip'); ?></span>
        <div class="art-body">
          <div class="art-title"><?php echo htmlspecialchars($c['name']); ?></div>
        </div>
        <div class="art-meta">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($subcatResults): ?>
    <div class="section-heading">
      <?php echo lang('subcategories'); ?> <span class="res-count"><?php echo count($subcatResults); ?></span>
    </div>
    <div class="d-flex flex-column gap-1 mb-4">
      <?php foreach ($subcatResults as $s): ?>
      <a class="result-bar" href="viewPage.php?primaryCategory=<?php echo urlencode($s['cat_name']); ?>&secondaryCategory=<?php echo urlencode($s['name']); ?>">
        <span class="res-chip"><?php echo lang('subcategory_chip'); ?></span>
        <div class="art-body">
          <div class="art-title"><?php echo htmlspecialchars($s['name']); ?></div>
        </div>
        <div class="art-meta">
          <span><?php echo htmlspecialchars($s['cat_name']); ?></span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($artResults): ?>
    <div class="section-heading">
      <?php echo lang('articles'); ?> <span class="res-count"><?php echo count($artResults); ?></span>
    </div>
    <div class="d-flex flex-column gap-1 mb-4">
      <?php foreach ($artResults as $a): ?>
      <a class="result-bar" href="viewPage.php?pageTitle=<?php echo urlencode($a['pageTitle']); ?>">
        <span class="art-tag"><?php echo htmlspecialchars($a['tag']); ?></span>
        <div class="art-body">
          <div class="art-title"><?php echo htmlspecialchars($a['title']); ?></div>
        </div>
        <div class="art-meta">
          <span><?php echo htmlspecialchars($a['path']); ?></span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($userResults): ?>
    <div class="section-heading">
      <?php echo lang('users'); ?> <span class="res-count"><?php echo count($userResults); ?></span>
    </div>
    <div class="d-flex flex-column gap-1 mb-4">
      <?php foreach ($userResults as $u): ?>
      <a class="result-bar user-result" href="profile.php?user=<?php echo urlencode($u['name']); ?>">
        <span class="res-avatar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </span>
        <div class="art-body">
          <div class="art-title"><?php echo htmlspecialchars($u['name']); ?></div>
        </div>
        <div class="art-meta">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  <?php endif; ?>

</div>

<script>
function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}
</script>
<script src="../scripts/ajaxHandler.js?v=3"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
