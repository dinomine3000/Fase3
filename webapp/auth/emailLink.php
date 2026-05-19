<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */
require_once( "../../Lib/lib.php" );
require_once( "../../Lib/db.php" );

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

$verCode = filter_input( $_INPUT_METHOD, 'verificationCode', FILTER_UNSAFE_RAW, $flags);
$userId = filter_input( $_INPUT_METHOD, 'userId', FILTER_UNSAFE_RAW, $flags);

function getUserChallenge($userId): string{
    //from user id, get associated uuid from challenge database
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName );

    $sql = "SELECT challenge FROM `$dataBaseName`.`auth-challenge` WHERE idUser=?";
    $stmt = mysqli_prepare($GLOBALS['ligacao'], $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);

        $success = mysqli_stmt_execute($stmt);

        if ($success) {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if($row){
                return $row['challenge'];
            }
        }

        mysqli_stmt_close($stmt);
    }
    mysqli_free_result($result);
    dbDisconnect();
    return "-1";
}

function clearUserChallenge($userId): bool{
    //call once you have verified both codes match.
    //will remove from DB and mark account as active.
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    $link = $GLOBALS['ligacao'];
    mysqli_select_db($link, $dataBaseName );
    mysqli_begin_transaction($link);

    try{
        //delete row from challenge table
        $sqlDelete = "DELETE FROM `$dataBaseName`.`auth-challenge` WHERE idUser = ?";
        $stmtDel = mysqli_prepare($link, $sqlDelete);
        if (!$stmtDel) throw new Exception("Prepare Delete failed");
        mysqli_stmt_bind_param($stmtDel, "i", $userId);
        mysqli_stmt_execute($stmtDel);
        mysqli_stmt_close($stmtDel);
        
        //mark account as active.
        $sqlUpdate = "UPDATE `$dataBaseName`.`auth-basic` SET active = 1 WHERE idUser=?";
        $stmtUpd = mysqli_prepare($link, $sqlUpdate);
        if (!$stmtUpd) throw new Exception("Prepare Update failed");
        
        mysqli_stmt_bind_param($stmtUpd, "i", $userId);
        mysqli_stmt_execute($stmtUpd);
        
        if (mysqli_stmt_affected_rows($stmtUpd) === 0) {
            throw new Exception("No user found with ID: $userId");
        }
        mysqli_stmt_close($stmtUpd);
        mysqli_commit($link);
        $success = true;
    }
        catch (Exception $e) {
        // 5. If anything failed, undo everything
        mysqli_rollback($link);
        echo "error" . $e; 
        error_log("Transaction failed: " . $e->getMessage());
        $success = false;
    }

    dbDisconnect();
    return $success;
}

function isUserActive($userId): bool {
    dbConnect(ConfigFile);
    $link = $GLOBALS['ligacao'];
    $dataBaseName = $GLOBALS['configDataBase']->db;
    
    // It's good practice to ensure the DB is selected, 
    // though dbConnect usually handles this.
    mysqli_select_db($link, $dataBaseName);

    $sql = "SELECT active FROM `$dataBaseName`.`auth-basic` WHERE idUser = ? LIMIT 1";
    $stmt = mysqli_prepare($link, $sql);
    $isActive = false;

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Returns true if active is 1, false if 0
            $isActive = (int)$row['active'] === 1;
        }

        mysqli_stmt_close($stmt);
    }

    dbDisconnect();
    return $isActive;
}

$name = webAppName();
$baseNextUrl = getBaseUrl();
$nextUrl = "../auth/formLogin.php";

$dbCode = getUserChallenge($userId);
$isActive = isUserActive($userId);
if($dbCode !== $verCode && !$isActive){
    $linkArgs = "?message=failed";
}

else if($isActive || clearUserChallenge($userId)){
    $linkArgs = "?message=success";
}
else {
    $linkArgs = "?message=error";
}
//echo "Location: " . $baseNextUrl . $nextUrl . $linkArgs;
header( "Location: " . $baseNextUrl . $nextUrl . $linkArgs);
