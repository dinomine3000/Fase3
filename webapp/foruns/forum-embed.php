<?php
// forum-embed.php
// Garante que as dependências existem se for chamado isoladamente
include_once("../../Lib/lib.php");
?>
<div id="forum-root" class="forum-wrapper">
    
    <div class="forum-nav">
        <button class="filter-btn active" data-category="">Todas</button>
        <button class="filter-btn" data-category="Geral">Geral</button>
        <button class="filter-btn" data-category="Suporte">Suporte</button>
    </div>

    <div id="forum-main-content">
        <p class="loading">A carregar discussões...</p>
    </div>

    <script src="js/composer.js"></script>
    <script src="js/discussions.js"></script>
</div>