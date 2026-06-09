<?php 
require_once( "../../Lib/wikiLib.php" );
include_once("../../Lib/db.php");

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
$searchTerm= filter_input( INPUT_GET, 'user', FILTER_UNSAFE_RAW, $flags);
if (!isset($searchTerm) || trim($searchTerm) === '') {
    echo json_encode([]);
    exit();
}
$results = getResultsMatching($searchTerm, "name" );
echo json_encode($results);
exit();
?>