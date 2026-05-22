<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once("../../Lib/lib.php");
include_once("../../Lib/db.php");
include_once("../../Lib/extendedParsedown.php");
$method = $_SERVER['REQUEST_METHOD'];

if ( !isset( $_SESSION ) ) {
    session_start();
}

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

$title     = filter_input($_INPUT_METHOD, 'pageTitle', FILTER_UNSAFE_RAW);
$secondary = filter_input($_INPUT_METHOD, 'secondaryCategory', FILTER_UNSAFE_RAW);
$primary   = filter_input($_INPUT_METHOD, 'primaryCategory', FILTER_UNSAFE_RAW);
$name = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$userId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null; // Ensure you store idUser in session on login

// Handle form subscription toggle processing actions 
if ($method == 'POST' && $userId !== null) {
    if (isset($_POST['toggle_page_sub']) && !empty($title)) {
        togglePageSubscription($userId, $title);
        header("Location: ?pageTitle=" . urlencode($title));
        exit();
    }
    if (isset($_POST['toggle_cat_sub']) && !empty($primary) && !empty($secondary)) {
        toggleCategorySubscription($userId, $primary, $secondary);
        header("Location: ?primaryCategory=" . urlencode($primary) . "&secondaryCategory=" . urlencode($secondary));
        exit();
    }
}

if (!empty($title)): 
$content = readWikiPage($title);
$meta = getPageMetaData($title);
$Parsedown = new ExtendedParsedown();
if (!authorizeUserByNumericLevel($name, $meta['visibility'])):
    header("Location: ?");
    exit();
endif;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wiki Browser</title>    
</head>
<body>

    <?php if ($meta): ?>
        <a href="?primaryCategory=<?php echo $meta['primaryCategory']; ?>&secondaryCategory=<?php echo $meta['secondaryCategory']; ?>">← Back to <?php echo $meta['secondaryCategory']; ?></a>
    <?php else: ?>
        <a href="?">← Back Home</a>
    <?php endif; ?>

    <?php if (isset($name) && authorizeUserByLevel($name, 'user')): ?>
        <br>
        <a href="editPage.php?pageTitle=<?php echo $title ?>"> Edit Page</a>
        
        <!-- Page Subscription Component -->
        <form method="POST" style="display:inline; margin-left:15px;">
            <input type="hidden" name="pageTitle" value="<?php echo htmlspecialchars($title); ?>">
            <button type="submit" name="toggle_page_sub">
                <?php echo isSubscribedToPage($userId, $title) ? "Unsubscribe from Page" : "Subscribe to Page Updates"; ?>
            </button>
        </form>
    <?php endif; ?>

    <article>
        <?php echo $Parsedown->text($content); ?>
    </article>

    <?php 
    elseif (!empty($primary) && !empty($secondary)): 
        $pages = getPagesList($primary, $secondary);
    ?>
        <a href="?primaryCategory=<?php echo $primary; ?>">← Back to Categories</a>
        
        <div>
            <h2>Pages in "<?php echo $secondary; ?>"</h2>
            <?php if ($userId !== null): ?>
                <form method="POST">
                    <input type="hidden" name="primaryCategory" value="<?php echo htmlspecialchars($primary); ?>">
                    <input type="hidden" name="secondaryCategory" value="<?php echo htmlspecialchars($secondary); ?>">
                    <button type="submit" name="toggle_cat_sub">
                        <?php echo isSubscribedToCategory($userId, $primary, $secondary) ? "Unsubscribe from Category" : "Subscribe to Category Additions"; ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <?php if (empty($pages)): ?>
            <p>No pages found in this category.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($pages as $page): ?>
                    <?php 
                    $requiredLevel = (int)$page['visibility'];
                    if (authorizeUserByNumericLevel($name, $requiredLevel)): 
                    ?>
                        <li>
                            <a href="?pageTitle=<?php echo urlencode($page['pageTitle']); ?>">
                                <?php echo htmlspecialchars($page['pageTitle']); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    <?php 
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
                        <a href="?primaryCategory=<?php echo $primary; ?>&secondaryCategory=<?php echo $secCat['secondaryCategory']; ?>">
                            <?php echo $secCat['secondaryCategory']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    <?php 
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