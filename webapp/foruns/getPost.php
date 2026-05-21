<?php
header('Content-Type: application/json; charset=utf-8');
require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");

if (!isset($_SESSION)) session_start();

dbConnect(ConfigFile);
$dataBaseName = $GLOBALS['configDataBase']->db;
mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

$idDiscussion  = $_GET['idDiscussion'] ?? null;

// Utilizador atual via sessão (para saber se já deu like)
$currentUserId = isset($_SESSION['idUser']) ? (int)$_SESSION['idUser'] : 0;

if (!$idDiscussion) {
    echo json_encode(['error' => 'Discussão inválida']);
    dbDisconnect();
    exit;
}

$sql = "SELECT p.*, u.name as author,
        (SELECT COUNT(*) FROM `$dataBaseName`.`forum_likes` l WHERE l.idPost = p.idPost) as likes_count,
        EXISTS(SELECT 1 FROM `$dataBaseName`.`forum_likes` l WHERE l.idPost = p.idPost AND l.idUser = ?) as has_liked
        FROM `$dataBaseName`.`forum_posts` p
        JOIN `$dataBaseName`.`auth-basic` u ON p.idUser = u.idUser
        WHERE p.idDiscussion = ?
        ORDER BY p.created_at ASC";

$stmt = mysqli_prepare($GLOBALS['ligacao'], $sql);
mysqli_stmt_bind_param($stmt, "ii", $currentUserId, $idDiscussion);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$posts  = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($posts);
dbDisconnect();