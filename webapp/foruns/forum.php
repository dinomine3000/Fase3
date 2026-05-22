<?php
// forum.php
include_once("../../Lib/lib.php");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Fórum da Comunidade</title>
    <link rel="stylesheet" href="css/forum.css">
</head>
<body>

    <div class="container">
        <a href="javascript:history.back()">← Back to Home</a>
        
        <h1>Fórum de Discussão</h1>
        
        <?php include __DIR__ . '/forum-embed.php'; ?>
    </div>
</body>
</html>