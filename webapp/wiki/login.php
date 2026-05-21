<?php
// login.php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    // TODO: validate credentials against your DB here
    // Example:
    // $user = get_user_by_username($username);
    // if ($user && password_verify($password, $user['password_hash'])) {
    //     $_SESSION['user_id'] = $user['id'];
    //     header('Location: wiki.php'); exit;
    // } else {
    //     $error = 'Invalid username or password.';
    // }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — WikiBase</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg:      #111214;
  --bg2:     #18191c;
  --bg3:     #1f2023;
  --border:  rgba(255,255,255,0.07);
  --border2: rgba(255,255,255,0.14);
  --text:    #e2e2e0;
  --muted:   #888880;
  --faint:   #444440;
  --green:   #5a9e4a;
  --green2:  #6db85c;
  --red:     #9e4a4a;
  --mono:    'IBM Plex Mono', monospace;
  --sans:    'IBM Plex Sans', sans-serif;
}

body {
  background: var(--bg); color: var(--text);
  font-family: var(--sans); min-height: 100vh;
  display: flex; flex-direction: column;
}

/* HEADER */
.site-header {
  background: var(--bg); border-bottom: 1px solid var(--border);
}
.logo {
  font-family: var(--mono); font-size: 1rem; font-weight: 500;
  color: var(--green); text-decoration: none; letter-spacing: 0.06em;
}
.logo:hover { color: var(--green2); }

/* CENTER WRAPPER */
.login-wrap {
  flex: 1; display: flex; align-items: center; justify-content: center;
  padding: 2rem;
}

/* CARD */
.login-card {
  background: var(--bg2); border: 1px solid var(--border);
  border-radius: 10px; padding: 2.2rem 2rem;
  width: 100%; max-width: 380px;
}

.card-heading {
  font-family: var(--mono); font-size: 11px; font-weight: 500;
  letter-spacing: 0.14em; text-transform: uppercase;
  color: var(--muted); border-bottom: 1px solid var(--border);
  padding-bottom: 0.75rem; margin-bottom: 1.75rem;
}

/* FIELDS */
.field-label {
  font-family: var(--mono); font-size: 10px; letter-spacing: 0.1em;
  text-transform: uppercase; color: var(--muted); margin-bottom: 6px;
  display: block;
}
.field-input {
  width: 100%; background: var(--bg3); border: 1px solid var(--border);
  border-radius: 6px; color: var(--text); font-family: var(--sans);
  font-size: 14px; padding: 9px 12px; outline: none;
  transition: border-color .2s;
  /* override bootstrap */
  appearance: none; -webkit-appearance: none;
}
.field-input:focus { border-color: var(--border2); box-shadow: none; }
.field-input::placeholder { color: var(--faint); }
.field-input.is-invalid { border-color: var(--red); }

/* ERROR */
.error-msg {
  background: rgba(158,74,74,0.12); border: 1px solid rgba(158,74,74,0.25);
  border-radius: 6px; padding: 9px 12px;
  font-size: 13px; color: #d47070;
  font-family: var(--mono); letter-spacing: 0.02em;
}

/* FORGOT */
.forgot {
  font-family: var(--mono); font-size: 10px; letter-spacing: 0.05em;
  color: var(--faint); text-decoration: none; float: right;
  position: relative; top: -1px;
  transition: color .15s;
}
.forgot:hover { color: var(--muted); }

/* SUBMIT */
.btn-submit {
  width: 100%; height: 40px; background: var(--green);
  border: none; border-radius: 6px; color: #fff;
  font-family: var(--mono); font-size: 12px; font-weight: 500;
  letter-spacing: 0.07em; cursor: pointer;
  transition: background .15s;
}
.btn-submit:hover { background: var(--green2); }
.btn-submit:active { transform: scale(0.99); }

/* DIVIDER */
.divider {
  display: flex; align-items: center; gap: 10px;
  color: var(--faint); font-family: var(--mono); font-size: 10px; letter-spacing: 0.08em;
}
.divider::before, .divider::after {
  content: ''; flex: 1; height: 1px; background: var(--border);
}

/* REGISTER LINK */
.register-row {
  text-align: center; font-size: 13px; color: var(--muted);
}
.register-row a {
  color: var(--green); text-decoration: none; font-weight: 500;
}
.register-row a:hover { color: var(--green2); }

/* FOOTER */
footer {
  border-top: 1px solid var(--border); padding: 1rem 2rem;
  font-family: var(--mono); font-size: 10px; letter-spacing: 0.06em;
  color: var(--faint); text-align: center;
}
footer a { color: var(--faint); text-decoration: none; }
footer a:hover { color: var(--muted); }
</style>
</head>
<body>

<!-- HEADER -->
<header class="site-header">
  <div class="container-lg">
    <div class="d-flex align-items-center" style="height:56px">
      <a class="logo" href="wiki.php">wikibase</a>
    </div>
  </div>
</header>

<!-- LOGIN -->
<div class="login-wrap">
  <div class="login-card">

    <div class="card-heading">Sign in</div>

    <?php if ($error): ?>
    <div class="error-msg mb-3"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>

      <div class="mb-3">
        <label class="field-label" for="username">Username</label>
        <input
          class="field-input<?= $error ? ' is-invalid' : '' ?>"
          type="text" id="username" name="username"
          placeholder="your_username"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          autocomplete="username" autofocus>
      </div>

      <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-1" style="min-height:20px">
          <label class="field-label mb-0" for="password">Password</label>
          <a class="forgot" href="forgot.php">Forgot password?</a>
        </div>
        <input
          class="field-input<?= $error ? ' is-invalid' : '' ?>"
          type="password" id="password" name="password"
          placeholder="••••••••"
          autocomplete="current-password">
      </div>

      <button class="btn-submit" type="submit">Sign in</button>

    </form>

    <div class="divider my-4">or</div>

    <div class="register-row">
      Don't have an account? <a href="register.php">Create one</a>
    </div>

  </div>
</div>

<!-- FOOTER -->
<footer>
  <a href="wiki.php">← Back to wiki</a>
  &nbsp;·&nbsp;
  <a href="#">Privacy</a>
  &nbsp;·&nbsp;
  <a href="#">Terms</a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>