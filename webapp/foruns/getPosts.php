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
    if ($currentUserId <= 0 && isset($_SESSION['id'])) {
        $currentUserId = (int)$_SESSION['id'];
    }
    
    $posts = getForumPosts($idDiscussion, $currentUserId);

    $userRole = 0;
    if (isset($_SESSION['role'])) {
        $userRole = (int)$_SESSION['role'];
    } elseif (isset($_SESSION['idRole'])) {
        $userRole = (int)$_SESSION['idRole'];
    }

    $isModerator = ($userRole === 4 || $userRole === 6);

    echo json_encode([
        'status' => 'success',
        'currentUserId' => $currentUserId,
        'isModerator' => $isModerator,
        'data' => $posts
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro interno ao processar o tópico.'
    ]);
}
exit;