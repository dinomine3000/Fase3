<?php
// createDiscussions.php
include_once("../../Lib/lib.php");

header('Content-Type: application/json; charset=utf-8');

// Bloquear se não for um pedido POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit;
}

// 1. Verificar se o utilizador está autenticado através da função da lib
$idUser = getActiveUserIdFromAuth();
if ($idUser === 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Utilizador não autenticado.']);
    exit;
}

// 2. Capturar e validar os dados enviados pelo formulário (POST)
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$primaryCategory = trim($_POST['primaryCategory'] ?? '');
$secondaryCategory = trim($_POST['secondaryCategory'] ?? '');

if (empty($title) || empty($content) || empty($primaryCategory)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Preencha todos os campos obrigatórios (Título, Conteúdo e Categoria).']);
    exit;
}

// 3. Executar a criação através da lib
$idDiscussion = createForumDiscussion($idUser, $title, $content, $primaryCategory, !empty($secondaryCategory) ? $secondaryCategory : null);

if ($idDiscussion) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Discussão criada com sucesso!',
        'idDiscussion' => $idDiscussion
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro interno ao criar a discussão.']);
}