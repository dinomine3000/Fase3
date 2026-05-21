<?php 
include_once("../../Lib/lib.php");

if ( !isset( $_SESSION ) ) {
    session_start();
}
if(!isset($_SESSION['username']) || !authorizeUserByLevel($_SESSION['username'], "organizer")){
header("Location: ../index.php");    
exit();
}
$primaryCategories = getCategoryList('primary');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Wiki Page</title>    
    <!-- Link the external JavaScript file -->
    <script src="../scripts/category.js" defer></script>
</head>
<body>

        <!--
        Source - https://stackoverflow.com/a/8814534
        Posted by Bajrang, modified by community. See post 'Timeline' for change history
        Retrieved 2026-05-21, License - CC BY-SA 3.0
        -->

        <a href="javascript:history.back()">Go Back</a>
    <h2>Create a New Wiki Page</h2>

    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form action="processCreatePage.php" method="POST">
        <div class="form-group">
            <label for="pageTitle">Page Title</label>
            <input type="text" id="pageTitle" name="pageTitle" placeholder="e.g., How to install PHP" required>
        </div>

        <div class="form-group">
            <label for="primaryCategory">Primary Category</label>
            <select id="primaryCategory" name="primaryCategory" onchange="updateSecondaryCategories()" required>
                <option value="">-- Select Primary Category --</option>
                <?php foreach ($primaryCategories as $category): ?>
                    <!-- Fixed array key access -->
                    <option value="<?php echo htmlspecialchars($category['primaryCategory']); ?>">
                        <?php echo htmlspecialchars($category['primaryCategory']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="secondaryCategory">Secondary Category</label>
            <select id="secondaryCategory" name="secondaryCategory" required>
                <option value="">-- Select Secondary Category --</option>
            </select>
        </div>

        <div class="form-group">
            <label for="content">Page Content (Markdown Syntax)</label>
            <textarea id="content" name="content" rows="15" placeholder="Use # for headers, **bold** for text, etc..." required></textarea>
        </div>

        <button type="submit">Publish Wiki Page</button>
    </form>

</body>
</html>
<?php 
// Fixed connection variable to match $GLOBALS['ligacao'] used by library
if (isset($GLOBALS['ligacao'])) {
    mysqli_close($GLOBALS['ligacao']); 
}
?>