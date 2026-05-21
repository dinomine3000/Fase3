function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}

/* ── STATE ── */
let searchTerm = '';

/* ── RENDER SUBCATEGORY BANNERS ── */
function renderSubcats() {
  const wrap = document.getElementById('subcatRow');
  if (!subcategories.length) { wrap.style.display = 'none'; return; }
  wrap.className = 'subcat-banners';
  const defaultImage = 'img/davie.png';
  wrap.innerHTML = subcategories.map(s => {
    const img = s.image || defaultImage;
    return `
    <a class="subcat-banner" href="subcategory.php?id=${s.id}" style="--subcat-banner-image: url('${img}')">
      <div class="subcat-banner-body">
        <div class="subcat-banner-title">${s.name}</div>
        <div class="subcat-banner-desc">${s.description || ''}</div>
      </div>
    </a>`;
  }).join('');
}

function filterArticles(val) { searchTerm = val.toLowerCase(); renderArticles(); }

renderSubcats();
