// js/composer.js

// Exibe o formulário de criação de um novo tópico com categorias dinâmicas da BD
window.showNewDiscussionForm = function() {
    const contentArea = document.getElementById("forum-main-content");
    
    contentArea.innerHTML = `<p class="loading">A carregar categorias disponíveis...</p>`;

    fetch("getCategories.php")
        .then(res => res.json())
        .then(response => {
            let optionsHtml = "";
            
            if (response.status === "success" && response.data.length > 0) {
                response.data.forEach(cat => {
                    optionsHtml += `<option value="${cat}">${cat}</option>`;
                });
            } else {
                optionsHtml = `
                    <option value="Games">Games</option>
                    <option value="Lore">Lore</option>
                    <option value="Geral">Geral</option>
                `;
            }

            contentArea.innerHTML = `
                <button onclick="loadDiscussions()" class="btn-secondary" style="margin-bottom:15px;">⬅️ Cancelar</button>
                <div class="forum-composer">
                    <h2>Criar Novo Tópico de Discussão</h2>
                    <form id="newDiscussionForm" onsubmit="window.submitNewDiscussion(event)">
                        <label>Título:</label>
                        <input type="text" id="compTitle" required placeholder="Introduza um título apelativo...">
                        
                        <label>Categoria Primária:</label>
                        <select id="compPrimary" required>
                            ${optionsHtml}
                        </select>

                        <label>Mensagem Inicial:</label>
                        <textarea id="compContent" required rows="6" placeholder="Escreva a sua mensagem aqui..."></textarea>
                        
                        <button type="submit" class="btn-primary" style="margin-top:10px;">Lançar Discussão</button>
                    </form>
                </div>
            `;
        })
        .catch(err => {
            console.error("Erro ao processar categorias:", err);
            contentArea.innerHTML = `
                <button onclick="loadDiscussions()" class="btn-secondary" style="margin-bottom:15px;">⬅️ Cancelar</button>
                <div class="forum-composer">
                    <h2>Criar Novo Tópico de Discussão</h2>
                    <form id="newDiscussionForm" onsubmit="window.submitNewDiscussion(event)">
                        <label>Título:</label>
                        <input type="text" id="compTitle" required placeholder="Introduza um título apelativo...">
                        
                        <label>Categoria Primária:</label>
                        <select id="compPrimary" required>
                            <option value="Games">Games</option>
                            <option value="Lore">Lore</option>
                            <option value="Geral">Geral</option>
                        </select>

                        <label>Mensagem Inicial:</label>
                        <textarea id="compContent" required rows="6" placeholder="Escreva a sua mensagem aqui..."></textarea>
                        
                        <button type="submit" class="btn-primary" style="margin-top:10px;">Lançar Discussão</button>
                    </form>
                </div>
            `;
        });
};

// Submete a nova discussão via AJAX para createDiscussions.php
window.submitNewDiscussion = function(event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append("title", document.getElementById("compTitle").value);
    formData.append("primaryCategory", document.getElementById("compPrimary").value);
    formData.append("content", document.getElementById("compContent").value);

    fetch("createDiscussions.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(response => {
            if (response.status === "success") {
                window.viewDiscussion(response.idDiscussion);
            } else {
                alert(response.message);
            }
        })
        .catch(err => console.error("Erro ao criar discussão:", err));
};

// Submete uma nova resposta rápida para createPost.php
window.submitReply = function(event, idDiscussion) {
    event.preventDefault();
    
    const contentValue = document.getElementById("replyContent").value;
    const formData = new FormData();
    formData.append("idDiscussion", idDiscussion);
    formData.append("content", contentValue);

    fetch("createPost.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(response => {
            if (response.status === "success") {
                window.viewDiscussion(idDiscussion);
            } else {
                alert(response.message);
            }
        })
        .catch(err => console.error("Erro ao publicar resposta:", err));
};