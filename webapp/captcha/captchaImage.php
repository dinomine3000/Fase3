<?php


$fontsDirectory = "/usr/share/fonts/truetype/dejavu/";

$jwplayerScript = "/jwplayer6/jwplayer.js";

$ffmpegBinary = "/usr/local/bin/ffmpeg";

ini_set( 'display_errors', 'On' );
error_reporting( E_ALL );

@session_start();

/**
 * function copied from https://www.uuidgenerator.net/dev-corner/php
 * generates RFC 4122 compliant Version 4 UUIDs
 * @param type $data
 * @return type
 */
function generateUUID($data = null) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$challenge = generateUUID();
$captchaValue = substr(@md5($challenge), 0, 9);
$_SESSION[ 'captcha' ] = $captchaValue;
$_SESSION[ 'challenge' ] = @substr($challenge, 0, 32);


$imageCaptcha = ImageCreateFromPNG( "images/fundocaptch.png" );

$colorCaptchaRed = ImageColorAllocate($imageCaptcha, 255, 0, 0);
$colorCaptchaBlue = ImageColorAllocate($imageCaptcha, 0, 0, 255);

$fontName = "DejaVuSans-Bold.ttf";
//$fontName = "DejaVuSans.ttf";
//$fontName = "DejaVuSansMono-Bold.ttf";
//$fontName = "DejaVuSansMono.ttf";
//$fontName = "DejaVuSerif-Bold.ttf";
//$fontName = "DejaVuSerif.ttf";

//$fontName = "Vera.ttf";
//$fontName = "VeraBd.ttf";
//$fontName = "VeraBI.ttf";
//$fontName = "VeraIt.ttf";
//$fontName = "VeraMoBd.ttf";
//$fontName = "VeraMoBI.ttf";
//$fontName = "VeraMoIt.ttf";
//$fontName = "VeraMono.ttf";
//$fontName = "VeraSe.ttf";
//$fontName = "VeraSeBd.ttf";

$fontCaptcha = $fontsDirectory . $fontName;

$code1 = substr($captchaValue, 0, 4);
$code2 = substr($captchaValue, 4, 9);

/*
imagettftext(
    $imageCaptcha,          // Image
    20,                     // Font size
    -5,                     // Font angle
    40,                     // X position
    30,                     // Y position
    $colorCaptchaRed,       // Font color
    $fontCaptcha,           // Font type
    $captchaValue           // Text to write
    );
*/

@ImageTTFText(
        $imageCaptcha,      // Image
        20,                 // Font size
        -15,                 // Font angle
        40,                 // X position
        30,                 // Y position
        $colorCaptchaRed,   // Font color
        $fontCaptcha,       // Font type
        $code1              // Text to write
);

@ImageTTFText(
        $imageCaptcha,      // Image
        20,                 // Font size
        5,                  // Font angle
        120,                // X position
        30,                 // Y position
        $colorCaptchaBlue,  // Font color
        $fontCaptcha,       // Font type
        $code2              // Text to write
);

/*
  $fontCaptcha = 4;

  ImageString(
  $imageCaptcha,
  $fontCaptcha,
  15,
  15,
  $codeCaptcha,
  $corCaptcha);
 */

header( "Content-type: image/png" );

ImagePNG( $imageCaptcha /*, "nome do ficheiro de output"*/  );

ImageDestroy( $imageCaptcha );
?>
