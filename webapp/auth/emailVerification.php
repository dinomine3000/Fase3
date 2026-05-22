<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

require_once( "../../Lib/lib.php" );
require_once( "../../Lib/db.php" );
require_once( "../../Lib/lib-mail-v2.php" );

ini_set('display_errors', 'On');
error_reporting(E_ALL);
if ( !isset( $_SESSION ) ) {
    session_start();
}

header( 'Content-Type: text/html; charset=utf-8' );

//getting the email account settings
dbConnect( ConfigFile );
$dataBaseName = $GLOBALS['configDataBase']->db;
mysqli_select_db( $GLOBALS['ligacao'], $dataBaseName );

//definir aqui o ID da conta de email
$emailId = 4;
$queryString = "SELECT * FROM `$dataBaseName`.`email-accounts` WHERE `id`=$emailId";
$queryResult = mysqli_query( $GLOBALS['ligacao'], $queryString );
$record = mysqli_fetch_array( $queryResult );

$smtpServer = $record[ 'smtpServer' ];
$port = intval( $record[ 'port' ] );
$useSSL = boolval( $record[ 'useSSL' ] );
$timeout = intval( $record[ 'timeout' ] );
$loginName = $record[ 'loginName' ];
$password = $record[ 'password' ];
$fromEmail = $record[ 'email' ];
$fromName = $record[ 'displayName' ];

mysqli_free_result( $queryResult );
dbDisconnect();

$flags[] = FILTER_NULL_ON_FAILURE;
$serverName = filter_input( INPUT_SERVER, 'SERVER_NAME', FILTER_UNSAFE_RAW, $flags);
$serverPort = 80;
$name = webAppName();
$baseUrl = "http://" . $serverName . ":" . $serverPort;
$link = $baseUrl . $name . "emailLink.php?verificationCode=" . $_SESSION['challenge'] . "&userId=" . $_SESSION['userId'];

$ToName = $_SESSION['tempUsername'];
$ToEmail = $_SESSION['tempEmail'];
$Subject = "Account creation e-mail validation";
$Message = "Click or copy the link below to validate your account creation:\n" . $link;

//$result = true;
$result = sendAuthEmail(
    $smtpServer,
    $useSSL,
    $port,
    $timeout,
    $loginName,
    $password,
    $fromEmail,
    $fromName,
    $ToName . " <" . $ToEmail . ">",
    NULL,
    NULL,
    $Subject,
    $Message,
    false,  // set to true see debug messages
    NULL );

?>


<html>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
        <title>Menu body</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <link rel="stylesheet" type="text/css" href="../../../Styles/GlobalStyle.css">
        
        <script type="text/javascript" src="forms.js">
        </script>
    </head>
    <body>
        <h1> E-mail validation </h1>
        <?php if($result == true) echo "<p> Check your e-mail ($ToEmail)for a validation link to create the account </p>";
        else echo "E-Mail failed to be sent."?>
        
    </body>
</html>