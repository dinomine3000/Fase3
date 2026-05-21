function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}

/* ── STATE & RENDER ── */
let activeCat = null, searchTerm = '';

function renderCats() {
  document.getElementById('catGrid').innerHTML = categories.map(c => `
    <div class="col">
      <div class="cat-card${activeCat === c.id ? ' active' : ''}" onclick="setCat('${c.id}')">
        <div class="cat-img">
          ${c.img
            ? `<img src="${c.img}" alt="${c.name}">`
            : `<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--cat-icon)" stroke-width="1">
                <rect x="3" y="3" width="18" height="14" rx="2"/>
                <line x1="3" y1="21" x2="21" y2="21"/>
                <line x1="8" y1="17" x2="8" y2="21"/>
                <line x1="16" y1="17" x2="16" y2="21"/>
               </svg>`}
        </div>
        <div class="cat-body">
          <div class="cat-label">${c.name}</div>
          <div class="cat-count">${c.count} articles</div>
        </div>
      </div>
    </div>`).join('');
}

function renderArticles() {
  let list = articles;
  if (activeCat)   list = list.filter(a => a.cat === activeCat);
  if (searchTerm)  list = list.filter(a =>
    a.title.toLowerCase().includes(searchTerm) ||
    a.excerpt.toLowerCase().includes(searchTerm) ||
    a.tag.toLowerCase().includes(searchTerm)
  );
  document.getElementById('articleList').innerHTML = list.length
    ? list.map(a => `
        <div class="article-bar">
          <span class="art-tag">${a.tag}</span>
          <div class="art-body">
            <div class="art-title">${a.title}</div>
            <div class="art-excerpt">${a.excerpt}</div>
          </div>
          <div class="art-meta">
            <span>${a.path}</span><span class="art-sep">·</span><span>${a.date}</span><span class="art-sep">·</span><span>${a.read}</span>
          </div>
        </div>`).join('')
    : `<div class="no-results">No articles found.</div>`;
}

function setCat(id)          { activeCat = activeCat === id ? null : id; renderCats(); renderArticles(); }
function filterArticles(val) { searchTerm = val.toLowerCase(); renderArticles(); }
function resetFilters()      { activeCat = null; searchTerm = ''; document.getElementById('searchInput').value = ''; renderCats(); renderArticles(); }

renderCats();
renderArticles();
