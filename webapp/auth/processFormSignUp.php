<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

require_once( "../../Lib/lib.php" );
require_once( "../../Lib/db.php" );
require_once( "../../Lib/wikiLib.php" );

$emailFilter = '/^[^@]+@[^@]+\.[^@]+$/';
$eliasFilter = '/^[A-Za-z0-9.]{2,}/';
$passFilter  = '/[A-Za-z0-9.\-#*,]{10,}/';

$flags[] = FILTER_NULL_ON_FAILURE;

$method = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_UNSAFE_RAW, $flags);

if ( $method=='POST') {
    $_INPUT_METHOD = INPUT_POST;
} elseif ( $method=='GET' ) {
    $_INPUT_METHOD = INPUT_GET;
}
else {
    echo "Invalid HTTP method (" . $method . ")";
    exit();
}

ini_set('display_errors', 'On');
error_reporting(E_ALL);
if ( !isset( $_SESSION ) ) {
    session_start();
}

header( 'Content-Type: text/html; charset=utf-8' );


$name = webAppName();
$serverName = filter_input( INPUT_SERVER, 'SERVER_NAME', FILTER_UNSAFE_RAW, $flags);
$serverPort = 80;
$baseUrl = "http://" . $serverName . ":" . $serverPort;
$baseNextUrl = $baseUrl . $name;

$guessCaptcha= filter_input( $_INPUT_METHOD, 'captcha', FILTER_UNSAFE_RAW, $flags);

if ($_SESSION['captcha'] != $guessCaptcha) {
    echo "<h1>Error - Code is incorrect</h1>";
    $nextUrl = "formSignUp.php";
    $linkargs = "?error=badCode";
}
else {
    echo "<h1>Ok - Code is correct</h1>";
    
    $flags[] = FILTER_NULL_ON_FAILURE;

    $username = filter_input( $_INPUT_METHOD, 'username', FILTER_UNSAFE_RAW, $flags);
    $password = filter_input( $_INPUT_METHOD, 'password', FILTER_UNSAFE_RAW, $flags);
    $password2 = filter_input( $_INPUT_METHOD, 'password_2', FILTER_UNSAFE_RAW, $flags);
    $email = filter_input( $_INPUT_METHOD, 'email', FILTER_UNSAFE_RAW, $flags);

    $userExists = existUserField("name", $username, "basic");
    $emailExists = existUserField("email", $email, "basic");
    if (!preg_match($eliasFilter, $username) || !preg_match($emailFilter, $email) || !preg_match($passFilter, $password)) {
        echo "<h1>Error - Invalid format</h1>";
        $nextUrl = "formSignUp.php";
        $linkargs = "?error=invalid";
    }
    else if ( $userExists ) {
        echo "<h1>Error - Account name already exists</h1>";
        $nextUrl = "formSignUp.php";
        $linkargs = "?error=badName";
    } elseif ($emailExists)  {
        echo "<h1>Error - Account e-mail already exists</h1>";
        $nextUrl = "formSignUp.php";
        $linkargs = "?error=badEmail";
    } elseif($password !== $password2){
        echo "<h1>Error - Wrong password repeat</h1>";
        $nextUrl = "formSignUp.php";
        $linkargs = "?error=badRepeat";
    } 
    else {
        $nextUrl = "emailVerification.php";
        $linkargs = "";
        
        //inserir na base de dados
        dbConnect(ConfigFile);
        $dataBaseName = $GLOBALS['configDataBase']->db;
        mysqli_select_db($GLOBALS['ligacao'], $dataBaseName );
        
        $sql = "INSERT INTO `$dataBaseName`.`auth-basic` (name, password, email, active) VALUES (?, ?, ?, 0)";
        $stmt = mysqli_prepare($GLOBALS['ligacao'], $sql);
        $userOk = -1;
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $username, $password, $email);

            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                $userOk = mysqli_insert_id($GLOBALS['ligacao']);
                $_SESSION['userId'] = $userOk;
                $_SESSION['tempEmail'] = $email;
                $_SESSION['tempUsername'] = $username;

            }

            mysqli_stmt_close($stmt);
        }
        
        if($userOk !== -1){
            $sql_challenge = "INSERT INTO `$dataBaseName`.`auth-challenge` (idUser, challenge) VALUES (?, ?)";

            $stmt_challenge = mysqli_prepare($GLOBALS['ligacao'], $sql_challenge);
            if ($stmt_challenge) {
                mysqli_stmt_bind_param($stmt_challenge, "is", $userOk, $_SESSION['challenge']);

                $result = mysqli_stmt_execute($stmt_challenge);

                if ($result) {
                    $userOk = mysqli_insert_id($GLOBALS['ligacao']);
                }

                mysqli_stmt_close($stmt_challenge);
            }
        }
        
        //mysqli_free_result($result);
        dbDisconnect();
    }
}

header( "Location: " . $baseNextUrl . $nextUrl . $linkargs);

?>