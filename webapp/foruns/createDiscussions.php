<?php
header('Content-Type: application/json; charset=utf-8');
require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");

if (!isset($_SESSION)) session_start();

// Utilizador autenticado via sessão
$idUser = isset($_SESSION['idUser']) ? (int)$_SESSION['idUser'] : null;

if (!$idUser) {
    echo json_encode(['success' => false, 'error' => 'Utilizador não autenticado. Faça login.']);
    exit;
}

$title     = $_POST['title']             ?? null;
$content   = $_POST['content']           ?? null;
$primary   = $_POST['primaryCategory']   ?? null;
$secondary = $_POST['secondaryCategory'] ?? null;

if (!$title || !$content || !$primary) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

dbConnect(ConfigFile);
$dataBaseName = $GLOBALS['configDataBase']->db;
mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

$slug           = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
$secondaryParam = !empty($secondary) ? $secondary : null;

mysqli_begin_transaction($GLOBALS['ligacao']);
try {
    $sqlDisc  = "INSERT INTO `$dataBaseName`.`forum_discussions` (title, slug, idUser, primaryCategory, secondaryCategory) VALUES (?, ?, ?, ?, ?)";
    $stmtDisc = mysqli_prepare($GLOBALS['ligacao'], $sqlDisc);
    mysqli_stmt_bind_param($stmtDisc, "ssiss", $title, $slug, $idUser, $primary, $secondaryParam);
    mysqli_stmt_execute($stmtDisc);

    $idDiscussion = mysqli_insert_id($GLOBALS['ligacao']);

    $sqlPost  = "INSERT INTO `$dataBaseName`.`forum_posts` (idDiscussion, idUser, content) VALUES (?, ?, ?)";
    $stmtPost = mysqli_prepare($GLOBALS['ligacao'], $sqlPost);
    mysqli_stmt_bind_param($stmtPost, "iis", $idDiscussion, $idUser, $content);
    mysqli_stmt_execute($stmtPost);

    mysqli_commit($GLOBALS['ligacao']);
    echo json_encode(['success' => true, 'idDiscussion' => $idDiscussion]);
} catch (Exception $e) {
    mysqli_rollback($GLOBALS['ligacao']);
    echo json_encode(['success' => false, 'error' => 'Erro interno ao processar base de dados']);
}
dbDisconnect();