<?php
require_once( "../../Lib/lib.php" );
require_once( "../../Lib/db.php" );
require_once( "../../Lib/wikiLib.php" );

if (!isset($_SESSION)) {
    session_start();
}
$username = $_SESSION['username'] ?? '';

$isAdmin     = authorizeUserByLevel($username, 'admin');
$isOrganizer = authorizeUserByLevel($username, 'organizer');

if (!$isOrganizer) {
    header('HTTP/1.1 403 Forbidden');
    die("Access Denied: You do not have permissions to manage categories.");
}

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_primary']) && $isAdmin) {
        $name = $_POST['primary_name'] ?? '';
        if (createPrimaryCategory($name)) {
            $message = "Primary category '" . htmlspecialchars($name) . "' created successfully.";
        } else {
            $error = "Failed to create primary category. It may already exist.";
        }
    } elseif (isset($_POST['create_secondary'])) {
        $primary   = $_POST['parent_primary'] ?? '';
        $secondary = $_POST['secondary_name'] ?? '';
        if (createSecondaryCategory($primary, $secondary)) {
            $message = "Secondary category '" . htmlspecialchars($secondary) . "' added to '" . htmlspecialchars($primary) . "'.";
        } else {
            $error = "Failed to create secondary category. Duplicate binding detected.";
        }
    }
}

$primaryCategoriesData = getCategoryList('primary');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Categories</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css?v=3">
<link rel="stylesheet" href="styles/form.css">
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
      <a href="profile.php?user=<?php echo urlencode($username); ?>" class="hbtn icon" style="text-decoration:none" title="My Profile">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>
      <form action="../auth/logout.php" method="POST" style="margin:0">
        <button type="submit" class="hbtn icon" title="Logout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </form>
    </div>
  </div>
</header>

<div class="container-lg py-4">
  <div class="section-heading">Category Architecture</div>

  <?php if (!empty($message)): ?>
    <div class="status-banner success mb-3">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      <?php echo $message; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="status-banner error mb-3">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <div class="d-flex flex-column gap-3">

    <?php if ($isAdmin): ?>
    <div class="form-card">
      <span class="role-label">Admin only</span>
      <div class="form-title">Create Primary Category</div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label" for="primary_name">Category Name</label>
          <input class="form-input" type="text" id="primary_name" name="primary_name"
                 placeholder="e.g., Science" required pattern="[A-Za-z0-9_\-]+">
        </div>
        <div class="form-actions">
          <button type="submit" name="create_primary" class="hbtn primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create Primary
          </button>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <div class="form-card">
      <div class="form-title">Create Secondary Category</div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label" for="parent_primary">Parent Primary Category</label>
          <select class="form-select" id="parent_primary" name="parent_primary" required>
            <option value="">— Select parent category —</option>
            <?php foreach ($primaryCategoriesData as $catRow):
              $catName = $catRow['primaryCategory'];
            ?>
            <option value="<?php echo htmlspecialchars($catName); ?>"><?php echo htmlspecialchars($catName); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="secondary_name">Sub-Category Name</label>
          <input class="form-input" type="text" id="secondary_name" name="secondary_name"
                 placeholder="e.g., Quantum Mechanics" required pattern="[A-Za-z0-9_\-]+">
        </div>
        <div class="form-actions">
          <button type="submit" name="create_secondary" class="hbtn primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create Secondary
          </button>
          <button type="button" class="hbtn" onclick="history.back()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Go Back
          </button>
        </div>
      </form>
    </div>

  </div>
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
