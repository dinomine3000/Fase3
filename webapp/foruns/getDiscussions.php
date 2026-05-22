<?php
// getDiscussions.php

// 1. Incluir o ficheiro de configuração e a biblioteca onde guardaste as funções do fórum
include_once("../../Lib/lib.php");

// 2. Definir o cabeçalho para indicar ao navegador/frontend que a resposta será um JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // 3. Capturar os filtros opcionais passados via URL (Query String)
    // Exemplo: getDiscussions.php?primaryCategory=Desporto&secondaryCategory=Futebol
    $primaryCategory = isset($_GET['primaryCategory']) && $_GET['primaryCategory'] !== '' ? $_GET['primaryCategory'] : null;
    $secondaryCategory = isset($_GET['secondaryCategory']) && $_GET['secondaryCategory'] !== '' ? $_GET['secondaryCategory'] : null;

    // 4. Chamar a função da biblioteca que trata de toda a lógica de BD
    $discussions = getForumDiscussions($primaryCategory, $secondaryCategory);

    // 5. Responder com sucesso e enviar os dados estruturados
    echo json_encode([
        'status' => 'success',
        'count'  => count($discussions),
        'data'   => $discussions
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Caso ocorra algum erro inesperado
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Erro ao carregar as discussões do fórum.'
    ]);
}
exit;