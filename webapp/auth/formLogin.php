<!DOCTYPE html>
<?php
    require_once( "../../Lib/lib.php" );
    
    $flags[] = FILTER_NULL_ON_FAILURE;
    
    $serverName = filter_input( INPUT_SERVER, 'SERVER_NAME', FILTER_SANITIZE_STRING, $flags);

    $serverPortSSL = 443;
    $serverPort = 80;

    $name = webAppName();

    //$nextUrl = "https://" . $serverName . ":" . $serverPortSSL . $name . "processFormLogin.php";
    //#$nextUrl = "http://" . $serverName . ":" . $serverPort . $name . "processFormLogin.php";
?>
<html>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
        <title>Authentication Using PHP</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <link rel="stylesheet" type="text/css" href="../../Styles/GlobalStyle.css">
    </head>
    
    <body>
        <!--<form action="<?php echo $nextUrl ?>" method="POST">-->
        <form action="processFormLogin.php" method="POST">
            <table>
                <tr>
                    <td>User Name</td>
                    <td><input type="text" name="username" placeholder="Type your name"></td>
                </tr>
                <div>
                <?php

                $method = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_UNSAFE_RAW, $flags);

                if ( $method=='POST') {
                    $_INPUT_METHOD = INPUT_POST;
                } elseif ( $method=='GET' ) {
                    $_INPUT_METHOD = INPUT_GET;
                }
                $flags[] = FILTER_NULL_ON_FAILURE;

                $message = filter_input( $_INPUT_METHOD, 'message', FILTER_UNSAFE_RAW, $flags);
                if(isset($message) && $message !== ""){
                    if($message == "success"){
                        echo "<p> E-mail validated successfully</p>";
                    } elseif($message === "failed"){
                        echo "<p> E-mail validation failed.</p>";
                    } else {
                        echo "<p> Error validating: $message</p>";
                    }
                } 
                ?>
                </div>
                <tr>
                    <td>Password</td>
                    <td><input type="password" name="password" placeholder="Type your password"></td>
                </tr>
            </table>

            <input type="submit" value="Login"> <input type="reset" value="Clear">
        </form>
        <a href="formSignUp.php">Sign-Up</a>
    </body>
</html>
