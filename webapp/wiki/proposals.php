<?php
require_once( "../../Lib/lib.php" );
require_once( "../../Lib/db.php" );
require_once( "../../Lib/wikiLib.php" );

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
<link rel="stylesheet" href="styles/wiki.css?v=4">
<link rel="stylesheet" href="styles/proposals.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<?php include('./header.php')?>


<div class="container-lg py-4">
  <div class="section-heading"><?php echo lang('moderation_queue'); ?></div>

  <?php if (empty($proposals)): ?>
    <div class="empty-state">
      <div class="empty-state-title"><?php echo lang('no_pending_proposals'); ?></div>
      <?php echo lang('proposals_processed'); ?>
    </div>
  <?php else: ?>
    <?php foreach ($proposals as $p): ?>
    <div class="proposal-card">

      <div class="proposal-header">
        <div class="proposal-title"><?php echo htmlspecialchars($p['pageTitle']); ?></div>
        <div class="proposal-author">
          <?php echo lang('by'); ?> <?php echo htmlspecialchars($p['editorName'] ?? 'Unknown'); ?>
        </div>
      </div>

      <div class="diff-row">
        <div class="diff-col">
          <div class="diff-col-label"><?php echo lang('current_content'); ?></div>
          <div class="diff-content"><?php echo htmlspecialchars(strip_tags($p['currentContent'] ?? '[No existing content]')); ?></div>
        </div>
        <div class="diff-col">
          <div class="diff-col-label"><?php echo lang('proposed_change'); ?></div>
          <div class="diff-content"><?php echo htmlspecialchars(strip_tags($p['newContent'])); ?></div>
        </div>
      </div>

      <div class="proposal-actions">
        <form method="POST" style="margin:0"
              onsubmit="return confirm('<?php echo lang('confirm_accept'); ?>')">
          <input type="hidden" name="changeId" value="<?php echo (int)$p['changeId']; ?>">
          <button type="submit" name="action" value="accept" class="hbtn primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <?php echo lang('accept'); ?>
          </button>
        </form>
        <form method="POST" style="margin:0"
              onsubmit="return confirm('<?php echo lang('confirm_reject'); ?>')">
          <input type="hidden" name="changeId" value="<?php echo (int)$p['changeId']; ?>">
          <button type="submit" name="action" value="deny" class="hbtn danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            <?php echo lang('reject'); ?>
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
<script src="../scripts/ajaxHandler.js?v=3"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
