/**
 * js/composer.js
 * Gestão do compositor de nova discussão (slide-up).
 * Depende das funções openComposer / closeComposer / fetchDiscussions / loadDiscussionDetails
 * definidas em discussions.js (carregado a seguir no HTML).
 */

document.addEventListener("DOMContentLoaded", () => {
    const btnClose   = document.getElementById("btnCloseComposer");
    const btnCancel  = document.getElementById("btnCancelComposer");
    const btnSubmit  = document.getElementById("btnSubmitComposer");
    const overlay    = document.getElementById("composerOverlay");

    // ── Fechar ──────────────────────────────────────────────────────────────
    if (btnClose)  btnClose.addEventListener("click",  () => closeComposer());
    if (btnCancel) btnCancel.addEventListener("click", () => closeComposer());
    if (overlay)   overlay.addEventListener("click",   () => closeComposer());

    // Fechar com Escape
    document.addEventListener("keydown", e => {
        if (e.key === "Escape") closeComposer();
    });

    // ── Submeter nova discussão ──────────────────────────────────────────────
    if (btnSubmit) {
        btnSubmit.addEventListener("click", () => {
            const titleEl     = document.getElementById("composerTitle");
            const contentEl   = document.getElementById("composerContent");
            const primaryEl   = document.getElementById("composerPrimaryCategory");
            const secondaryEl = document.getElementById("composerSecondaryCategory");

            const title     = titleEl?.value.trim()     ?? "";
            const content   = contentEl?.value.trim()   ?? "";
            const primary   = primaryEl?.value          ?? "";
            const secondary = secondaryEl?.value        ?? "";

            if (!title) {
                titleEl?.focus();
                alert("Por favor, preencha o título do tópico.");
                return;
            }
            if (!content) {
                contentEl?.focus();
                alert("Por favor, escreva o conteúdo do tópico.");
                return;
            }
            if (!primary) {
                alert("Por favor, selecione uma categoria principal.");
                return;
            }

            btnSubmit.disabled    = true;
            btnSubmit.textContent = "A publicar…";

            const body = new FormData();
            body.append("title",             title);
            body.append("content",           content);
            body.append("primaryCategory",   primary);
            body.append("secondaryCategory", secondary);

            fetch("./createDiscussions.php", { method: "POST", body })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (titleEl)   titleEl.value   = "";
                        if (contentEl) contentEl.value = "";

                        closeComposer();

                        // Atualizar lista e abrir logo o novo tópico
                        if (typeof fetchDiscussions === "function") {
                            fetchDiscussions();
                        }
                        if (typeof loadDiscussionDetails === "function" && data.idDiscussion) {
                            loadDiscussionDetails(data.idDiscussion, title);
                        }
                    } else {
                        alert("Erro ao criar tópico: " + (data.error || "Erro desconhecido"));
                    }
                })
                .catch(() => alert("Erro de ligação ao servidor."))
                .finally(() => {
                    btnSubmit.disabled    = false;
                    btnSubmit.textContent = "Publicar Tópico";
                });
        });
    }
});