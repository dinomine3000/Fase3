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
<link rel="stylesheet" href="styles/wiki.css?v=3">
<link rel="stylesheet" href="styles/search.css?v=3">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="../home.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div class="search-outer">
        <div class="search-wrap">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="headerSearch" placeholder="Search wiki…" value="<?php echo htmlspecialchars($q); ?>"
                 oninput="searchAllHeader(this.value,'hdr-suggest','')"
                 onblur="setTimeout(()=>{let s=document.getElementById('hdr-suggest');if(s){s.innerHTML='';s.classList.remove('has-results');}},150)"
                 onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='search.php?q='+encodeURIComponent(this.value.trim())">
        </div>
        <div id="hdr-suggest" class="search-suggest"></div>
      </div>
      <button class="theme-toggle" onclick="toggleTheme()" title="Toggle light/dark">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <a href="../foruns/forum.php" class="hbtn primary" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Forum
      </a>
      <?php if ($isLoggedIn): ?>
      <a href="profile.php?user=<?php echo urlencode($name); ?>" class="hbtn icon" style="text-decoration:none" title="My Profile">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>
      <form action="../auth/logout.php" method="POST" style="margin:0">
        <button type="submit" class="hbtn icon" title="Logout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </form>
      <?php else: ?>
      <a href="../auth/formLogin.php" class="hbtn" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Login
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<div class="container-lg py-4">

  <?php if ($q === ''): ?>
    <div class="search-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <div class="search-empty-title">Search the wiki</div>
      <div class="search-empty-sub">Type to find categories, subcategories, articles, and users.</div>
    </div>

  <?php elseif ($total === 0): ?>
    <div class="search-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <div class="search-empty-title">No results for &ldquo;<?php echo htmlspecialchars($q); ?>&rdquo;</div>
      <div class="search-empty-sub">Try different keywords or check your spelling.</div>
    </div>

  <?php else: ?>
    <div class="search-summary">
      <?php echo $total; ?> result<?php echo $total !== 1 ? 's' : ''; ?> for &ldquo;<?php echo htmlspecialchars($q); ?>&rdquo;
    </div>

    <?php if ($catResults): ?>
    <div class="section-heading">
      Categories <span class="res-count"><?php echo count($catResults); ?></span>
    </div>
    <div class="d-flex flex-column gap-1 mb-4">
      <?php foreach ($catResults as $c): ?>
      <a class="result-bar" href="viewPage.php?primaryCategory=<?php echo urlencode($c['name']); ?>">
        <span class="res-chip">Category</span>
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
      Subcategories <span class="res-count"><?php echo count($subcatResults); ?></span>
    </div>
    <div class="d-flex flex-column gap-1 mb-4">
      <?php foreach ($subcatResults as $s): ?>
      <a class="result-bar" href="viewPage.php?primaryCategory=<?php echo urlencode($s['cat_name']); ?>&secondaryCategory=<?php echo urlencode($s['name']); ?>">
        <span class="res-chip">Subcategory</span>
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
      Articles <span class="res-count"><?php echo count($artResults); ?></span>
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
      Users <span class="res-count"><?php echo count($userResults); ?></span>
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
