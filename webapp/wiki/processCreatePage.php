<?php
include_once("../../Lib/lib.php");
include_once("../../Lib/db.php");
require_once( "../../Lib/wikiLib.php" );
$method = $_SERVER[ 'REQUEST_METHOD' ];
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ( $method=='POST') {
  $_INPUT_METHOD = INPUT_POST;
$_ARGS = $_POST;
} elseif ( $method=='GET' ) {
  $_INPUT_METHOD = INPUT_GET;
$_ARGS = $_GET;
}
else {
  echo "Invalid HTTP method (" . $method . ")";
  exit();
}


$flags[] = FILTER_NULL_ON_FAILURE;

$primary = filter_input( $_INPUT_METHOD, 'primaryCategory', FILTER_UNSAFE_RAW, $flags);
$secondary = filter_input( $_INPUT_METHOD, 'secondaryCategory', FILTER_UNSAFE_RAW, $flags);
$title = filter_input( $_INPUT_METHOD, 'pageTitle', FILTER_UNSAFE_RAW, $flags);
$content = filter_input( $_INPUT_METHOD, 'content', FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES);

writeWikiPage($primary, $secondary, $title, $content);

header("Location: ../index.php");
    
?>