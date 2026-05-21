/**
 * js/discussions.js
 * Motor principal do fórum: lista discussões, carrega posts, respostas, likes.
 * Os PHP estão na mesma pasta (foruns/), por isso os paths são relativos simples.
 */

document.addEventListener("DOMContentLoaded", () => {
    const btnNew = document.getElementById("btnNewDiscussion");
    if (btnNew) {
        btnNew.addEventListener("click", openComposer);
    }
    fetchDiscussions();
});

// ─────────────────────────────────────────────────────────────────────────────
// 1. Lista de discussões
// ─────────────────────────────────────────────────────────────────────────────
function fetchDiscussions() {
    const list = document.getElementById("discussionsList");
    if (!list) return;

    // Os data-* são injetados pelo forum-embed.php; no forum.php ficam vazios (mostra tudo)
    const primary   = list.dataset.primary   || "";
    const secondary = list.dataset.secondary || "";

    list.innerHTML = "<p class='discussion-meta state-msg'>A carregar discussões…</p>";

    const params = new URLSearchParams();
    if (primary)   params.set("primary",   primary);
    if (secondary) params.set("secondary", secondary);

    const url = "./getDiscussions.php" + (params.toString() ? "?" + params.toString() : "");

    fetch(url)
        .then(res => res.json())
        .then(data => {
            list.innerHTML = "";
            if (!data || data.length === 0) {
                list.innerHTML = "<p class='discussion-meta state-msg'>Nenhuma discussão encontrada. Seja o primeiro!</p>";
                return;
            }
            data.forEach(disc => renderDiscussionCard(disc, list));
        })
        .catch(() => {
            list.innerHTML = "<p class='discussion-meta state-msg error'>Erro ao ligar ao servidor.</p>";
        });
}

function renderDiscussionCard(disc, container) {
    const card = document.createElement("div");
    card.className = "discussion-card";
    card.id = `disc-${disc.idDiscussion}`;

    const sticky = disc.isSticky == 1 ? `<span class="badge-sticky">📌 Fixado</span>` : "";
    const date   = formatDate(disc.last_posted_at);

    card.innerHTML = `
        <div class="discussion-card-body">
            ${sticky}
            <h4 class="discussion-title">${escapeHtml(disc.title)}</h4>
            <p class="discussion-meta">
                Por <strong>${escapeHtml(disc.author)}</strong>
                · ${escapeHtml(disc.primaryCategory)}
                · ${date}
            </p>
        </div>
        <span class="badge-replies" title="Nº de respostas">💬 ${disc.total_replies}</span>
    `;

    card.addEventListener("click", () => {
        document.querySelectorAll(".discussion-card").forEach(el => el.classList.remove("active"));
        card.classList.add("active");
        loadDiscussionDetails(disc.idDiscussion, disc.title);
    });

    container.appendChild(card);
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. Posts de uma discussão
// ─────────────────────────────────────────────────────────────────────────────
function loadDiscussionDetails(idDiscussion, title) {
    const view = document.getElementById("discussionView");
    if (!view) return;

    view.innerHTML = "<p class='discussion-meta state-msg'>A carregar mensagens…</p>";

    fetch(`./getPosts.php?idDiscussion=${encodeURIComponent(idDiscussion)}`)
        .then(res => res.json())
        .then(posts => {
            if (posts.error) {
                view.innerHTML = `<p class='discussion-meta state-msg error'>${escapeHtml(posts.error)}</p>`;
                return;
            }

            let html = `
                <div class="detail-header">
                    <h3 class="detail-title">${escapeHtml(title)}</h3>
                </div>
                <div class="post-stream" id="postStream">
            `;

            if (posts.length === 0) {
                html += `<p class="discussion-meta state-msg">Sem respostas ainda. Seja o primeiro!</p>`;
            } else {
                posts.forEach(post => { html += buildPostHtml(post); });
            }

            html += `</div>`;

            html += `
                <div class="quick-reply-box">
                    <h4>Deixar uma Resposta</h4>
                    <textarea id="replyTextarea" placeholder="Escreva o seu comentário…" rows="4"></textarea>
                    <div class="quick-reply-actions">
                        <button class="btn-primary-forum btn-inline" id="btnSubmitReply">Publicar Comentário</button>
                    </div>
                </div>
            `;

            view.innerHTML = html;

            // Likes
            view.querySelectorAll(".btn-like").forEach(btn => {
                btn.addEventListener("click", () => toggleLike(btn));
            });

            // Resposta rápida
            document.getElementById("btnSubmitReply").addEventListener("click", () => {
                submitQuickReply(idDiscussion, title);
            });

            // Scroll automático ao fim dos posts
            const stream = document.getElementById("postStream");
            if (stream) stream.scrollTop = stream.scrollHeight;
        })
        .catch(() => {
            view.innerHTML = "<p class='discussion-meta state-msg error'>Erro ao carregar as mensagens.</p>";
        });
}

function buildPostHtml(post) {
    const likedClass = post.has_liked == 1 ? "liked" : "";
    const likedTitle = post.has_liked == 1 ? "Remover like" : "Dar like";

    return `
        <div class="post-item" id="post-${post.idPost}">
            <div class="post-header">
                <span>
                    <strong>${escapeHtml(post.author)}</strong>
                    <span class="post-date">${formatDate(post.created_at)}</span>
                </span>
                <button class="btn-like ${likedClass}"
                    data-post-id="${post.idPost}"
                    title="${likedTitle}">
                    ❤️ <span class="like-count">${post.likes_count}</span>
                </button>
            </div>
            <div class="post-content">${escapeHtml(post.content)}</div>
        </div>
    `;
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. Resposta rápida
// ─────────────────────────────────────────────────────────────────────────────
function submitQuickReply(idDiscussion, title) {
    const textarea = document.getElementById("replyTextarea");
    const content  = textarea ? textarea.value.trim() : "";

    if (!content) {
        alert("Por favor, escreva uma mensagem antes de publicar.");
        return;
    }

    const btn = document.getElementById("btnSubmitReply");
    btn.disabled    = true;
    btn.textContent = "A publicar…";

    const body = new FormData();
    body.append("idDiscussion", idDiscussion);
    body.append("content",      content);

    fetch("./createPost.php", { method: "POST", body })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                textarea.value = "";
                loadDiscussionDetails(idDiscussion, title);
                fetchDiscussions();
            } else {
                alert("Erro ao responder: " + (res.error || "Erro desconhecido"));
                btn.disabled    = false;
                btn.textContent = "Publicar Comentário";
            }
        })
        .catch(() => {
            alert("Erro de ligação ao servidor.");
            btn.disabled    = false;
            btn.textContent = "Publicar Comentário";
        });
}

