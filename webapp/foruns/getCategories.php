<?php
// getCategories.php
include_once("../../Lib/lib.php");

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION)) session_start();

try {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $query = "SELECT `primaryCategory` FROM `$dataBaseName`.`category-primary` WHERE `primaryCategory` IS NOT NULL AND `primaryCategory` != '' ORDER BY `primaryCategory` ASC";
    $res = mysqli_query($GLOBALS['ligacao'], $query);
    
    $categories = [];
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $categories[] = $row['primaryCategory'];
        }
        mysqli_free_result($res);
    }
    
    // Se por algum motivo a tabela estiver vazia, mantém salvaguarda
    if (empty($categories)) {
        $categories = ['GameObjects', 'Games', 'Lore', 'Speedruns'];
    }

    echo json_encode([
        'status' => 'success',
        'data'   => $categories
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Erro ao carregar categorias: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
dbDisconnect();
exit;