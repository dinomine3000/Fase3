<?php
require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");
require_once("../../Lib/wikiLib.php");
include_once("../../Lib/lang/translator.php");

if (!isset($_SESSION)) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
$name = $isLoggedIn ? $_SESSION['username'] : '';

if (!$isLoggedIn || !authorizeUserByLevel($name, 'organizer')) {
    header("Location: ../index.php");
    exit();
}

$configurations = getConfiguration();
$maxBytes = (int)$configurations['maxFileSize'];
$maxMb    = $maxBytes > 0 ? round($maxBytes / 1048576, 1) : 0;

$_langSwitch = ($current_lang === 'en') ? 'pt' : 'en';
$_qp = $_GET; $_qp['lang'] = $_langSwitch;
$_langToggleUrl = '?' . http_build_query($_qp);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload File — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../wiki/styles/wiki.css?v=4">
<link rel="stylesheet" href="../wiki/styles/form.css?v=3">
<style>
.file-drop {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 0.5rem; text-align: center; cursor: pointer;
  padding: 2rem 1rem; border: 1px dashed var(--border2); border-radius: 9px;
  background: var(--bg3); color: var(--muted);
  transition: border-color .2s, background .25s, color .2s;
}
.file-drop:hover, .file-drop.dragover { border-color: var(--accent); color: var(--text); }
.file-drop svg { width: 26px; height: 26px; color: var(--faint); }
.file-drop-text { font-size: 13px; }
.file-drop-hint { font-family: 'Outfit', sans-serif; font-size: 10px; color: var(--faint); }
.file-drop-name {
  font-family: 'Outfit', sans-serif; font-size: 11px; color: var(--accent);
  word-break: break-all; display: none;
}
.file-drop.has-file .file-drop-name { display: block; }
.file-input-hidden { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
</style>
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="../home.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div style="flex:1"></div>
      <a href="list.php" class="hbtn" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        <?php echo lang('go_back'); ?>
      </a>
      <a href="<?php echo $_langToggleUrl; ?>" class="lang-toggle" title="<?php echo lang('switch_language'); ?>" style="text-decoration:none"><?php echo strtoupper($_langSwitch); ?></a>
      <button class="theme-toggle" onclick="toggleTheme()" title="<?php echo lang('toggle_theme'); ?>">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <a href="../wiki/profile.php?user=<?php echo urlencode($name); ?>" class="hbtn icon" style="text-decoration:none" title="<?php echo lang('my_profile'); ?>">
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
  <div class="section-heading"><?php echo lang('upload_file'); ?></div>

  <div class="form-card">
    <div class="form-title"><?php echo lang('new_file'); ?></div>

    <form enctype="multipart/form-data" action="processFormUpload.php" method="POST" name="FormUpload">

      <div class="form-group">
        <label class="form-label" for="title"><?php echo lang('title'); ?></label>
        <input class="form-input" type="text" id="title" name="title" placeholder="<?php echo lang('title_placeholder'); ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="userFile"><?php echo lang('file_label'); ?></label>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxBytes; ?>">
        <label class="file-drop" id="fileDrop" for="userFile">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span class="file-drop-text"><?php echo lang('choose_file'); ?></span>
          <?php if ($maxMb > 0): ?>
          <span class="file-drop-hint"><?php echo lang('max_size'); ?> <?php echo $maxMb; ?> MB</span>
          <?php endif; ?>
          <span class="file-drop-name" id="fileName"></span>
        </label>
        <input class="file-input-hidden" type="file" id="userFile" name="userFile" required>
      </div>

      <div class="form-actions">
        <button type="submit" name="Submit" value="Upload file" class="hbtn primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
          <?php echo lang('upload_file'); ?>
        </button>
        <a href="list.php" class="hbtn" style="text-decoration:none">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          <?php echo lang('cancel'); ?>
        </a>
      </div>

    </form>
  </div>
</div>

<script>
function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}

(function () {
  const input = document.getElementById('userFile');
  const drop  = document.getElementById('fileDrop');
  const nameEl = document.getElementById('fileName');

  function showName() {
    if (input.files && input.files.length) {
      nameEl.textContent = input.files[0].name;
      drop.classList.add('has-file');
    } else {
      nameEl.textContent = '';
      drop.classList.remove('has-file');
    }
  }
  input.addEventListener('change', showName);

  ['dragenter', 'dragover'].forEach(function (ev) {
    drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('dragover'); });
  });
  ['dragleave', 'drop'].forEach(function (ev) {
    drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('dragover'); });
  });
  drop.addEventListener('drop', function (e) {
    if (e.dataTransfer && e.dataTransfer.files.length) {
      input.files = e.dataTransfer.files;
      showName();
    }
  });
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
