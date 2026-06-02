<?php
require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");

if (!isset($_SESSION)) {
    session_start();
}
if (!authorizeUserByLevel($_SESSION['username'] ?? '', 'organizer')) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['changeId'])) {
    $changeId = (int)$_POST['changeId'];
    $action   = $_POST['action'] === 'accept' ? 'accept' : 'deny';
    moderateProposal($changeId, $action);
    header("Location: proposals.php");
    exit;
}

$proposals = getAllProposals();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Moderation Queue</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css">
<link rel="stylesheet" href="styles/proposals.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="../home.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div class="search-wrap flex-grow-1">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search wiki…"
               onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='search.php?q='+encodeURIComponent(this.value.trim())">
      </div>
      <button class="theme-toggle" onclick="toggleTheme()" title="Toggle light/dark">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <a href="../foruns/forum.php" class="hbtn primary" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Forum
      </a>
      <a href="../auth/formLogin.php" class="hbtn" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Login
      </a>
    </div>
  </div>
</header>

<div class="container-lg py-4">
  <div class="section-heading">Moderation Queue</div>

  <?php if (empty($proposals)): ?>
    <div class="empty-state">
      <div class="empty-state-title">No pending proposals</div>
      All user edit proposals have been processed.
    </div>
  <?php else: ?>
    <?php foreach ($proposals as $p): ?>
    <div class="proposal-card">

      <div class="proposal-header">
        <div class="proposal-title"><?php echo htmlspecialchars($p['pageTitle']); ?></div>
        <div class="proposal-author">
          by <?php echo htmlspecialchars($p['editorName'] ?? 'Unknown'); ?>
        </div>
      </div>

      <div class="diff-row">
        <div class="diff-col">
          <div class="diff-col-label">Current content</div>
          <div class="diff-content"><?php echo htmlspecialchars(strip_tags($p['currentContent'] ?? '[No existing content]')); ?></div>
        </div>
        <div class="diff-col">
          <div class="diff-col-label">Proposed change</div>
          <div class="diff-content"><?php echo htmlspecialchars(strip_tags($p['newContent'])); ?></div>
        </div>
      </div>

      <div class="proposal-actions">
        <form method="POST" style="margin:0"
              onsubmit="return confirm('Accept this proposal?')">
          <input type="hidden" name="changeId" value="<?php echo (int)$p['changeId']; ?>">
          <button type="submit" name="action" value="accept" class="hbtn primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Accept
          </button>
        </form>
        <form method="POST" style="margin:0"
              onsubmit="return confirm('Reject this proposal?')">
          <input type="hidden" name="changeId" value="<?php echo (int)$p['changeId']; ?>">
          <button type="submit" name="action" value="deny" class="hbtn danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Reject
          </button>
        </form>
      </div>

    </div>
    <?php endforeach; ?>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
