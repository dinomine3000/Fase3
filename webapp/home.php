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
    if(isset($name) && authorizeUserByLevel($name, "organizer")):
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

</body>
</html>