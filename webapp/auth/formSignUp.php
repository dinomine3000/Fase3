<?php
require_once("../../Lib/db.php");
require_once( "../../Lib/wikiLib.php" );
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
<link rel="stylesheet" href="../wiki/styles/wiki.css">
<link rel="stylesheet" href="../wiki/styles/form.css">
<script type="text/javascript" src="forms.js"></script>
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
    </div>
  </div>
</header>

<div class="container-lg py-4 d-flex justify-content-center">
  <div class="form-card" style="width:100%;max-width:400px">

    <div class="form-title">Create account</div>

    <?php if (isset($error) && $error !== ''): ?>
      <div class="status-banner error" style="margin-bottom:1rem">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php
        $msgs = [
          'badCode'   => 'Wrong captcha code.',
          'badName'   => 'Username already exists.',
          'badEmail'  => 'Email already exists.',
          'badRepeat' => 'Passwords do not match.',
          'invalid'   => 'Invalid values submitted.',
        ];
        echo isset($msgs[$error]) ? $msgs[$error] : 'Bad field: ' . htmlspecialchars($error);
        ?>
      </div>
    <?php endif; ?>

    <form action="processFormSignUp.php" method="POST"
          onsubmit="return FormSignupValidator(this)" name="FormSignup">

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input class="form-input" required type="text" id="username" name="username"
               placeholder="Choose a username" autofocus>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email</label>
        <input class="form-input" required type="text" id="email" name="email"
               placeholder="your@email.com">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" required type="password" id="password" name="password"
               placeholder="Choose a password">
      </div>

      <div class="form-group">
        <label class="form-label" for="password_2">Repeat password</label>
        <input class="form-input" required type="password" id="password_2" name="password_2"
               placeholder="Repeat your password">
      </div>

      <div class="form-group">
        <label class="form-label">Captcha</label>
        <img src="../captcha/captchaImage.php" style="display:block;margin-bottom:6px"><br>
        <label for="captcha" style="font-family:'Outfit',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;color:var(--faint)">Enter the code above</label><br>
        <input required type="text" name="captcha" id="captcha" class="form-input" style="margin-top:4px">
      </div>

      <div class="form-actions" style="flex-direction:column;gap:0.75rem">
        <button type="submit" class="hbtn primary" style="width:100%;justify-content:center">
          Create account
        </button>
        <button type="reset" class="hbtn" style="width:100%;justify-content:center">
          Clear
        </button>
      </div>

    </form>

    <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border);display:flex;gap:1rem;justify-content:center">
      <a href="formLogin.php" style="font-size:12px;color:var(--muted);text-decoration:none;transition:color .15s"
         onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        Sign In
      </a>
      <span style="color:var(--border2)">·</span>
      <a href="javascript:history.back()" style="font-size:12px;color:var(--muted);text-decoration:none;transition:color .15s"
         onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--muted)'">
        Go back
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
