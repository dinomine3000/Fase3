<?php
require_once("../../Lib/wikiLib.php");
include_once("../../Lib/db.php");

$q = trim(filter_input(INPUT_GET, 'q', FILTER_UNSAFE_RAW) ?? '');

header('Content-Type: application/json');

if (strlen($q) < 2) {
    echo json_encode(['categories' => [], 'subcategories' => [], 'pages' => []]);
    exit();
}

dbConnect(ConfigFile);
$db   = $GLOBALS['configDataBase']->db;
$conn = $GLOBALS['ligacao'];
mysqli_select_db($conn, $db);

$esc = mysqli_real_escape_string($conn, $q);

$cats = [];
$r = mysqli_query($conn, "SELECT `primaryCategory` FROM `$db`.`category-primary` WHERE `primaryCategory` LIKE '%$esc%' ORDER BY `primaryCategory` LIMIT 2");
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) $cats[] = ['name' => $row['primaryCategory']];
    mysqli_free_result($r);
}

$subcats = [];
$r = mysqli_query($conn, "SELECT `secondaryCategory`, `primaryCategory` FROM `$db`.`category-secondary` WHERE `secondaryCategory` LIKE '%$esc%' ORDER BY `secondaryCategory` LIMIT 2");
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) $subcats[] = ['name' => $row['secondaryCategory'], 'primaryCategory' => $row['primaryCategory']];
    mysqli_free_result($r);
}

$pages = [];
$r = mysqli_query($conn, "SELECT `pageTitle`, `primaryCategory`, `secondaryCategory` FROM `$db`.`page` WHERE `pageTitle` LIKE '%$esc%' ORDER BY `pageTitle` LIMIT 2");
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) $pages[] = $row;
    mysqli_free_result($r);
}

dbDisconnect();

echo json_encode(['categories' => $cats, 'subcategories' => $subcats, 'pages' => $pages]);
exit();
