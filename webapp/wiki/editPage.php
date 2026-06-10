<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once("../../Lib/lib.php");
require_once("../../Lib/wikiLib.php");

if (!isset($_SESSION)) {
    session_start();
}

$name = $_SESSION['username'];
if (!isset($name) || !authorizeUserByLevel($name, 'user')) {
    header("Location: ../index.php");
    exit();
}

$title = filter_input(INPUT_GET, 'pageTitle', FILTER_UNSAFE_RAW);

if (empty($title)) {
    echo "Error: No page title specified for editing.";
    exit();
}

$content = readWikiPage($title);

if (!isset($content)) {
    echo "Error: The requested page does not exist.";
    exit();
}

$willAutoPublish = authorizeUserByLevel($name, 'editor');

if (isset($name)) {
    $allowedRoles = getAvailableRolesUpToUser($name);
} else {
    $allowedRoles = [];
}

$meta = getPageMetaData($title);
$currentPageLevel = isset($meta['visibility']) ? (int)$meta['visibility'] : 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit — <?php echo htmlspecialchars($title); ?></title>
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
      <a href="profile.php?user=<?php echo urlencode($name); ?>" class="hbtn icon" style="text-decoration:none" title="My Profile">
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
  <div class="section-heading">Edit Page</div>

  <div class="form-card">
    <div class="form-title"><?php echo htmlspecialchars($title); ?></div>

    <?php if ($willAutoPublish): ?>
    <div class="status-banner success" style="margin-bottom:1.25rem">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      Your changes will be published immediately.
    </div>
    <?php else: ?>
    <div class="status-banner" style="margin-bottom:1.25rem;background:rgba(180,140,0,0.07);border:1px solid rgba(180,140,0,0.25);color:#a07800">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Your changes will be held in the moderation queue for approval.
    </div>
    <?php endif; ?>

    <form action="processEditPage.php" method="POST">
      <input type="hidden" name="pageTitle" value="<?php echo htmlspecialchars($title); ?>">

      <div class="form-group">
        <label class="form-label">Content (Markdown)</label>
        <div class="md-toolbar">
          <button type="button" class="md-btn" onclick="insertMd('**','**')"><b>B</b></button>
          <button type="button" class="md-btn" onclick="insertMd('*','*')"><em>I</em></button>
          <button type="button" class="md-btn" onclick="insertMd('# ','')">H1</button>
          <button type="button" class="md-btn" onclick="insertMd('## ','')">H2</button>
          <button type="button" class="md-btn" onclick="insertMd('### ','')">H3</button>
          <button type="button" class="md-btn" onclick="insertMd('`','`')">Code</button>
          <button type="button" class="md-btn" onclick="insertMd('\n```\n','\n```')">Block</button>
          <button type="button" class="md-btn" onclick="insertMd('- ','')">List</button>
          <button type="button" class="md-btn" onclick="insertMd('[','](url)')">Link</button>
        </div>
        <textarea class="form-textarea" id="content" name="content" required><?php echo htmlspecialchars($content); ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="visibility">Minimum role to view</label>
        <select class="form-select" id="visibility" name="visibility" required>
          <?php if (empty($allowedRoles)): ?>
            <option value="0" selected>Guest (default)</option>
          <?php else: ?>
            <?php foreach ($allowedRoles as $role): ?>
            <option value="<?php echo $role['roleLevel']; ?>"
              <?php echo ($role['roleLevel'] === $currentPageLevel) ? 'selected' : ''; ?>>
              <?php echo $role['roleLevel'] . ' — ' . htmlspecialchars($role['friendlyName']); ?>
            </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>

      <div class="form-actions">
        <button type="submit" class="hbtn primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Save Changes
        </button>
        <a href="viewPage.php?pageTitle=<?php echo urlencode($title); ?>" class="hbtn" style="text-decoration:none">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Cancel
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

function insertMd(before, after) {
  const ta = document.getElementById('content');
  const start = ta.selectionStart;
  const end   = ta.selectionEnd;
  const sel   = ta.value.substring(start, end);
  ta.value = ta.value.substring(0, start) + before + sel + after + ta.value.substring(end);
  ta.selectionStart = start + before.length;
  ta.selectionEnd   = start + before.length + sel.length;
  ta.focus();
}
</script>
<script src="../scripts/ajaxHandler.js?v=3"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
