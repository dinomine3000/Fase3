<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

require_once("../../Lib/db.php");
loadConfigurationDataBase(constant("ConfigFile"));

?>

<html>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
        <title>Menu body</title>
        
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <link rel="stylesheet" type="text/css" href="../../Styles/GlobalStyle.css">
        
        <script type="text/javascript" src="forms.js">
        </script>
    </head>
    <body>
        <h1>
            Sign-Up
        </h1>
        
        <form 
            action="processFormSignUp.php" 
            onsubmit="return FormSignupValidator(this)"
            name="FormSignup"
            method="POST">
            <table>
                <tr>
                    <td>User Name*</td>
                    <td><input required type="text" name="username" placeholder="Type your username"></td>
                </tr>
                <tr>
                    <td>E-Mail*</td>
                    <td><input required type="text" name="email" placeholder="Type your e-mail"></td>
                </tr>
                <tr>
                    <td>Password*</td>
                    <td><input required type="password" name="password" placeholder="Type your password"></td>
                </tr>
                <tr>
                    <td>Password Again*</td>
                    <td><input required type="password" name="password_2" placeholder="Type your password again"></td>
                </tr>
            </table>
            <div>
            <?php

            $method = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_UNSAFE_RAW, $flags);

            if ( $method=='POST') {
                $_INPUT_METHOD = INPUT_POST;
            } elseif ( $method=='GET' ) {
                $_INPUT_METHOD = INPUT_GET;
            }
            $flags[] = FILTER_NULL_ON_FAILURE;

            $error = filter_input( $_INPUT_METHOD, 'error', FILTER_UNSAFE_RAW, $flags);
            if(isset($error) && $error !== ""){
                if($error === "badCode"){
                    echo "<p> Wrong Captcha</p>";
                } elseif($error === "badName"){
                    echo "<p> Name already exists.</p>";
                } elseif($error === "badEmail"){
                    echo "<p> E-mail already exists</p>";
                } elseif($error === "badRepeat"){
                    echo "<p> Password repeat is wrong</p>";
                }
                else {
                    echo "<p> Bad field: $error</p>";
                }
            } 
            ?>
            </div>
            <div style="text-align: left;">
                <img src="../captcha/captchaImage.php"/><br>
                <label for="captcha">Captcha Code*</label><br>
                <input required type="text" name="captcha" id="captcha">
            </div>
            
            <p>Fields with * are required</p>
            <input type="submit" value="Sign-up"> <input type="reset" value="Clear">
        </form>
        <a href="../index.php">Home</a> <br>
        <!--
        Source - https://stackoverflow.com/a/8814534
        Posted by Bajrang, modified by community. See post 'Timeline' for change history
        Retrieved 2026-05-21, License - CC BY-SA 3.0
        -->

        <a href="javascript:history.back()">Go Back</a>
    </body>
</html>