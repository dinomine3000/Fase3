<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");
require_once("../../Lib/wikiLib.php");

if (!isset($_SESSION)) {
    session_start();
}
$username = $_SESSION['username'] ?? '';
$isAdmin  = authorizeUserByLevel($username, 'admin');

if (!$isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    die("Access Denied: You do not have permissions to access this location.");
}

$flags   = [FILTER_NULL_ON_FAILURE];
$message = '';
$isError = false;

$emailFilter = '/^[^@]+@[^@]+\.[^@]+$/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_UNSAFE_RAW, $flags);

    if ($action === 'update_email') {
        $email       = filter_input(INPUT_POST, 'email_address', FILTER_VALIDATE_EMAIL, $flags);
        $password    = filter_input(INPUT_POST, 'email_password', FILTER_UNSAFE_RAW, $flags);
        $displayName = filter_input(INPUT_POST, 'email_display_name', FILTER_UNSAFE_RAW, $flags);

        if (isset($email) && $password && $displayName) {
            if (!preg_match($emailFilter, $email)) {
                $message = "Invalid e-mail format."; $isError = true;
            } elseif (setEmailConfig($email, $password, $displayName)) {
                $message = "Email configuration updated successfully.";
            } else {
                $message = "Failed to save email configuration to the database."; $isError = true;
            }
        } else {
            $message = "Invalid email configuration inputs."; $isError = true;
        }
    } elseif ($action === 'update_image') {
        $maxSize     = filter_input(INPUT_POST, 'max_size', FILTER_UNSAFE_RAW, $flags);
        $destination = filter_input(INPUT_POST, 'image_destination', FILTER_UNSAFE_RAW, $flags);
        $thumbType   = filter_input(INPUT_POST, 'thumb_type', FILTER_UNSAFE_RAW, $flags);
        $thumbWidth  = filter_input(INPUT_POST, 'thumb_width', FILTER_VALIDATE_INT, $flags);
        $thumbHeight = filter_input(INPUT_POST, 'thumb_height', FILTER_VALIDATE_INT, $flags);

        $allowedTypes = ['jpg', 'png', 'gif', 'webp'];

        if ($destination && in_array($thumbType, $allowedTypes) && $thumbWidth > 0 && $thumbHeight > 0) {
            $formattedDestination = rtrim($destination, '/') . '/';
            if (setImageConfig($formattedDestination, $thumbType, $thumbWidth, $thumbHeight, $maxSize)) {
                $message = "Image configuration updated successfully.";
            } else {
                $message = "Failed to save image configuration to the database."; $isError = true;
            }
        } else {
            $message = "Invalid image configuration inputs. Verify dimensions are numbers and image type is supported."; $isError = true;
        }
    }
}

$emailConfig = getEmailConfig();
$imageConfig = getImageConfig();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Configuration — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css?v=3">
<link rel="stylesheet" href="styles/form.css?v=3">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<?php include('./header.php'); ?>

<div class="container-lg py-4">
  <div class="section-heading">System Configuration</div>

  <?php if (!empty($message)): ?>
  <div class="status-banner <?php echo $isError ? 'error' : 'success'; ?>" style="margin-bottom:1.5rem">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <?php if ($isError): ?>
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
      <?php else: ?>
        <polyline points="20 6 9 17 4 12"/>
      <?php endif; ?>
    </svg>
    <?php echo htmlspecialchars($message); ?>
  </div>
  <?php endif; ?>

  <div class="d-flex flex-column gap-3">

    <!-- Email config -->
    <div class="form-card">
      <span class="role-label">Admin only</span>
      <div class="form-title">Server Email Configuration</div>
      <form method="POST" action="">
        <input type="hidden" name="action" value="update_email">

        <div class="form-group">
          <label class="form-label" for="email_address">Sender Email Address</label>
          <input class="form-input" type="email" id="email_address" name="email_address"
                 placeholder="noreply@example.com"
                 pattern="^[^@]+@[^@]+\.[^@]+$" required
                 value="<?php echo htmlspecialchars($emailConfig['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="email_password">SMTP Password</label>
          <input class="form-input" type="password" id="email_password" name="email_password"
                 placeholder="••••••••" required
                 value="<?php echo htmlspecialchars($emailConfig['password'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="email_display_name">Display Name</label>
          <input class="form-input" type="text" id="email_display_name" name="email_display_name"
                 placeholder="Smiki Notifications" required
                 value="<?php echo htmlspecialchars($emailConfig['displayName'] ?? ''); ?>">
        </div>

        <div class="form-actions">
          <button type="submit" class="hbtn primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Email Settings
          </button>
        </div>
      </form>
    </div>

    <!-- Image config -->
    <div class="form-card">
      <span class="role-label">Admin only</span>
      <div class="form-title">Image &amp; Thumbnail Configuration</div>
      <form method="POST" action="">
        <input type="hidden" name="action" value="update_image">

        <div class="form-group">
          <label class="form-label" for="image_destination">Image Destination Folder Path</label>
          <input class="form-input" type="text" id="image_destination" name="image_destination"
                 placeholder="/var/www/uploads/" required
                 value="<?php echo htmlspecialchars($imageConfig['destination'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="thumb_type">Thumbnail Format</label>
          <select class="form-select" id="thumb_type" name="thumb_type">
            <?php foreach (['png' => 'PNG', 'jpg' => 'JPG', 'gif' => 'GIF', 'webp' => 'WEBP'] as $val => $label): ?>
            <option value="<?php echo $val; ?>" <?php echo (($imageConfig['thumbType'] ?? '') === $val) ? 'selected' : ''; ?>>
              <?php echo $label; ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="thumb_width">Thumbnail Max Width (px)</label>
            <input class="form-input" type="number" id="thumb_width" name="thumb_width" min="1" required
                   value="<?php echo (int)($imageConfig['thumbWidth'] ?? 0); ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="thumb_height">Thumbnail Max Height (px)</label>
            <input class="form-input" type="number" id="thumb_height" name="thumb_height" min="1" required
                   value="<?php echo (int)($imageConfig['thumbHeight'] ?? 0); ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="max_size">Max File Size (bytes)</label>
          <input class="form-input" type="number" id="max_size" name="max_size" min="1" required
                 value="<?php echo (int)($imageConfig['maxFileSize'] ?? 0); ?>">
        </div>

        <div class="form-actions">
          <button type="submit" class="hbtn primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Image Settings
          </button>
        </div>
      </form>
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
<script src="../scripts/ajaxHandler.js?v=3"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
