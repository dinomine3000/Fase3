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

$idDiscussion = $_POST['idDiscussion'] ?? null;
$content      = $_POST['content']      ?? null;

if (!$idDiscussion || !$content) {
    echo json_encode(['success' => false, 'error' => 'Campos obrigatórios em falta']);
    exit;
}

dbConnect(ConfigFile);
$dataBaseName = $GLOBALS['configDataBase']->db;
mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

mysqli_begin_transaction($GLOBALS['ligacao']);
try {
    $sqlPost  = "INSERT INTO `$dataBaseName`.`forum_posts` (idDiscussion, idUser, content) VALUES (?, ?, ?)";
    $stmtPost = mysqli_prepare($GLOBALS['ligacao'], $sqlPost);
    mysqli_stmt_bind_param($stmtPost, "iis", $idDiscussion, $idUser, $content);
    mysqli_stmt_execute($stmtPost);

    $sqlUpdate  = "UPDATE `$dataBaseName`.`forum_discussions` SET last_posted_at = NOW() WHERE idDiscussion = ?";
    $stmtUpdate = mysqli_prepare($GLOBALS['ligacao'], $sqlUpdate);
    mysqli_stmt_bind_param($stmtUpdate, "i", $idDiscussion);
    mysqli_stmt_execute($stmtUpdate);

    mysqli_commit($GLOBALS['ligacao']);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    mysqli_rollback($GLOBALS['ligacao']);
    echo json_encode(['success' => false, 'error' => 'Falha ao publicar comentário']);
}
dbDisconnect();