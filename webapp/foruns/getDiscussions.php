<?php
header('Content-Type: application/json; charset=utf-8');

require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");

$primary   = isset($_GET['primary'])   ? $_GET['primary']   : null;
$secondary = isset($_GET['secondary']) ? $_GET['secondary'] : null;

dbConnect(ConfigFile);
$dataBaseName = $GLOBALS['configDataBase']->db;
mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

$sql = "SELECT d.*, u.name as author,
        (SELECT COUNT(*) FROM `$dataBaseName`.`forum_posts` p WHERE p.idDiscussion = d.idDiscussion) as total_replies
        FROM `$dataBaseName`.`forum_discussions` d
        JOIN `$dataBaseName`.`auth-basic` u ON d.idUser = u.idUser";

$types  = "";
$params = [];

if (!empty($primary)) {
    $sql .= " WHERE d.primaryCategory = ?";
    $types .= "s";
    $params[] = $primary;

    if (!empty($secondary)) {
        $sql .= " AND d.secondaryCategory = ?";
        $types .= "s";
        $params[] = $secondary;
    }
}

$sql .= " ORDER BY d.isSticky DESC, d.last_posted_at DESC";

$stmt = mysqli_prepare($GLOBALS['ligacao'], $sql);
if (!empty($types)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result      = mysqli_stmt_get_result($stmt);
$discussions = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($discussions);

dbDisconnect();