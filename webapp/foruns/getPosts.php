<?php
// getPosts.php
include_once("../../Lib/lib.php");
include_once("../../Lib/wikiLib.php");

if (!isset($_SESSION)) session_start();

header('Content-Type: application/json; charset=utf-8');

$idDiscussion = (int)($_GET['idDiscussion'] ?? 0);

if ($idDiscussion <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID da discussão inválido.']);
    exit;
}

try {
    $currentUserId = getActiveUserIdFromAuth();

    $posts = getForumPosts($idDiscussion, $currentUserId);

    echo json_encode([
        'status' => 'success',
        'data'   => $posts
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno ao processar o tópico.'
    ]);
}
exit;