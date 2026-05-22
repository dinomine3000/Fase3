<?php
require_once( "../../Lib/lib.php" );
require_once( "../../Lib/db.php" );

if ( !isset( $_SESSION ) ) {
    session_start();
}
$username = $_SESSION['username'] ?? '';

$isAdmin = authorizeUserByLevel($username, 'admin');
$isOrganizer = authorizeUserByLevel($username, 'organizer');

// Block anyone below an organizer
if (!$isOrganizer) {
    header('HTTP/1.1 403 Forbidden');
    die("Access Denied: You do not have permissions to manage categories.");
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_primary']) && $isAdmin) {
        // Primary Category Generation (Admin Only)
        $name = $_POST['primary_name'] ?? '';
        if (createPrimaryCategory($name)) {
            $message = "Primary category '" . htmlspecialchars($name) . "' created successfully.";
        } else {
            $error = "Failed to create primary category. It may already exist.";
        }
    } 
    elseif (isset($_POST['create_secondary'])) {
        // Secondary Category Generation (Organizer or Higher)
        $primary = $_POST['parent_primary'] ?? '';
        $secondary = $_POST['secondary_name'] ?? '';
        if (createSecondaryCategory($primary, $secondary)) {
            $message = "Secondary category '" . htmlspecialchars($secondary) . "' successfully added to '" . htmlspecialchars($primary) . "'.";
        } else {
            $error = "Failed to create secondary category. Duplicate binding detected.";
        }
    }
}

// Using your existing framework function instead of a custom one
$primaryCategoriesData = getCategoryList('primary');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Structural Categories</title>
</head>
<body>

    <a href="#" onclick="history.back()">← Return to Previous Page</a>
    <h2>Category Architecture Dashboard</h2>

    <?php if (!empty($message)): ?>
        <div><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="status status-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- admin can create primary -->
    <?php if ($isAdmin): ?>
        <div>
            <h3>Create New Primary Category</h3>
            <form method="POST">
                <div>
                    <label for="primary_name">Category Name</label>
                    <input type="text" id="primary_name" name="primary_name" required pattern="[A-Za-z0-9_\-]+">
                </div>
                <button type="submit" name="create_primary">Create Primary</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- SECONDARY CATEGORY CREATION SECTION (ORGANIZER + ADMIN) -->
    <div>
        <h3>Create New Secondary Category</h3>
        <form method="POST">
            <div>
                <label for="parent_primary">Parent Primary Category</label>
                <select id="parent_primary" name="parent_primary" required>
                    <option value="">-- Select Parent Category --</option>
                    <?php foreach ($primaryCategoriesData as $catRow): 
                        $catName = $catRow['primaryCategory'];
                    ?>
                        <option value="<?php echo htmlspecialchars($catName); ?>"><?php echo htmlspecialchars($catName); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="secondary_name">Secondary Sub-Category Name</label>
                <input type="text" id="secondary_name" name="secondary_name" required pattern="[A-Za-z0-9_\-]+">
            </div>
            <button type="submit" name="create_secondary">Create Secondary</button>
        </form>
    </div>

</body>
</html>