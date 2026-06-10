<?php
// getDiscussions.php
include_once("../../Lib/lib.php");
include_once("../../Lib/wikiLib.php");

header('Content-Type: application/json; charset=utf-8');

try {
    $primaryCategory = isset($_GET['primaryCategory']) && $_GET['primaryCategory'] !== '' ? $_GET['primaryCategory'] : null;
    $secondaryCategory = isset($_GET['secondaryCategory']) && $_GET['secondaryCategory'] !== '' ? $_GET['secondaryCategory'] : null;

    $discussions = getForumDiscussions($primaryCategory, $secondaryCategory);

    echo json_encode([
        'status' => 'success',
        'count'  => count($discussions),
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