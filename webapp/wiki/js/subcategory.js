function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}

/* ── STATE ── */
let searchTerm = '';

/* ── RENDER ARTICLES ── */
function renderArticles() {
  let list = articles;
  if (searchTerm) list = list.filter(a =>
    a.title.toLowerCase().includes(searchTerm) ||
    a.excerpt.toLowerCase().includes(searchTerm)
  );
  document.getElementById('articleList').innerHTML = list.length
    ? list.map(a => `
        <div class="article-bar" onclick="location.href='article.php?id=1'">
          <span class="art-tag">${a.tag}</span>
          <div class="art-body">
            <div class="art-title">${a.title}</div>
            <div class="art-excerpt">${a.excerpt}</div>
          </div>
          <div class="art-meta">
            <span>${a.date}</span><span class="art-sep">·</span><span>${a.read}</span>
          </div>
        </div>`).join('')
    : '<div class="no-results">No articles found.</div>';
}

function filterArticles(val) { searchTerm = val.toLowerCase(); renderArticles(); }

renderArticles();
