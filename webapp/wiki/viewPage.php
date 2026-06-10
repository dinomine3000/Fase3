<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once("../../Lib/lib.php");
include_once("../../Lib/db.php");
require_once( "../../Lib/wikiLib.php" );
include_once("../../Lib/extendedParsedown.php");
$method = $_SERVER['REQUEST_METHOD'];

if (!isset($_SESSION)) {
    session_start();
}

if ($method == 'POST') {
    $_INPUT_METHOD = INPUT_POST;
    $_ARGS = $_POST;
} elseif ($method == 'GET') {
    $_INPUT_METHOD = INPUT_GET;
    $_ARGS = $_GET;
} else {
    echo "Invalid HTTP method (" . $method . ")";
    exit();
}

$title     = filter_input($_INPUT_METHOD, 'pageTitle', FILTER_UNSAFE_RAW);
$secondary = filter_input($_INPUT_METHOD, 'secondaryCategory', FILTER_UNSAFE_RAW);
$primary   = filter_input($_INPUT_METHOD, 'primaryCategory', FILTER_UNSAFE_RAW);
$name      = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$userId    = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;

// Handle subscription toggle POST actions
if ($method == 'POST' && $userId !== null) {
    if (isset($_POST['toggle_page_sub']) && !empty($title)) {
        togglePageSubscription($userId, $title);
        header("Location: ?pageTitle=" . urlencode($title));
        exit();
    }
    if (isset($_POST['toggle_cat_sub']) && !empty($primary) && !empty($secondary)) {
        toggleCategorySubscription($userId, $primary, $secondary);
        header("Location: ?primaryCategory=" . urlencode($primary) . "&secondaryCategory=" . urlencode($secondary));
        exit();
    }
}

// Determine page state
if (!empty($title)) {
    $content  = readWikiPage($title);
    $meta     = getPageMetaData($title);
    $Parsedown = new ExtendedParsedown();
    if (!authorizeUserByNumericLevel($name, $meta['visibility'])) {
        header("Location: ?");
        exit();
    }
    $state = 'article';
} elseif (!empty($primary) && !empty($secondary)) {
    $pages = getPagesList($primary, $secondary);
    $state = 'pages';
} elseif (!empty($primary)) {
    $secondaryCategories = getCategoryList('secondary', $primary);
    $state = 'secondary';
} else {
    $primaryCategories = getCategoryList('primary');
    $state = 'primary';
}

// Shade cycle for category cards
$shades = ['s1','s2','s3','s4','s5'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Wiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css?v=3">
<link rel="stylesheet" href="styles/viewpage.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="viewPage.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div class="search-outer">
        <div class="search-wrap">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" placeholder="Search wiki…"
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
      <?php if ($name): ?>
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

<?php if ($state === 'primary'): ?>
  <!-- ── STATE 1: Primary categories ── -->
  <div class="section-heading">Categories</div>
  <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-2">
    <?php foreach ($primaryCategories as $i => $cat):
      $shade = $shades[$i % 5];
      $catName = htmlspecialchars($cat['primaryCategory']);
    ?>
    <div class="col">
      <a href="?primaryCategory=<?php echo urlencode($cat['primaryCategory']); ?>" class="cat-card-link">
        <div class="cat-card">
          <div class="cat-img <?php echo $shade; ?>">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--cat-icon)" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
          </div>
          <div class="cat-body">
            <div class="cat-label"><?php echo $catName; ?></div>
          </div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

<?php elseif ($state === 'secondary'): ?>
  <!-- ── STATE 2: Secondary categories ── -->
  <div class="page-nav">
    <a href="?" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      All Categories
    </a>
  </div>
  <div class="section-heading"><?php echo htmlspecialchars($primary); ?></div>
  <?php if (empty($secondaryCategories)): ?>
    <div class="no-results">No subcategories yet.</div>
  <?php else: ?>
  <div class="subcat-grid">
    <?php foreach ($secondaryCategories as $i => $secCat):
      $shade = $shades[$i % 5];
      $secName = htmlspecialchars($secCat['secondaryCategory']);
    ?>
    <a href="?primaryCategory=<?php echo urlencode($primary); ?>&secondaryCategory=<?php echo urlencode($secCat['secondaryCategory']); ?>" class="subcat-banner">
      <div class="subcat-banner-img">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--cat-icon)" stroke-width="1.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div class="subcat-banner-body">
        <div class="subcat-banner-name"><?php echo $secName; ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

