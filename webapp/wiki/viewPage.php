<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once("../../Lib/lib.php");
include_once("../../Lib/extendedParsedown.php");
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $_INPUT_METHOD = INPUT_POST;
    $_ARGS = $_POST;
} elseif ($method == 'GET') {
    $_INPUT_METHOD = INPUT_GET;
    $_ARGS = $_GET;
} else {
    echo "Invalid HTTP method (" . $method . ")";
    exit();
}

if ( !isset( $_SESSION ) ) {
    session_start();
}
$title     = filter_input($_INPUT_METHOD, 'pageTitle', FILTER_UNSAFE_RAW);
$secondary = filter_input($_INPUT_METHOD, 'secondaryCategory', FILTER_UNSAFE_RAW);
$primary   = filter_input($_INPUT_METHOD, 'primaryCategory', FILTER_UNSAFE_RAW);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wiki Browser</title>    
</head>
<body>

    <?php 
    // STEP 4: Render specific page content (?pageTitle=XYZ)
    if (!empty($title)): 
        $content = readWikiPage($title);
        $meta = getPageMetaData($title);
        $Parsedown = new ExtendedParsedown();
    ?>
    <!-- Back button extracts parent components from DB, keeping the current URL clean -->
        <?php if ($meta): ?>
            <a href="?primaryCategory=<?php echo $meta['primaryCategory']; ?>&secondaryCategory=<?php echo $meta['secondaryCategory']; ?>">← Back to <?php echo $meta['secondaryCategory']; ?></a>
        <?php else: ?>
            <a href="?">← Back Home</a>
        <?php endif; ?>
        <!-- edit button -->
        <?php if (isset($_SESSION['username']) && authorizeUserByLevel($_SESSION['username'], 'user')): ?>
            <br>
            <a href="editPage.php?pageTitle=<?php echo $title ?>"> Edit Page</a>
        <?php endif; ?>

        <article>
            <?php echo $Parsedown->text($content); ?>
        </article>

    <?php 
    // STEP 3: Render list of pages under a secondary category (Requires composite key elements)
    elseif (!empty($primary) && !empty($secondary)): 
        $pages = getPagesList($primary, $secondary);
    ?>
        <a href="?primaryCategory=<?php echo $primary; ?>">← Back to Categories</a>
        <h2>Pages in "<?php echo $secondary; ?>"</h2>
        
        <?php if (empty($pages)): ?>
            <p>No pages found in this category.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($pages as $pTitle): ?>
                    <li>
                        <a href="?pageTitle=<?php echo $pTitle; ?>">
                            <?php echo $pTitle; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    <?php 
    // STEP 2: Render list of secondary categories under chosen primary category
    elseif (!empty($primary)): 
        $secondaryCategories = getCategoryList('secondary', $primary);
    ?>
        <a href="?">← Back to Primary Categories</a>
        <h2>Secondary Categories in "<?php echo $primary; ?>"</h2>
        
        <?php if (empty($secondaryCategories)): ?>
            <p>No secondary categories found.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($secondaryCategories as $secCat): ?>
                    <li>
                        <!-- Includes primary scope context required by the secondary composite structure -->
                        <a href="?primaryCategory=<?php echo $primary; ?>&secondaryCategory=<?php echo $secCat['secondaryCategory']; ?>">
                            <?php echo $secCat['secondaryCategory']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    <?php 
    // STEP 1: Render fallback home list of primary categories
    else: 
        $primaryCategories = getCategoryList('primary');
    ?>
        <a href="../index.php">← Back to Home</a>
        <h2>Primary Categories</h2>
        <ul>
            <?php foreach ($primaryCategories as $priCat): ?>
                <li>
                    <a href="?primaryCategory=<?php echo $priCat['primaryCategory']; ?>">
                        <?php echo $priCat['primaryCategory']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

</body>
</html>