<?php
// getPost.php
include_once("../../Lib/lib.php");

header('Content-Type: application/json; charset=utf-8');

$idPost = (int)($_GET['idPost'] ?? 0);
if ($idPost <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do post inválido.']);
    exit;
}

dbConnect(ConfigFile);
$db = $GLOBALS['configDataBase']->db;
$idPostSafe = (int)$idPost;

$query = "SELECT p.*, u.name as author FROM `$db`.`forum_posts` p 
          JOIN `$db`.`auth-basic` u ON p.idUser = u.idUser 
          WHERE p.idPost = $idPostSafe LIMIT 1";

$result = mysqli_query($GLOBALS['ligacao'], $query);
$post = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;

dbDisconnect();

if ($post) {
    echo json_encode(['status' => 'success', 'data' => $post]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Post não encontrado.']);
}