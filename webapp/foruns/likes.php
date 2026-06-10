<?php
// like.php
include_once("../../Lib/lib.php");
include_once("../../Lib/wikiLib.php");

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit;
}

if (!isset($_SESSION)) session_start();
$idUser = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

if ($idUser === 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Precisa de estar autenticado para fazer Like.']);
    exit;
}

$idPost = (int)($_POST['idPost'] ?? 0);

if ($idPost <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID do post inválido.']);
    exit;
}

// Executa o toggle na lib ('liked', 'unliked' ou false)
$action = toggleForumLike($idUser, $idPost);

if ($action) {
    echo json_encode([
        'status' => 'success',
        'action' => $action, // devolve 'liked' ou 'unliked' para o JS atualizar o botão dinamicamente
        'message' => $action === 'liked' ? 'Gosto adicionado.' : 'Gosto removido.'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao processar a ação de Like.']);
}