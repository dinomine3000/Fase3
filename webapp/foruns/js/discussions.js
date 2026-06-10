// js/discussions.js
document.addEventListener("DOMContentLoaded", () => {
    const contentArea = document.getElementById("forum-main-content");

    // 1. Carrega a lista global de discussões
    window.loadDiscussions = function(primaryCategory = "") {
        contentArea.innerHTML = `<p class="loading">A carregar discussões...</p>`;
        
        let url = `getDiscussions.php`;
        if (primaryCategory) url += `?primaryCategory=${encodeURIComponent(primaryCategory)}`;

        fetch(url)
            .then(res => res.json())
            .then(response => {
                if (response.status === "success" && response.count > 0) {
                    let html = `<button onclick="showNewDiscussionForm()" class="btn-primary">⚡ Criar Novo Tópico</button>`;
                    html += `<ul class="discussions-list">`;
                    
                    response.data.forEach(disc => {
                        html += `
                            <li class="discussion-item" onclick="viewDiscussion(${disc.idDiscussion})">
                                <h3>${disc.title}</h3>
                                <span class="meta">Autor: <b>${disc.author}</b> | Categoria: ${disc.primaryCategory} | Respostas: ${disc.total_replies}</span>
                            </li>
                        `;
                    });
                    html += `</ul>`;
                    contentArea.innerHTML = html;
                } else {
                    contentArea.innerHTML = `
                        <p>Nenhuma discussão encontrada nesta categoria.</p>
                        <button onclick="showNewDiscussionForm()" class="btn-primary">Seja o primeiro a criar um tópico!</button>
                    `;
                }
            })
            .catch(() => contentArea.innerHTML = `<p class="error">Erro ao carregar o fórum.</p>`);
    };

    // 2. Abre uma discussão e renderiza todos os posts associados
    window.viewDiscussion = function(idDiscussion) {
        contentArea.innerHTML = `<p class="loading">A abrir tópico...</p>`;

        fetch(`getDiscussions.php?idDiscussion=${idDiscussion}`)
            .then(res => res.json())
            .then(response => {
                if (response.status === "success") {
                    let html = `<button onclick="loadDiscussions()" class="btn-secondary">⬅ Voltar à lista</button>`;
                    html += `<div class="posts-stream">`;

                    response.data.forEach((post, index) => {
                        const isMainPost = index === 0;
                        const likeClass = post.has_liked ? 'liked' : '';
                        
                        html += `
                            <div class="post-card ${isMainPost ? 'main-post' : ''}">
                                <div class="post-meta"><b>${post.author}</b> em ${post.created_at}</div>
                                <div class="post-body">${post.content}</div>
                                <div class="post-actions">
                                    <button class="like-btn ${likeClass}" onclick="toggleLike(this, ${post.idPost})">
                                        ❤️ <span class="count">${post.likes_count}</span> Gosto(s)
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    html += `</div>`;
                    
                    // Adicionar formulário de resposta rápida no fim do tópico
                    html += `
                        <div class="reply-box">
                            <h3>Deixe uma resposta:</h3>
                            <form id="replyForm" onsubmit="submitReply(event, ${idDiscussion})">
                                <textarea id="replyContent" required placeholder="Escreva aqui a sua resposta..."></textarea>
                                <button type="submit" class="btn-submit">Publicar Resposta</button>
                            </form>
                        </div>
                    `;

                    contentArea.innerHTML = html;
                }
            });
    };

    // 3. Executa a ação de Like dinamicamente
    window.toggleLike = function(button, idPost) {
        const formData = new FormData();
        formData.append("idPost", idPost);

        fetch("likes.php", { method: "POST", body: formData })
            .then(res => {
                if (res.status === 401) {
                    alert("Precisa de iniciar sessão para fazer Gosto!");
                    throw new Error("Não autenticado");
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

    // Inicialização por defeito: carregar tópicos gerais
    loadDiscussions();
});