<?php elseif ($state === 'pages'): ?>
  <!-- ── STATE 3: Pages list ── -->
  <div class="page-nav">
    <a href="?primaryCategory=<?php echo urlencode($primary); ?>" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      <?php echo htmlspecialchars($primary); ?>
    </a>
    <?php if ($userId !== null): ?>
    <form method="POST" style="margin:0">
      <input type="hidden" name="primaryCategory" value="<?php echo htmlspecialchars($primary); ?>">
      <input type="hidden" name="secondaryCategory" value="<?php echo htmlspecialchars($secondary); ?>">
      <button type="submit" name="toggle_cat_sub" class="hbtn" style="height:28px;font-size:11px;padding:0 10px">
        <?php echo isSubscribedToCategory($userId, $primary, $secondary) ? 'Unsubscribe' : 'Subscribe'; ?>
      </button>
    </form>
    <?php endif; ?>
  </div>

  <div class="section-heading"><?php echo htmlspecialchars($secondary); ?></div>

  <?php if (empty($pages)): ?>
    <div class="no-results">No pages in this category yet.</div>
  <?php else: ?>
  <div class="d-flex flex-column">
    <?php foreach ($pages as $page):
      $requiredLevel = (int)$page['visibility'];
      if (!authorizeUserByNumericLevel($name, $requiredLevel)) continue;
      $pageTitle = htmlspecialchars($page['pageTitle']);
    ?>
    <a href="?pageTitle=<?php echo urlencode($page['pageTitle']); ?>" class="page-bar">
      <div class="page-bar-title"><?php echo $pageTitle; ?></div>
      <div class="page-bar-sub">
        <span class="art-sep" style="font-family:'Outfit',sans-serif;font-size:10px;color:var(--faint)">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

<?php elseif ($state === 'article'): ?>
  <!-- ── STATE 4: Article view ── -->
  <div class="page-nav">
    <?php if ($meta): ?>
    <a href="?primaryCategory=<?php echo urlencode($meta['primaryCategory']); ?>&secondaryCategory=<?php echo urlencode($meta['secondaryCategory']); ?>" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      <?php echo htmlspecialchars($meta['secondaryCategory']); ?>
    </a>
    <?php else: ?>
    <a href="?" class="back-link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Categories
    </a>
    <?php endif; ?>

    <div style="flex:1"></div>

    <?php if (isset($name) && authorizeUserByLevel($name, 'user')): ?>
    <a href="editPage.php?pageTitle=<?php echo urlencode($title); ?>" class="hbtn" style="text-decoration:none">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </a>
    <form method="POST" style="margin:0">
      <input type="hidden" name="pageTitle" value="<?php echo htmlspecialchars($title); ?>">
      <button type="submit" name="toggle_page_sub" class="hbtn" style="height:36px;padding:0 15px;cursor:pointer">
        <?php echo isSubscribedToPage($userId, $title) ? 'Unsubscribe' : 'Subscribe'; ?>
      </button>
    </form>
    <?php endif; ?>
  </div>

  <div class="article-header">
    <?php if ($meta): ?>
    <div class="article-breadcrumb">
      <?php echo htmlspecialchars($meta['primaryCategory']); ?> &rsaquo; <?php echo htmlspecialchars($meta['secondaryCategory']); ?>
    </div>
    <?php endif; ?>
    <div class="article-title"><?php echo htmlspecialchars($title); ?></div>
  </div>

  <article class="article-content" id="articleBody"></article>

<?php endif; ?>

</div>

<script>
function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}
<?php if ($state === 'article'): ?>
const articleContent = <?php echo json_encode($Parsedown->text($content)); ?>;
<?php endif; ?>
</script>
<?php if ($state === 'article'): ?>
<script>
document.getElementById('articleBody').innerHTML = articleContent;
</script>
<?php endif; ?>
<script src="../scripts/ajaxHandler.js?v=3"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
