<?php
// deletePost.php
include_once("../../Lib/lib.php");
include_once("../../Lib/wikiLib.php");

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método não permitido.']);
    exit;
}

if (!isset($_SESSION)) session_start();
$currentUserId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

if ($currentUserId === 0) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Inicie sessão para efetuar esta ação.']);
    exit;
}

$idPost = (int)($_POST['idPost'] ?? 0);
$idDiscussion = (int)($_POST['idDiscussion'] ?? 0);

if ($idPost <= 0 && $idDiscussion <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID inválido recebido.']);
    exit;
}

dbConnect(ConfigFile);
$dataBaseName = $GLOBALS['configDataBase']->db;
mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

$userRole = isset($_SESSION['role']) ? (int)$_SESSION['role'] : 0;
$isModerator = ($userRole === 4 || $userRole === 6);

if ($idDiscussion > 0) {
    $idDiscussionSafe = (int)$idDiscussion;
    $query = "SELECT idUser FROM `$dataBaseName`.`forum_discussions` WHERE idDiscussion = $idDiscussionSafe";
    $res = mysqli_query($GLOBALS['ligacao'], $query);
    $discussion = mysqli_fetch_assoc($res);
    
    if (!$discussion) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Discussão não encontrada.']);
        dbDisconnect();
        exit;
    }
    
    if ($currentUserId !== (int)$discussion['idUser'] && !$isModerator) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Não tem permissão para apagar esta discussão.']);
        dbDisconnect();
        exit;
    }
    
    $deleteQuery = "DELETE FROM `$dataBaseName`.`forum_discussions` WHERE idDiscussion = $idDiscussionSafe";
    if (mysqli_query($GLOBALS['ligacao'], $deleteQuery)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Discussão apagada com sucesso.',
            'discussionDeleted' => true
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao apagar a discussão da base de dados.']);
    }
    dbDisconnect();
    exit;
} 

// CENÁRIO B: Eliminar Mensagem/Post Individual
else {
    $idPostSafe = (int)$idPost;
    $query = "SELECT idUser, idDiscussion FROM `$dataBaseName`.`forum_posts` WHERE idPost = $idPostSafe";
    $res = mysqli_query($GLOBALS['ligacao'], $query);
    $post = mysqli_fetch_assoc($res);
    
    if (!$post) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Mensagem não encontrada.']);
        dbDisconnect();
        exit;
    }
    
    if ($currentUserId !== (int)$post['idUser'] && !$isModerator) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Não tem permissões para apagar esta mensagem.']);
        dbDisconnect();
        exit;
    }
    
    $deleteQuery = "DELETE FROM `$dataBaseName`.`forum_posts` WHERE idPost = $idPostSafe";
    if (mysqli_query($GLOBALS['ligacao'], $deleteQuery)) {
        
        $checkQuery = "SELECT COUNT(*) as total FROM `$dataBaseName`.`forum_posts` WHERE idDiscussion = " . (int)$post['idDiscussion'];
        $checkRes = mysqli_query($GLOBALS['ligacao'], $checkQuery);
        $checkRow = mysqli_fetch_assoc($checkRes);
        
        $discussionDeleted = false;
        if ((int)$checkRow['total'] === 0) {
            mysqli_query($GLOBALS['ligacao'], "DELETE FROM `$dataBaseName`.`forum_discussions` WHERE idDiscussion = " . (int)$post['idDiscussion']);
            $discussionDeleted = true;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Mensagem eliminada com sucesso.',
            'discussionDeleted' => $discussionDeleted
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Erro ao eliminar a mensagem.']);
    }
    dbDisconnect();
    exit;
}