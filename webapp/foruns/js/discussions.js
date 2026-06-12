// js/discussions.js
document.addEventListener("DOMContentLoaded", () => {
    const contentArea = document.getElementById("forum-main-content");

    // 1. Carrega a lista global de discussões (Inclui eliminação de tópicos)
    window.loadDiscussions = function(primaryCategory = "") {
        contentArea.innerHTML = `<p class="loading">${forumLang.loading}</p>`;

        let url = `getDiscussions.php`;
        if (primaryCategory) {
            url += `?primaryCategory=${encodeURIComponent(primaryCategory)}`;
        }

        fetch(url)
            .then(res => res.json())
            .then(response => {
                if (response.status === "success" && response.count > 0) {
                    let html = `<button onclick="showNewDiscussionForm()" class="btn-primary">${forumLang.new_topic}</button>`;
                    html += `<ul class="discussions-list">`;

                    response.data.forEach(disc => {
                        const canDeleteDisc = (response.currentUserId === parseInt(disc.idUser)) || response.isModerator;
                        const deleteDiscBtn = canDeleteDisc
                            ? `<button class="btn-delete-disc" style="background:none; border:none; color:#dc3545; cursor:pointer; float:right; font-size:1.2em; padding:0 5px;" onclick="window.deleteDiscussion(event, ${disc.idDiscussion})">🗑️</button>`
                            : '';

                        html += `
                            <li class="discussion-item" onclick="viewDiscussion(${disc.idDiscussion})">
                                ${deleteDiscBtn}
                                <h3>${disc.title}</h3>
                                <span class="meta">${forumLang.author}: <b>${disc.author}</b> | ${forumLang.category_label}: ${disc.primaryCategory} | ${forumLang.replies}: ${disc.total_replies}</span>
                            </li>
                        `;
                    });
                    html += `</ul>`;
                    contentArea.innerHTML = html;
                } else {
                    contentArea.innerHTML = `
                        <button onclick="showNewDiscussionForm()" class="btn-primary">${forumLang.new_topic}</button>
                        <p class="empty-state">${forumLang.empty}</p>
                    `;
                }
            })
            .catch(err => {
                console.error(err);
                contentArea.innerHTML = `<p class="error">${forumLang.error_load}</p>`;
            });
    };

    // 2. Abre uma discussão e renderiza todos os posts associados (Inclui eliminação de posts)
    window.viewDiscussion = function(idDiscussion) {
        contentArea.innerHTML = `<p class="loading">${forumLang.loading}</p>`;

        fetch(`getPosts.php?idDiscussion=${idDiscussion}`)
            .then(res => {
                if (!res.ok) throw new Error(forumLang.error_discussion);
                return res.json();
            })
            .then(response => {
                if (response.status === "success") {
                    let html = `<button onclick="loadDiscussions()" class="btn-secondary">${forumLang.back}</button>`;
                    html += `<div class="posts-stream">`;

                    response.data.forEach((post, index) => {
                        const isMainPost = index === 0;
                        const likeClass = post.has_liked ? 'liked' : '';

                        console.log(`Post ID ${post.idPost} criado pelo User ID:`, post.idUser);

                        const canDelete = (Number(response.currentUserId) === Number(post.idUser)) || response.isModerator;

                        const deleteBtnHtml = canDelete
                            ? `<button class="btn-delete" style="background:none; border:none; color:#dc3545; cursor:pointer; margin-left:12px; font-size: 0.9em; font-weight:bold;" onclick="window.deletePost(${post.idPost}, ${idDiscussion})">${forumLang.delete_post}</button>`
                            : '';

                        html += `
                            <div class="post-card ${isMainPost ? 'main-post' : ''}">
                                <div class="post-meta">
                                    <b>${post.author}</b> ${forumLang.at} ${post.created_at}
                                    ${deleteBtnHtml}
                                </div>
                                <div class="post-body">${post.content}</div>
                                <div class="post-actions">
                                    <button class="like-btn ${likeClass}" onclick="toggleLike(this, ${post.idPost})">
                                        ❤️ <span class="count">${post.likes_count ?? 0}</span> ${forumLang.likes}
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div>`;

                    html += `
                        <div class="reply-box">
                            <h3>${forumLang.leave_reply}</h3>
                            <form id="replyForm" onsubmit="submitReply(event, ${idDiscussion})">
                                <textarea id="replyContent" required placeholder="${forumLang.reply_placeholder}"></textarea>
                                <button type="submit" class="btn-submit">${forumLang.post_reply}</button>
                            </form>
                        </div>
                    `;

                    contentArea.innerHTML = html;
                } else {
                    alert(response.message || forumLang.error_discussion);
                }
            })
            .catch(err => {
                console.error(err);
                contentArea.innerHTML = `<p class="error">${forumLang.error_discussion}</p>`;
            });
    };

    // 3. Executa a ação de Like dinamicamente
    window.toggleLike = function(button, idPost) {
        const formData = new FormData();
        formData.append("idPost", idPost);

        fetch("likes.php", { method: "POST", body: formData })
            .then(res => {
                if (res.status === 401) {
                    alert(forumLang.login_to_like);
                    throw new Error(forumLang.not_authenticated);
                }
                return res.json();
            })
            .then(response => {
                if (response.status === "success") {
                    const countSpan = button.querySelector(".count");
                    let currentCount = parseInt(countSpan.textContent);

                    if (response.action === "liked") {
                        button.classList.add("liked");
                        countSpan.textContent = currentCount + 1;
                    } else {
                        button.classList.remove("liked");
                        countSpan.textContent = currentCount - 1;
                    }
                }
            }).catch(err => console.log(err));
    };

    // 4. Filtro de navegação lateral por categorias
    window.setFilter = function(btn, category) {
        document.querySelectorAll('.forum-nav .filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        window.loadDiscussions(category);
    };

    // 5. Ação AJAX para apagar uma mensagem individual
    window.deletePost = function(idPost, idDiscussion) {
        if (!confirm(forumLang.confirm_delete_post)) return;

        const formData = new FormData();
        formData.append("idPost", idPost);

        fetch("deletePost.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === "success") {
                if (response.discussionDeleted) {
                    alert(forumLang.discussion_auto_removed);
                    window.loadDiscussions();
                } else {
                    window.viewDiscussion(idDiscussion);
                }
            } else {
                alert(response.message || forumLang.error_delete_post);
            }
        })
        .catch(err => console.error("Erro:", err));
    };

    // 6. Ação AJAX para apagar uma discussão completa (Impede propagação do clique do card)
    window.deleteDiscussion = function(event, idDiscussion) {
        event.stopPropagation();

        if (!confirm(forumLang.confirm_delete_discussion)) return;

        const formData = new FormData();
        formData.append("idDiscussion", idDiscussion);

        fetch("deletePost.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(response => {
            if (response.status === "success") {
                alert(forumLang.discussion_deleted);
                window.loadDiscussions();
            } else {
                alert(response.message || forumLang.error_delete_discussion);
            }
        })
        .catch(err => console.error("Erro:", err));
    };

    // Inicialização por defeito: carregar tópicos gerais ao entrar
    loadDiscussions();
});
