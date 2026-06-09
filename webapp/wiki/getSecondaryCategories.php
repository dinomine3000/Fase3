<?php
// Turn on error reporting for debugging if it happens again
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../../Lib/lib.php");
require_once( "../../Lib/wikiLib.php" );

$response = array();

if (isset($_GET['primary']) && $_GET['primary'] !== "") {
    $primaryFilter = $_GET['primary'];
    
    // getCategoryList opens and explicitly CLOSES the connection internally via dbDisconnect()
    $secondaryData = getCategoryList('secondary', $primaryFilter);
    
    if (is_array($secondaryData)) {
        foreach ($secondaryData as $row) {
            if (isset($row['secondaryCategory'])) {
                $response[] = $row['secondaryCategory'];
            }
        }
    }
}

// Ensure clean JSON output
header('Content-Type: application/json');
echo json_encode($response);
exit; 
// DO NOT call mysqli_close() here. getCategoryList() already disconnected.
?>