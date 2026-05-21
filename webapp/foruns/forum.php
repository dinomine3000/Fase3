<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fórum Geral</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/discussions.css">
    <link rel="stylesheet" href="styles/composer.css">
</head>
<body>

<div class="page-header">
    <div class="page-header-inner">
        <div>
            <h1 class="page-title">Fórum Central</h1>
            <p class="page-subtitle">Explora e participa nas conversas do SGC.</p>
        </div>
        <button class="btn-primary-forum btn-inline" id="btnNewDiscussion">+ Novo Tópico</button>
    </div>
</div>

<div class="forum-container">

    <!-- Coluna esquerda: lista de discussões (sem filtro de categoria — mostra tudo) -->
    <div class="forum-sidebar-list">
        <div id="discussionsList" data-primary="" data-secondary="">
            <p class="discussion-meta state-msg">A carregar discussões...</p>
        </div>
    </div>

    <!-- Coluna direita: posts da discussão selecionada -->
    <div class="forum-detail-panel" id="discussionView">
        <div class="empty-state">
            <span class="empty-icon">💬</span>
            <p>Selecione um tópico para ler as respostas.</p>
        </div>
    </div>
</div>

<!-- ── Compositor de nova discussão (slide-up) ── -->
<div class="forum-composer" id="forumComposer" aria-modal="true" role="dialog" aria-labelledby="composerTitleLabel">
    <div class="composer-header">
        <span class="composer-title-label" id="composerTitleLabel">Nova Discussão</span>
        <button id="btnCloseComposer" class="btn-close-composer" aria-label="Fechar compositor">✖</button>
    </div>
    <div class="composer-body">
        <input
            type="text"
            id="composerTitle"
            placeholder="Título do tópico…"
            maxlength="150"
            autocomplete="off"
        >
        <div class="composer-categories">
            <select id="composerPrimaryCategory" aria-label="Categoria principal">
                <option value="test1">test1</option>
                <option value="test2">test2</option>
            </select>
            <select id="composerSecondaryCategory" aria-label="Subcategoria">
                <option value="">Sem subcategoria</option>
                <option value="secondary1">secondary1</option>
                <option value="secondary2">secondary2</option>
                <option value="secondaryBIg">secondaryBIg</option>
            </select>
        </div>
        <textarea
            id="composerContent"
            placeholder="Descreva o seu tópico em detalhe…"
            rows="6"
        ></textarea>
    </div>
    <div class="composer-footer">
        <button class="btn-secondary-forum" id="btnCancelComposer">Cancelar</button>
        <button class="btn-primary-forum btn-inline" id="btnSubmitComposer">Publicar Tópico</button>
    </div>
</div>

<!-- Overlay escuro atrás do compositor -->
<div class="composer-overlay" id="composerOverlay"></div>

<script src="js/composer.js"></script>
<script src="js/discussions.js"></script>

<a href="javascript:history.back()">Go Back</a>
</body>
</html>