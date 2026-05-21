<?php
header('Content-Type: application/json; charset=utf-8');
require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");

if (!isset($_SESSION)) session_start();

$idUser = isset($_SESSION['idUser']) ? (int)$_SESSION['idUser'] : null;

if (!$idUser) {
    echo json_encode(['success' => false, 'error' => 'Utilizador não autenticado. Faça login.']);
    exit;
}

$idPost = $_POST['idPost'] ?? null;

if (!$idPost) {
    echo json_encode(['success' => false, 'error' => 'Dados em falta']);
    exit;
}

dbConnect(ConfigFile);
$dataBaseName = $GLOBALS['configDataBase']->db;
mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

$sqlCheck  = "SELECT 1 FROM `$dataBaseName`.`forum_likes` WHERE idPost = ? AND idUser = ?";
$stmtCheck = mysqli_prepare($GLOBALS['ligacao'], $sqlCheck);
mysqli_stmt_bind_param($stmtCheck, "ii", $idPost, $idUser);
mysqli_stmt_execute($stmtCheck);
mysqli_stmt_store_result($stmtCheck);

if (mysqli_stmt_num_rows($stmtCheck) > 0) {
    $sqlDel  = "DELETE FROM `$dataBaseName`.`forum_likes` WHERE idPost = ? AND idUser = ?";
    $stmtDel = mysqli_prepare($GLOBALS['ligacao'], $sqlDel);
    mysqli_stmt_bind_param($stmtDel, "ii", $idPost, $idUser);
    mysqli_stmt_execute($stmtDel);
    echo json_encode(['success' => true, 'action' => 'unliked']);
} else {
    $sqlIns  = "INSERT INTO `$dataBaseName`.`forum_likes` (idPost, idUser) VALUES (?, ?)";
    $stmtIns = mysqli_prepare($GLOBALS['ligacao'], $sqlIns);
    mysqli_stmt_bind_param($stmtIns, "ii", $idPost, $idUser);
    mysqli_stmt_execute($stmtIns);
    echo json_encode(['success' => true, 'action' => 'liked']);
}

dbDisconnect();

