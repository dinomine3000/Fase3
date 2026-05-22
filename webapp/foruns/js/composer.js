// js/composer.js

// Exibe o formulário de criação de um novo tópico
function showNewDiscussionForm() {
    const contentArea = document.getElementById("forum-main-content");
    
    contentArea.innerHTML = `
        <button onclick="loadDiscussions()" class="btn-secondary">Cancel</button>
        <div class="forum-composer">
            <h2>Criar Novo Tópico de Discussão</h2>
            <form id="newDiscussionForm" onsubmit="submitNewDiscussion(event)">
                <label>Título:</label>
                <input type="text" id="compTitle" required placeholder="Introduza um título apelativo...">
                
                <label>Categoria Primária:</label>
                <select id="compPrimary" required>
                    <option value="Geral">Geral</option>
                    <option value="Suporte">Suporte</option>
                </select>

                <label>Mensagem Inicial:</label>
                <textarea id="compContent" required rows="6" placeholder="Escreva a sua mensagem aqui..."></textarea>
                
                <button type="submit" class="btn-primary">Lançar Discussão</button>
            </form>
        </div>
    `;
}

// Submete a nova discussão via AJAX para createDiscussions.php
function submitNewDiscussion(event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append("title", document.getElementById("compTitle").value);
    formData.append("primaryCategory", document.getElementById("compPrimary").value);
    formData.append("content", document.getElementById("compContent").value);

    fetch("createDiscussions.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(response => {
            if (response.status === "success") {
                // Redireciona imediatamente para o tópico recém-criado
                viewDiscussion(response.idDiscussion);
            } else {
                alert(response.message);
            }
        });
}

// Submete uma nova resposta (comentário) rápida para createPost.php
function submitReply(event, idDiscussion) {
    event.preventDefault();
    
    const contentValue = document.getElementById("replyContent").value;
    const formData = new FormData();
    formData.append("idDiscussion", idDiscussion);
    formData.append("content", contentValue);

    fetch("createPost.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(response => {
            if (response.status === "success") {
                // Recarrega a discussão para mostrar a nova mensagem no fim
                viewDiscussion(idDiscussion);
            } else {
                alert(response.message);
            }
        });
}