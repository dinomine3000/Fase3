<?php
include_once("../Lib/lib.php");
require_once( "../Lib/wikiLib.php" );
//Display dos erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

if (!isset($_SESSION)) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
$name = $isLoggedIn ? $_SESSION['username'] : null;
$role = $name ? getUserRoleInfo($name)['friendlyName'] : null;

$primaryCategories = getCategoryList('primary');
$shades = ['s1','s2','s3','s4','s5'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smiki — Home</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="wiki/styles/wiki.css?v=3">
<link rel="stylesheet" href="wiki/styles/home.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="home.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div class="search-outer">
        <div class="search-wrap">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchInput" placeholder="Search wiki…"
                 oninput="searchAllHeader(this.value,'hdr-suggest','wiki/')"
                 onblur="setTimeout(()=>{let s=document.getElementById('hdr-suggest');if(s){s.innerHTML='';s.classList.remove('has-results');}},150)"
                 onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='wiki/search.php?q='+encodeURIComponent(this.value.trim())">
        </div>
        <div id="hdr-suggest" class="search-suggest"></div>
      </div>
      <button class="theme-toggle" onclick="toggleTheme()" title="Toggle light/dark">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <a href="foruns/forum.php" class="hbtn primary" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Forum
      </a>
      <?php if ($isLoggedIn): ?>
      <a href="wiki/profile.php?user=<?php echo urlencode($name); ?>" class="hbtn icon" style="text-decoration:none" title="My Profile">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>
      <form action="auth/logout.php" method="POST" style="margin:0">
        <button type="submit" class="hbtn icon" title="Logout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </form>
      <?php else: ?>
      <a href="auth/formLogin.php" class="hbtn" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Login
      </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<div class="container-lg py-4">

  <?php if ($isLoggedIn): ?>
  <div class="welcome-block">
    <div class="welcome-name">Welcome back, <?php echo htmlspecialchars($name); ?></div>
    <?php if ($role): ?>
    <div class="welcome-role"><?php echo htmlspecialchars($role); ?></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php
  if ($name && authorizeUserByLevel($name, 'organizer')):
    $pendingCount = getPendingProposalsCount();
    if ($pendingCount > 0):
  ?>
  <a href="wiki/proposals.php" class="proposals-block" style="text-decoration:none">
    <div class="proposals-count"><?php echo (int)$pendingCount; ?></div>
    <div>
      <div class="proposals-label">Pending proposals</div>
      <div class="proposals-sub">Open moderation queue — click to review</div>
    </div>
  </a>
  <?php endif; endif; ?>

  <div class="section-heading">Categories</div>

  <?php if (empty($primaryCategories)): ?>
    <div class="no-results">No categories yet.</div>
  <?php else: ?>
  <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-2 mb-4">
    <?php foreach ($primaryCategories as $i => $cat):
      $shade   = $shades[$i % 5];
      $catName = htmlspecialchars($cat['primaryCategory']);
    ?>
    <div class="col">
      <a href="wiki/viewPage.php?primaryCategory=<?php echo urlencode($cat['primaryCategory']); ?>" style="text-decoration:none;display:block">
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
  <?php endif; ?>

  <?php if ($isLoggedIn && (
    authorizeUserByLevel($name, 'user') ||
    authorizeUserByLevel($name, 'editor') ||
    authorizeUserByLevel($name, 'organizer')
  )): ?>
  <div class="section-heading mt-4">Actions</div>
  <div class="action-grid">

    <?php if (authorizeUserByLevel($name, 'user')): ?>
    <a href="files/list.php" class="action-card" style="text-decoration:none">
      <div class="action-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      </div>
      <div class="action-label">View Files</div>
      <div class="action-desc">Browse uploaded images and media files</div>
    </a>
    <?php endif; ?>

    <?php if (authorizeUserByLevel($name, 'editor')): ?>
    <a href="wiki/create.php" class="action-card" style="text-decoration:none">
      <div class="action-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      </div>
      <div class="action-label">Create a Page</div>
      <div class="action-desc">Write and publish a new wiki article</div>
    </a>
    <?php endif; ?>

    <?php if (authorizeUserByLevel($name, 'organizer')): ?>
    <a href="wiki/manage_categories.php" class="action-card" style="text-decoration:none">
      <div class="action-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div class="action-label">Manage Categories</div>
      <div class="action-desc">Create primary and secondary category structure</div>
    </a>

    <a href="wiki/proposals.php" class="action-card" style="text-decoration:none">
      <div class="action-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      </div>
      <div class="action-label">Moderation Queue</div>
      <div class="action-desc">Review and approve pending page edit proposals</div>
    </a>
    <?php endif; ?>

  </div>
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
<script src="scripts/ajaxHandler.js?v=3"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
