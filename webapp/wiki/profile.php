<?php
require_once( "../../Lib/wikiLib.php" );
include_once("../../Lib/extendedParsedown.php");
include_once("../../Lib/db.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION)) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
$clientName = $isLoggedIn ? $_SESSION['username'] : null;

$flags[] = FILTER_NULL_ON_FAILURE;

$pageUsername = filter_input( INPUT_GET, 'user', FILTER_UNSAFE_RAW, $flags);
if(!isset($pageUsername)){
    header("Location: ../index.php");
    exit();
}

$submitted_role = filter_input( INPUT_POST, 'new_role', FILTER_UNSAFE_RAW, $flags);
$isModerator = authorizeUserByLevel($clientName, 'moderator');
$canChangeRoles = $isLoggedIn && $isModerator && $clientName !== $pageUsername;
if(isset($submitted_role) && $canChangeRoles){
    $rolePage = getUserRoleInfo($pageUsername);
    $roleClient = $clientName == null ? 0 : getUserRoleInfo($clientName);
    if ($rolePage['roleLevel'] < $roleClient['roleLevel']) {
        changeUserInfo($pageUsername, "idRole", $submitted_role);
    }
}

$newBio = filter_input( INPUT_POST, 'new_bio', FILTER_UNSAFE_RAW, $flags);
if (isset($newBio) && $isLoggedIn && $pageUsername === $clientName) {
    changeUserInfo($pageUsername, "bio", mb_substr($newBio, 0, 256, 'UTF-8'));
}

$newBan = filter_input( INPUT_POST, 'banning', FILTER_UNSAFE_RAW, $flags);
if ($isModerator && isset($newBan) && $pageUsername !== $clientName) {
    $rolePage = getUserRoleInfo($pageUsername);
    $roleClient = $clientName == null ? 0 : getUserRoleInfo($clientName);
    if ($rolePage['roleLevel'] < $roleClient['roleLevel']) {
        changeUserInfo($pageUsername, "isBanned", $newBan);
    }
}

$user = getUserInfo($pageUsername);
if($user === null || !isset($user)){
    header("Location: ../index.php");
    exit();
}
$user['role'] = getUserRoleInfo($pageUsername);

$canBan = $isModerator && $pageUsername !== $clientName && $user['role']['roleLevel'] < getUserRoleInfo($clientName)['roleLevel'];

$availableRoles = [];
if ($canChangeRoles) {
    $rawRoles = getAvailableRolesUpToUser($pageUsername);
    foreach ($rawRoles as $r) {
        if ($r['friendlyName'] !== $user['role']['friendlyName']) {
            $availableRoles[] = $r;
        }
    }
}

$isOwnProfile = $isLoggedIn && ($clientName === $pageUsername);
$status_label = $user['isBanned'] ? 'Banned' : ($user['active'] ? 'Active' : 'Unverified');
$statusClass  = $user['isBanned'] ? 'banned' : ($user['active'] ? 'active' : 'unverified');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageUsername); ?> — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css?v=3">
<link rel="stylesheet" href="styles/form.css">
<link rel="stylesheet" href="styles/profile.css">
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
      <?php if ($isLoggedIn): ?>
      <a href="profile.php?user=<?php echo urlencode($clientName); ?>" class="hbtn icon" style="text-decoration:none" title="My Profile">
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

  <div class="profile-hero">
    <div class="profile-avatar">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <div>
      <div class="profile-name"><?php echo htmlspecialchars($pageUsername); ?></div>
      <div class="profile-meta">
        <span class="art-tag"><?php echo htmlspecialchars($user['role']['friendlyName']); ?></span>
        <span class="profile-status <?php echo $statusClass; ?>"><?php echo $status_label; ?></span>
      </div>
    </div>
  </div>

  <div class="profile-stats">
    <div class="stat-chip">
      <div class="stat-value"><?php echo (int)$user['contributions']; ?></div>
      <div class="stat-label">Contributions</div>
    </div>
  </div>

  <div class="section-heading">Bio</div>

  <div class="profile-bio-card">
    <div id="bio-view">
      <div class="profile-bio-content">
        <?php echo (new ExtendedParsedown())->text($user['bio']); ?>
      </div>
      <?php if ($isOwnProfile): ?>
      <div class="profile-bio-actions">
        <button type="button" id="edit-bio-btn" class="hbtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit Bio
        </button>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($isOwnProfile): ?>
    <form id="bio-edit-form" method="POST" action="?user=<?php echo urlencode($pageUsername); ?>" style="display:none">
      <div class="form-group" style="margin-top:0">
        <div class="md-toolbar">
          <button type="button" class="md-btn" onclick="insertBio('**','**')"><b>B</b></button>
          <button type="button" class="md-btn" onclick="insertBio('*','*')"><em>I</em></button>
          <button type="button" class="md-btn" onclick="insertBio('# ','')">H1</button>
          <button type="button" class="md-btn" onclick="insertBio('## ','')">H2</button>
          <button type="button" class="md-btn" onclick="insertBio('### ','')">H3</button>
          <button type="button" class="md-btn" onclick="insertBio('`','`')">Code</button>
          <button type="button" class="md-btn" onclick="insertBio('\n```\n','\n```')">Block</button>
          <button type="button" class="md-btn" onclick="insertBio('- ','')">List</button>
          <button type="button" class="md-btn" onclick="insertBio('[','](url)')">Link</button>
        </div>
        <textarea class="form-textarea" id="bio-content" maxlength="256" name="new_bio" rows="5"><?php echo htmlspecialchars($user['bio']); ?></textarea>
      </div>
      <div class="form-actions">
        <button type="submit" class="hbtn primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Save
        </button>
        <button type="button" id="cancel-bio-btn" class="hbtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          Cancel
        </button>
      </div>
    </form>
    <?php endif; ?>
  </div>

  <?php if ($canChangeRoles): ?>
  <div class="section-heading">Change Role</div>
  <div class="form-card" style="margin-bottom:1rem">
    <form method="POST" action="?user=<?php echo urlencode($pageUsername); ?>">
      <div class="form-group">
        <label class="form-label" for="new_role">Assign a new role</label>
        <select class="form-select" name="new_role" id="new_role">
          <?php foreach ($availableRoles as $r): ?>
          <option value="<?php echo htmlspecialchars($r['idRole']); ?>">
            <?php echo htmlspecialchars($r['friendlyName']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-actions">
        <button type="submit" class="hbtn primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Update Role
        </button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <?php if ($canBan): ?>
  <div class="form-card" style="margin-bottom:1.5rem">
    <div class="form-title">Moderation</div>
    <form method="POST" action="?user=<?php echo urlencode($pageUsername); ?>">
      <input type="hidden" name="banning" value="<?php echo $user['isBanned'] ? 0 : 1; ?>">
      <div class="form-actions">
        <button type="submit" class="hbtn <?php echo $user['isBanned'] ? 'primary' : 'danger'; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <?php if ($user['isBanned']): ?>
            <polyline points="20 6 9 17 4 12"/>
            <?php else: ?>
            <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            <?php endif; ?>
          </svg>
          <?php echo $user['isBanned'] ? "Unban " . htmlspecialchars($pageUsername) : "Ban " . htmlspecialchars($pageUsername); ?>
        </button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="section-heading">Find User</div>
  <div class="profile-find-wrap">
    <form id="userSearch" method="GET" action="">
      <div class="search-wrap">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" name="user" id="userSearchbar" placeholder="Search users…">
      </div>
    </form>
    <div id="autocomplete-suggestions"></div>
  </div>

</div>

<script>
function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}
function insertBio(before, after) {
  const ta = document.getElementById('bio-content');
  if (!ta) return;
  const start = ta.selectionStart, end = ta.selectionEnd;
  const sel = ta.value.substring(start, end);
  ta.value = ta.value.substring(0, start) + before + sel + after + ta.value.substring(end);
  ta.selectionStart = start + before.length;
  ta.selectionEnd   = start + before.length + sel.length;
  ta.focus();
}
</script>
<script src="../scripts/ajaxHandler.js?v=3"></script>
<script>
document.getElementById('userSearchbar').addEventListener('input', function() {
  searchUsers(this.value);
});
</script>
<?php if ($isOwnProfile): ?>
<script src="js/profile.js"></script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
