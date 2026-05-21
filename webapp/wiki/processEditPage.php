<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once("../../Lib/lib.php");

if ( !isset( $_SESSION ) ) {
    session_start();
}

// Security barrier protection check
if (!isset($_SESSION['username']) || !authorizeUserByLevel($_SESSION['username'], 'user')) {
    header("Location: ../index.php");    
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method.";
    exit();
}

$title      = filter_input(INPUT_POST, 'pageTitle', FILTER_UNSAFE_RAW);
$newContent = filter_input(INPUT_POST, 'content', FILTER_UNSAFE_RAW);

if (empty($title) || $newContent === null) {
    echo "Missing required page components.";
    exit();
}

// Call your custom dual-route storage function
$success = processPageChange($_SESSION['username'], $title, $newContent);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processing Changes</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">

    <?php if ($success): ?>
        <h2>Changes Successfully Processed!</h2>
        
        <?php 
        // Determine what happened during processPageChange to show precise confirmation text
        // Re-run checks mimicking the condition: ($isOrganizerOrHigher || $hasHighContributions)
        $isOrganizerOrHigher = authorizeUserByLevel($_SESSION['username'], 'organizer');
        
        // Fetch contribution metrics straight from DB to keep logic uniform
        dbConnect(ConfigFile);
        $dataBaseName = $GLOBALS['configDataBase']->db;
        $escapedUser = mysqli_real_escape_string($GLOBALS['ligacao'], $_SESSION['username']);
        $res = mysqli_query($GLOBALS['ligacao'], "SELECT `contributions` FROM `$dataBaseName`.`auth-basic` WHERE `name` = '$escapedUser' LIMIT 1");
        $contributions = 0;
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            $contributions = (int)$row['contributions'];
            mysqli_free_result($res);
        }
        dbDisconnect();
        ?>

        <?php if ($isOrganizerOrHigher || $contributions > 3): ?>
            <p style="color: green; font-weight: bold;">✓ Your changes have been updated directly to the production wiki page and are now live.</p>
        <?php else: ?>
            <p style="color: #b36b00; font-weight: bold;">⏱ Your changes were saved to the moderation queue. A staff member will review and authorize them shortly.</p>
        <?php endif; ?>

    <?php else: ?>
        <h2 style="color: red;">Database Transaction Failure</h2>
        <p>The system encountered an error while attempting to process and log your file changes.</p>
    <?php endif; ?>

    <p style="margin-top: 25px;">
        <a href="viewPage.php?pageTitle=<?php echo urlencode($title); ?>" style="display: inline-block; background: #333; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 3px;">Return to Wiki Page</a>
    </p>

</body>
</html>