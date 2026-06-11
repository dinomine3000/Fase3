<?php
// Display errors for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once( "../../Lib/db.php" );
require_once( "../../Lib/wikiLib.php" );


if (!isset($_SESSION)) {
    session_start();
}
$username = $_SESSION['username'] ?? '';

$isAdmin     = authorizeUserByLevel($username, 'admin');

if (!$isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    die("Access Denied: You do not have permissions to access this location.");
}


$flags[] = FILTER_NULL_ON_FAILURE;
$message = '';

$emailFilter = '/^[^@]+@[^@]+\.[^@]+$/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_UNSAFE_RAW, $flags);

    if ($action === 'update_email') {
        $email = filter_input(INPUT_POST, 'email_address', FILTER_VALIDATE_EMAIL, $flags);
        $password = filter_input(INPUT_POST, 'email_password', FILTER_UNSAFE_RAW, $flags);
        $displayName = filter_input(INPUT_POST, 'email_display_name', FILTER_UNSAFE_RAW, $flags);
        
        if (isset($email) && $password && $displayName) {
            if(!preg_match($emailFilter, $email)){
                $message = "Error: Invalid e-mail format.";
            }
            else if (setEmailConfig($email, $password, $displayName)) {
                $message = "Email configuration updated successfully in database.";
            } else {
                $message = "Error: Failed to save email configurations to the database.";
            }
        } else {
            $message = "Error: Invalid email configuration inputs.";
        }
    } 
    
    elseif ($action === 'update_image') {
        $maxSize = filter_input(INPUT_POST, 'max_size', FILTER_UNSAFE_RAW, $flags);
        $destination = filter_input(INPUT_POST, 'image_destination', FILTER_UNSAFE_RAW, $flags);
        $thumbType = filter_input(INPUT_POST, 'thumb_type', FILTER_UNSAFE_RAW, $flags);
        $thumbWidth = filter_input(INPUT_POST, 'thumb_width', FILTER_VALIDATE_INT, $flags);
        $thumbHeight = filter_input(INPUT_POST, 'thumb_height', FILTER_VALIDATE_INT, $flags);

        $allowedTypes = ['jpg', 'png', 'gif', 'webp'];

        if ($destination && in_array($thumbType, $allowedTypes) && $thumbWidth > 0 && $thumbHeight > 0) {
            $formattedDestination = rtrim($destination, '/') . '/';
            
            if (setImageConfig($formattedDestination, $thumbType, $thumbWidth, $thumbHeight, $maxSize)) {
                $message = "Image configuration updated successfully in database.";
            } else {
                $message = "Error: Failed to save image configurations to the database.";
            }
        } else {
            $message = "Error: Invalid image configuration inputs. Verify dimensions are numbers and image type is supported.";
        }
    }
}

// Fetch current values from database for form presentation
$emailConfig = getEmailConfig();
$imageConfig = getImageConfig();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Configuration Portal</title>
</head>
<body>
    <a href="../index.php">← Back to Home</a>
    <h1>System Configuration Portal</h1>

    <?php if (!empty($message)): ?>
        <p><strong>Notice: <?php echo htmlspecialchars($message); ?></strong></p>
        <hr>
    <?php endif; ?>

    <section>
        <h2>Server Email Configuration</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_email">
            
            <p>
                <label for="email_address">Sender Email Address:</label><br>
                <input type="email" id="email_address" required pattern="^[^@]+@[^@]+\.[^@]+$" name="email_address" value="<?php echo htmlspecialchars($emailConfig['email'] ?? ''); ?>" required size="40">
            </p>
            
            <p>
                <label for="email_password">SMTP Password:</label><br>
                <input type="password" id="email_password" name="email_password" value="<?php echo htmlspecialchars($emailConfig['password'] ?? ''); ?>" required size="40">
            </p>
            
            <p>
                <label for="email_display_name">Display Name (Sender Title):</label><br>
                <input type="text" id="email_display_name" name="email_display_name" value="<?php echo htmlspecialchars($emailConfig['displayName'] ?? ''); ?>" required size="40">
            </p>
            
            <button type="submit">Save Email Settings</button>
        </form>
    </section>

    <hr>

    <section>
        <h2>Image & Thumbnail Configuration</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_image">
            
            <p>
                <label for="image_destination">Image Destination Folder Path:</label><br>
                <input type="text" id="image_destination" name="image_destination" value="<?php echo htmlspecialchars($imageConfig['destination'] ?? ''); ?>" required size="40">
            </p>
            
            <p>
                <label for="thumb_type">Thumbnail Image Format Type:</label><br>
                <select id="thumb_type" name="thumb_type">
                    <option value="png" <?php echo (isset($imageConfig['thumbType']) && $imageConfig['thumbType'] === 'png') ? 'selected' : ''; ?>>PNG</option>
                    <option value="jpg" <?php echo (isset($imageConfig['thumbType']) && $imageConfig['thumbType'] === 'jpg') ? 'selected' : ''; ?>>JPG</option>
                    <option value="gif" <?php echo (isset($imageConfig['thumbType']) && $imageConfig['thumbType'] === 'gif') ? 'selected' : ''; ?>>GIF</option>
                    <option value="webp" <?php echo (isset($imageConfig['thumbType']) && $imageConfig['thumbType'] === 'webp') ? 'selected' : ''; ?>>WEBP</option>
                </select>
            </p>
            
            <p>
                <label for="thumb_width">Thumbnail Max Width (Pixels):</label><br>
                <input type="number" id="thumb_width" name="thumb_width" value="<?php echo (int)($imageConfig['thumbWidth'] ?? 0); ?>" min="1" required>
            </p>
            
            
            <p>
                <label for="max_size">Max File Size (bytes): </label><br>
                <input type="number" id="max_size" name="max_size" value="<?php echo (int)($imageConfig['maxFileSize'] ?? 0); ?>" min="1" required>
            </p>
            
            <p>
                <label for="thumb_height">Thumbnail Max Height (Pixels):</label><br>
                <input type="number" id="thumb_height" name="thumb_height" value="<?php echo (int)($imageConfig['thumbHeight'] ?? 0); ?>" min="1" required>
            </p>
            
            <button type="submit">Save Image Settings</button>
        </form>
    </section>

</body>
</html>