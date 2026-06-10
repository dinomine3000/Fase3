<?php
require_once( "../../Lib/lib.php" );
require_once( "../../Lib/db.php" );
require_once( "../../Lib/wikiLib.php" );

if (!isset($_SESSION)) {
    session_start();
}
$username = $_SESSION['username'] ?? '';

$isAdmin     = authorizeUserByLevel($username, 'admin');
$isOrganizer = authorizeUserByLevel($username, 'organizer');

if (!$isOrganizer) {
    header('HTTP/1.1 403 Forbidden');
    die("Access Denied: You do not have permissions to manage categories.");
}

$message = '';
$error   = '';
$catNameTest = "/[A-Za-z0-9_\-]+/";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isOrganizer) {
    if (isset($_POST['create_primary'])) {
        $name = $_POST['primary_name'] ?? '';
        if (!preg_match($catNameTest, $name)) {
            $error = "Category name does not match the format requested.";
        } else if (createPrimaryCategory($name)) {
            $message = "Primary category '" . htmlspecialchars($name) . "' created successfully.";
        } else {
            $error = "Failed to create primary category. It may already exist.";
        }
    } elseif (isset($_POST['create_secondary'])) {
        $primary   = $_POST['parent_primary'] ?? '';
        $secondary = $_POST['secondary_name'] ?? '';
        if (!preg_match($catNameTest, $secondary)) {
            $error = "Category name does not match format requested.";
        } else if (createSecondaryCategory($primary, $secondary)){
            $message = "Secondary category '" . htmlspecialchars($secondary) . "' added to '" . htmlspecialchars($primary) . "'.";
        } else {
            $error = "Failed to create secondary category. Duplicate binding detected.";
        }
    }
}

$primaryCategoriesData = getCategoryList('primary');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Categories</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css?v=3">
<link rel="stylesheet" href="styles/form.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<?php include('./header.php')?>

<div class="container-lg py-4">
  <div class="section-heading">Category Architecture</div>

  <?php if (!empty($message)): ?>
    <div class="status-banner success mb-3">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      <?php echo $message; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="status-banner error mb-3">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <div class="d-flex flex-column gap-3">

    <?php if ($isAdmin): ?>
    <div class="form-card">
      <span class="role-label">Admin only</span>
      <div class="form-title">Create Primary Category</div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label" for="primary_name">Category Name</label>
          <input class="form-input" type="text" id="primary_name" name="primary_name"
                 placeholder="e.g., Science" required pattern="[A-Za-z0-9_\-]+">
        </div>
        <div class="form-actions">
          <button type="submit" name="create_primary" class="hbtn primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create Primary
          </button>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <div class="form-card">
      <div class="form-title">Create Secondary Category</div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label" for="parent_primary">Parent Primary Category</label>
          <select class="form-select" id="parent_primary" name="parent_primary" required>
            <option value="">— Select parent category —</option>
            <?php foreach ($primaryCategoriesData as $catRow):
              $catName = $catRow['primaryCategory'];
            ?>
            <option value="<?php echo htmlspecialchars($catName); ?>"><?php echo htmlspecialchars($catName); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="secondary_name">Sub-Category Name</label>
          <input class="form-input" type="text" id="secondary_name" name="secondary_name"
                 placeholder="e.g., Quantum Mechanics" required pattern="[A-Za-z0-9_\-]+">
        </div>
        <div class="form-actions">
          <button type="submit" name="create_secondary" class="hbtn primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create Secondary
          </button>
          <button type="button" class="hbtn" onclick="history.back()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Go Back
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
