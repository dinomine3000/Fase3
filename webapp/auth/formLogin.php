<!DOCTYPE html>
<?php
require_once( "../../Lib/lib.php" );
require_once( "../../Lib/wikiLib.php" );
include_once( "../../Lib/lang/translator.php");

$flags[] = FILTER_NULL_ON_FAILURE;

$serverName = filter_input( INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING, $flags);
$name = webAppName();

if (!isset($_SESSION)) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
if ($isLoggedIn) {
    header("Location: ../index.php");
    exit();
}

$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_UNSAFE_RAW, $flags);
$_INPUT_METHOD = ($method === 'POST') ? INPUT_POST : INPUT_GET;
$flags[] = FILTER_NULL_ON_FAILURE;
$message = filter_input($_INPUT_METHOD, 'message', FILTER_UNSAFE_RAW, $flags);
?>
<html lang="en" data-theme="light">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../wiki/styles/wiki.css?v=4">
<link rel="stylesheet" href="../wiki/styles/form.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<?php include('/works/webapp/wiki/header.php')?>

<div class="container-lg py-4 d-flex justify-content-center">
  <div class="form-card" style="width:100%;max-width:400px">

    <div class="form-title"><?php echo lang("login");?></div>

    <?php if (isset($message) && $message !== ''): ?>
      <?php if ($message === 'success'): ?>
        <div class="status-banner success" style="margin-bottom:1rem">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          <?php echo lang("email_validated");?>
        </div>
      <?php else: ?>
        <div class="status-banner error" style="margin-bottom:1rem">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?php echo $message === 'failed' ? lang("email_failed") : lang("error") . htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <form action="processFormLogin.php" method="POST">

      <div class="form-group">
        <label class="form-label" for="username"><?php echo lang("username");?></label>
        <input class="form-input" type="text" id="username" name="username"
               placeholder="<?php echo lang("username_placeholder");?>" required autofocus>
      </div>

      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" for="password"><?php echo lang("password");?></label>
        <input class="form-input" type="password" id="password" name="password"
               placeholder="<?php echo lang("password_placeholder");?>" required>
      </div>

      <div class="form-actions" style="flex-direction:column;gap:0.75rem">
        <button type="submit" class="hbtn primary" style="width:100%;justify-content:center">
          <?php echo lang("login");?>
        </button>
        <button type="reset" class="hbtn" style="width:100%;justify-content:center">
          <?php echo lang("clear");?>
        </button>
      </div>

    </form>

    <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border);display:flex;gap:1rem;justify-content:center">
      <a href="formSignUp.php" style="font-size:12px;color:var(--muted);text-decoration:none;transition:color .15s"
         onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <?php echo lang("create_account");?>
      </a>
      <span style="color:var(--border2)">·</span>
      <a href="javascript:history.back()" style="font-size:12px;color:var(--muted);text-decoration:none;transition:color .15s"
         onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <?php echo lang("go_back");?>
      </a>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
