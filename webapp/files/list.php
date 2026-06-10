<?php
require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");

$configDetails = getConfiguration();
$numColls = 0 + $configDetails['numColls'];

dbConnect(ConfigFile);
mysqli_select_db($GLOBALS['ligacao'], $GLOBALS['configDataBase']->db);
$query  = "SELECT `id`, `fileName`, `title` FROM `images-details`";
$result = mysqli_query($GLOBALS['ligacao'], $query);

if (!isset($_SESSION)) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
$name = $isLoggedIn ? $_SESSION['username'] : '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Files — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../wiki/styles/wiki.css?v=3">
<style>
.file-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 0.75rem;
}
.file-card {
  background: var(--bg2); border: 1px solid var(--border);
  border-radius: 9px; overflow: hidden;
  transition: border-color .2s, box-shadow .2s, background .25s;
}
.file-card:hover { border-color: var(--border2); box-shadow: 0 2px 10px var(--shadow); }
.file-thumb {
  width: 100%; aspect-ratio: 1;
  background: var(--bg3); border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  overflow: hidden; transition: background .25s;
}
.file-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.file-info { padding: 0.65rem 0.75rem; }
.file-name { font-size: 12px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-id   { font-family: 'Outfit', sans-serif; font-size: 10px; color: var(--faint); margin-top: 1px; }
</style>
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="../home.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div style="flex:1"></div>
      <button class="theme-toggle" onclick="toggleTheme()" title="Toggle light/dark">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <?php if (isset($name) && authorizeUserByLevel($name, 'organizer')): ?>
      <a href="./formUpload.php" class="hbtn primary" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
        Upload File
      </a>
      <?php endif; ?>
      <button class="hbtn" onclick="history.back()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Go Back
      </button>
      <?php if ($isLoggedIn): ?>
      <a href="../wiki/profile.php?user=<?php echo urlencode($name); ?>" class="hbtn icon" style="text-decoration:none" title="My Profile">
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
  <div class="section-heading">Files</div>
  <div class="file-grid">
    <?php while ($imageData = mysqli_fetch_array($result)):
      $id        = $imageData['id'];
      $fileTitle = $imageData['title'];
    ?>
    <div class="file-card">
      <div class="file-thumb">
        <img src="showFileThumb.php?id=<?php echo (int)$id; ?>"
             alt="<?php echo htmlspecialchars($fileTitle); ?>"
             loading="lazy">
      </div>
      <div class="file-info">
        <div class="file-name"><?php echo htmlspecialchars($fileTitle); ?></div>
        <div class="file-id">ID: <?php echo (int)$id; ?></div>
      </div>
    </div>
    <?php endwhile;
    mysqli_free_result($result);
    dbDisconnect();
    ?>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
