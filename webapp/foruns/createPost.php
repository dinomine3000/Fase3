<?php
// createPost.php
include_once("../../Lib/lib.php");

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit;
}

$idUser = getActiveUserIdFromAuth();
if ($idUser === 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Utilizador não autenticado.']);
    exit;
}

$idDiscussion = (int)($_POST['idDiscussion'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($idDiscussion <= 0 || empty($content)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos ou conteúdo vazio.']);
    exit;
}

// Chamar função da lib que insere o post e faz o "bump" no tópico
$success = createForumPost($idUser, $idDiscussion, $content);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Resposta publicada com sucesso!']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao publicar a resposta.']);
}