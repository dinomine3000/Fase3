<?php
// getDiscussions.php
include_once("../../Lib/lib.php");
include_once("../../Lib/wikiLib.php");

if (!isset($_SESSION)) session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    $primaryCategory = isset($_GET['primaryCategory']) && $_GET['primaryCategory'] !== '' ? $_GET['primaryCategory'] : null;
    $secondaryCategory = isset($_GET['secondaryCategory']) && $_GET['secondaryCategory'] !== '' ? $_GET['secondaryCategory'] : null;

    $discussions = getForumDiscussions($primaryCategory, $secondaryCategory);

    // Obtém dados do utilizador e permissões de moderação
    $currentUserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
    $userRole = isset($_SESSION['role']) ? (int)$_SESSION['role'] : 0;
    $isModerator = ($userRole === 4 || $userRole === 6);

    echo json_encode([
        'status' => 'success',
        'count'  => count($discussions),
        'currentUserId' => $currentUserId,
        'isModerator' => $isModerator,
        'data'   => $discussions
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Erro ao carregar as discussões do fórum.'
    ]);
}
exit;