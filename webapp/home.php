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

<?php include('./wiki/header.php'); ?>

<div class="container-lg py-4">
  <?php if ($isLoggedIn): ?>
  <div class="welcome-block">
    <div class="welcome-name">Welcome back, <?php echo htmlspecialchars($name); ?></div>
    <?php if ($role): ?>
    <div class="welcome-role"><?php echo htmlspecialchars($role); ?></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div>
    <?php 
      $portalId = 400;
      $portal2Id = 620;
      
      $url = "https://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1/?appid=$portalId";
      $response = file_get_contents($url);
      $data = json_decode($response, true);
      $portalPlayers = $data['response']['player_count'];
      
      $url = "https://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1/?appid=$portal2Id";
      $response = file_get_contents($url);
      $data = json_decode($response, true);
      $portal2Players = $data['response']['player_count'];

      echo "Current Portal players: " . $portalPlayers . "<br>Current Portal 2 Players: " . $portal2Players;
    ?>
  </div>

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

    <?php if (authorizeUserByLevel($name, 'admin')): ?>
    <a href="wiki/manage_db.php" class="action-card" style="text-decoration:none">
      <div class="action-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div class="action-label">Manage Website</div>
      <div class="action-desc">Modify configured e-mail accounts and DB settings</div>
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
