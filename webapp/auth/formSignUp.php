<?php
require_once("../../Lib/db.php");
require_once("../../Lib/wikiLib.php" );
require_once("../../Lib/lang/translator.php" );
loadConfigurationDataBase(constant("ConfigFile"));

$flags = [];
$flags[] = FILTER_NULL_ON_FAILURE;

$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_UNSAFE_RAW, $flags);
$_INPUT_METHOD = ($method === 'POST') ? INPUT_POST : INPUT_GET;

$error = filter_input($_INPUT_METHOD, 'error', FILTER_UNSAFE_RAW, $flags);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../wiki/styles/wiki.css?v=4">
<link rel="stylesheet" href="../wiki/styles/form.css">
<script type="text/javascript" src="forms.js"></script>
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<?php include('/works/webapp/wiki/header.php')?>

<div class="container-lg py-4 d-flex justify-content-center">
  <div class="form-card" style="width:100%;max-width:400px">

    <div class="form-title"><?php echo lang("create_account");?></div>
    <?php if (isset($error) && $error !== ''): ?>
      <div class="status-banner error" style="margin-bottom:1rem">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php
        $msgs = [
          'badCode'   => lang('err_bad_code'),
          'badName'   => lang('err_bad_name'),
          'badEmail'  => lang('err_bad_email'),
          'badRepeat' => lang('err_bad_repeat'),
          'invalid'   => lang('err_invalid'),
        ];
        echo isset($msgs[$error]) ? $msgs[$error] : lang('error') . htmlspecialchars($error);
        ?>
      </div>
    <?php endif; ?>

    <form action="processFormSignUp.php" method="POST"
          onsubmit="return FormSignupValidator(this)" name="FormSignup">

      <div class="form-group">
        <label class="form-label" for="username"><?php echo lang("username");?></label>
        <input class="form-input" required pattern="[A-Za-z0-9.]{2,}.*" type="text" id="username" name="username"
               placeholder="<?php echo lang('choose_username'); ?>" autofocus>
      </div>

      <div class="form-group">
        <label class="form-label" for="email"><?php echo lang('email'); ?></label>
        <input class="form-input" pattern="[^@]+@[^@]+\.[^@]+" required type="text" id="email" name="email"
               placeholder="<?php echo lang('email_placeholder'); ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="password"><?php echo lang('password'); ?></label>
        <input class="form-input" required pattern="^[A-Za-z0-9.\-#*,]{10,}$" type="password" id="password" name="password"
               placeholder="<?php echo lang('choose_password'); ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="password_2"><?php echo lang('repeat_password_label'); ?></label>
        <input class="form-input" required type="password" id="password_2" name="password_2"
               placeholder="<?php echo lang('repeat_password_placeholder'); ?>">
      </div>

      <div class="form-group">
        <label class="form-label"><?php echo lang('captcha'); ?></label>
        <img src="../captcha/captchaImage.php" style="display:block;margin-bottom:6px"><br>
        <label for="captcha" style="font-family:'Outfit',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--faint)"><?php echo lang('captcha_label'); ?></label><br>
        <input required type="text" name="captcha" id="captcha" class="form-input" style="margin-top:4px">
      </div>

      <div class="form-actions" style="flex-direction:column;gap:0.75rem">
        <button type="submit" class="hbtn primary" style="width:100%;justify-content:center">
          <?php echo lang('create_account'); ?>
        </button>
        <button type="reset" class="hbtn" style="width:100%;justify-content:center">
          <?php echo lang('clear'); ?>
        </button>
      </div>

    </form>

    <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border);display:flex;gap:1rem;justify-content:center">
      <a href="formLogin.php" style="font-size:12px;color:var(--muted);text-decoration:none;transition:color .15s"
         onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <?php echo lang('sign_in'); ?>
      </a>
      <span style="color:var(--border2)">·</span>
      <a href="formLogin.php" style="font-size:12px;color:var(--muted);text-decoration:none;transition:color .15s"
         onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        <?php echo lang('go_back'); ?>
      </a>
    </div>

  </div>
</div>

<!--
Source - https://stackoverflow.com/a/8814534
Posted by Bajrang, modified by community. See post 'Timeline' for change history
Retrieved 2026-05-21, License - CC BY-SA 3.0
-->

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
