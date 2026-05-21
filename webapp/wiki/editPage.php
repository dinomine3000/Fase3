<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once("../../Lib/lib.php");

if ( !isset( $_SESSION ) ) {
    session_start();
}

$name = $_SESSION['username'];
// Restrict access to authenticated users with 'user' level or higher
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

// Fallback checking if the page doesn't exist in the database
if (!isset($content)) {
    echo "Error: The requested page does not exist.";
    exit();
}

// Check privilege tier beforehand to dynamically customize UI notifications
$willAutoPublish = authorizeUserByLevel($name, 'editor');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Page: <?php echo htmlspecialchars($title); ?></title> 
</head>
<body>

    <a href="viewPage.php?pageTitle=<?php echo urlencode($title); ?>">← Cancel and Go Back</a>
    <h2>Edit Page: <?php echo htmlspecialchars($title); ?></h2>

    <div style="background-color: #f0f7ff; border-left: 4px solid #0066cc; padding: 10px; margin-bottom: 20px;">
        <strong>Submission Notice:</strong> 
        <?php if ($willAutoPublish): ?>
            Your account status allows changes to take effect **immediately** upon saving.
        <?php else: ?>
            Your modifications will be **held in a moderation queue** for manual administrative approval before going live, unless your overall contribution score exceeds 3.
        <?php endif; ?>
    </div>

    <form action="processEditPage.php" method="POST">
        <input type="hidden" name="pageTitle" value="<?php echo htmlspecialchars($title); ?>">

        <div class="form-group" style="margin-bottom: 15px;">
            <label for="content" style="display:block; font-weight:bold; margin-bottom:5px;">Page Content (Markdown Syntax)</label>
            <textarea id="content" name="content" rows="20" style="width: 100%; font-family: monospace;" required><?php echo htmlspecialchars($content); ?></textarea>
        </div>

        <button type="submit">Save Changes</button>
    </form>

</body>
</html>