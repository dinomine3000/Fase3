<?php
/**
 * forum-embed.php
 * Incluir no final das páginas wiki via: include 'forum-embed.php';
 *
 * Variáveis obrigatórias antes do include:
 *   $currentPrimary   → string  (ex: 'primary_1')
 *   $currentSecondary → string|null  (ex: 'secondary_2' ou null)
 */
?>
<link rel="stylesheet" href="styles/discussions.css">
<link rel="stylesheet" href="styles/composer.css">

<hr style="margin-top:50px; border:0; border-top:1px solid #eee;">
<h3 style="font-family:'Playfair Display',serif; color:#1e2d45;">Fórum de Discussão Relacionado</h3>

<div class="forum-container" style="margin-top:14px;">

    <!-- Sidebar: botão de novo tópico -->
    <div class="forum-sidebar">
        <button class="btn-primary-forum" id="btnNewDiscussion">+ Começar Tópico</button>
    </div>

    <!-- Lista de discussões filtrada pela categoria desta página wiki -->
    <div class="forum-sidebar-list"
         id="discussionsList"
         data-primary="<?php echo htmlspecialchars($currentPrimary ?? ''); ?>"
         data-secondary="<?php echo htmlspecialchars($currentSecondary ?? ''); ?>">
        <p class="discussion-meta state-msg">A carregar discussões…</p>
    </div>

    <!-- Detalhe da discussão selecionada -->
    <div class="forum-detail-panel" id="discussionView">
        <div class="empty-state">
            <span class="empty-icon">💬</span>
            <p>Selecione ou inicie uma discussão sobre esta categoria da Wiki.</p>
        </div>
    </div>

</div>

<!-- Compositor -->
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
        <!-- Categorias pré-preenchidas com os valores da página wiki atual -->
        <div class="composer-categories">
            <select id="composerPrimaryCategory" aria-label="Categoria principal">
                <option value="<?php echo htmlspecialchars($currentPrimary ?? ''); ?>" selected>
                    <?php echo htmlspecialchars($currentPrimary ?? 'Principal'); ?>
                </option>
            </select>
            <select id="composerSecondaryCategory" aria-label="Subcategoria">
                <option value=""<?php echo empty($currentSecondary) ? ' selected' : ''; ?>>Sem subcategoria</option>
                <?php if (!empty($currentSecondary)): ?>
                <option value="<?php echo htmlspecialchars($currentSecondary); ?>" selected>
                    <?php echo htmlspecialchars($currentSecondary); ?>
                </option>
                <?php endif; ?>
            </select>
        </div>
        <textarea
            id="composerContent"
            placeholder="Descreva o seu tópico em detalhe…"
            rows="5"
        ></textarea>
    </div>
    <div class="composer-footer">
        <button class="btn-secondary-forum" id="btnCancelComposer">Cancelar</button>
        <button class="btn-primary-forum btn-inline" id="btnSubmitComposer">Publicar Tópico</button>
    </div>
</div>
<div class="composer-overlay" id="composerOverlay"></div>

<script src="js/composer.js"></script>
<script src="js/discussions.js"></script>
