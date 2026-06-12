<?php
require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");
require_once( "../../Lib/wikiLib.php" );
//Display dos erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

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
$isOrganizer = $isLoggedIn ? authorizeUserByLevel($name, 'organizer') : false;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Files — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../wiki/styles/wiki.css?v=4">
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

/* Styling for the new download link button */
.file-download-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  width: 100%;
  margin-top: 8px;
  padding: 4px 8px;
  font-size: 11px;
  font-weight: 500;
  color: #fff;
  background-color: #0d6efd;
  border-radius: 4px;
  text-decoration: none;
  transition: background-color 0.15s ease;
}
.file-download-btn:hover {
  background-color: #0b5ed7;
  color: #fff;
}
</style>
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<?php include('../wiki/header.php')?>

<div class="container-lg py-4">
  <div class="section-heading d-flex justify-content-between align-items-center w-100"><?php echo lang('files'); ?>
    <?php if (isset($name) && authorizeUserByLevel($name, 'organizer')): ?>
      <a href="./formUpload.php" class="hbtn primary" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
        <?php echo lang('upload_file'); ?>
      </a>
      <?php endif; ?>
  </div>
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
        <?php if($isOrganizer): ?>
          <a href="../wiki/getFileContents.php?id=<?php echo (int)$id; ?>" class="file-download-btn">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Download
          </a>
        <?php endif ?>
      </div>
    </div>
    <?php endwhile;
    mysqli_free_result($result);
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