// ─────────────────────────────────────────────────────────────────────────────
// 4. Likes
// ─────────────────────────────────────────────────────────────────────────────
function toggleLike(btn) {
    const idPost    = btn.dataset.postId;
    const countSpan = btn.querySelector(".like-count");

    btn.disabled = true;

    const body = new FormData();
    body.append("idPost", idPost);

    fetch("./likes.php", { method: "POST", body })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                const n = parseInt(countSpan.textContent, 10) || 0;
                if (res.action === "liked") {
                    btn.classList.add("liked");
                    btn.title           = "Remover like";
                    countSpan.textContent = n + 1;
                } else {
                    btn.classList.remove("liked");
                    btn.title           = "Dar like";
                    countSpan.textContent = Math.max(0, n - 1);
                }
            } else {
                alert(res.error || "Não foi possível registar o like.");
            }
            btn.disabled = false;
        })
        .catch(() => {
            alert("Erro de ligação ao servidor.");
            btn.disabled = false;
        });
}

// ─────────────────────────────────────────────────────────────────────────────
// Utilitários (acessíveis globalmente para o composer.js)
// ─────────────────────────────────────────────────────────────────────────────
function openComposer() {
    document.getElementById("forumComposer")?.classList.add("active");
    document.getElementById("composerOverlay")?.classList.add("active");
    document.getElementById("composerTitle")?.focus();
}

function closeComposer() {
    document.getElementById("forumComposer")?.classList.remove("active");
    document.getElementById("composerOverlay")?.classList.remove("active");
}

function formatDate(dateStr) {
    if (!dateStr) return "—";
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    return d.toLocaleDateString("pt-PT", { day: "2-digit", month: "short", year: "numeric" });
}

function escapeHtml(text) {
    if (text === null || text === undefined) return "";
    return String(text).replace(/[&<>'"]/g, m => (
        { "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#39;", '"': "&quot;" }[m]
    ));
}