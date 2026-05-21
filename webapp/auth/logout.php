<?php 

if ( !isset( $_SESSION ) ) {
    session_start();
}   
$_SESSION['id'] = null;
$_SESSION['username'] = null;
header("Location: ../index.php");
?>