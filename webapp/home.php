<?php
include_once("../Lib/lib.php");
    //Display dos erros
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL); 

if ( !isset( $_SESSION ) ) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wiki Home</title>
</head>
<body>

    <h1>Wiki System Home</h1>
    <?php 
    if($isLoggedIn){
        $name = $_SESSION['username'];
        echo "<h2>Welcome $name</h2>";
        $role = getUserRoleFriendlyName($name);
        echo "<p>Your role is $role</p>";
    }
    ?>


    <?php 
    if(isset($name) && authorizeUserByLevel($name, "user")):
    ?>
    <form action="files/list.php" method="GET">
        <button type="submit">View Files</button>
    </form>
    <br>
    <?php endif; ?>

    <?php 
    if(isset($name) && authorizeUserByLevel($name, 'organizer')):
    ?>
    <form action="wiki/manage_categories.php" method="GET">
        <button type="submit">Create Categories</button>
    </form>
    <br>
    <?php endif; ?>

    <?php 
    if(isset($name) && authorizeUserByLevel($name, "editor")):
    ?>
    <form action="wiki/create.php" method="GET">
        <button type="submit">Create a Page</button>
    </form>
    <br>
    <?php endif; ?>

    <form action="wiki/viewPage.php" method="GET">
        <button type="submit">View Categories</button>
    </form>
    <br>
    <form action="foruns/forum.php" method="GET">
        <button type="submit">Forum</button>
    </form>
    <br>

    <?php if ($isLoggedIn): ?>
        <form action="auth/logout.php" method="POST">
            <button type="submit">Logout</button>
        </form>
    <?php else: ?>
    <form action="auth/formLogin.php" method="GET">
        <button type="submit">Login</button>
    </form>
    <?php endif; ?>

    <?php 
    if (authorizeUserByLevel($name, 'organizer')): 
        $pendingCount = getPendingProposalsCount();
    ?>
        <div>
            <h3>Page Proposals Management</h3>
            <p>There are currently <strong><?php echo $pendingCount; ?></strong> changes waiting to be managed.</p>
            <a href="wiki/proposals.php">View Moderation Queue</a>
        </div>
    <?php endif; ?>
</body>
</html>