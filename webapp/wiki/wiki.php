<?php
// webapp/wiki/wiki.php
// Replace JS arrays below with your DB queries
// $articles = fetch_articles(); $categories = fetch_categories();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
/* ── LIGHT THEME ── */
[data-theme="light"] {
  --bg:      #f7f6f3;
  --bg2:     #ffffff;
  --bg3:     #efefec;
  --border:  rgba(0,0,0,0.08);
  --border2: rgba(0,0,0,0.18);
  --text:    #1c1c1a;
  --muted:   #6b6b67;
  --faint:   #b0afa9;
  --green:   #3d8b30;
  --green2:  #2e6e24;
  --shadow:  rgba(0,0,0,0.06);
  --cat-s1:  #eef0eb;
  --cat-s2:  #ededf0;
  --cat-s3:  #f0ebeb;
  --cat-s4:  #ebeff0;
  --cat-s5:  #f0efeb;
  --cat-icon: #ccc;
}

/* ── DARK THEME ── */
[data-theme="dark"] {
  --bg:      #111214;
  --bg2:     #18191c;
  --bg3:     #1f2023;
  --border:  rgba(255,255,255,0.07);
  --border2: rgba(255,255,255,0.16);
  --text:    #e2e2e0;
  --muted:   #888880;
  --faint:   #444440;
  --green:   #5a9e4a;
  --green2:  #6db85c;
  --shadow:  rgba(0,0,0,0.25);
  --cat-s1:  #181c18;
  --cat-s2:  #1a1a1c;
  --cat-s3:  #1c1818;
  --cat-s4:  #181a1c;
  --cat-s5:  #1a1c18;
  --cat-icon: #333;
}

/* ── BASE ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html { transition: background .25s, color .25s; }
body {
  background: var(--bg); color: var(--text);
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 15px; line-height: 1.6;
  transition: background .25s, color .25s;
}

/* ── HEADER ── */
.site-header {
  background: var(--bg2); border-bottom: 1px solid var(--border);
  position: sticky; top: 0; z-index: 100;
  transition: background .25s, border-color .25s;
}
.logo {
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.05rem; font-weight: 700;
  color: var(--green); text-decoration: none; letter-spacing: -0.01em;
}
.logo:hover { color: var(--green2); }

.search-wrap {
  background: var(--bg3); border: 1px solid var(--border);
  border-radius: 7px; display: flex; align-items: center;
  padding: 0 10px; gap: 7px; height: 36px;
  transition: border-color .2s, background .25s;
}
.search-wrap:focus-within { border-color: var(--border2); }
.search-wrap input {
  background: none; border: none; outline: none;
  color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 13px; width: 100%;
}
.search-wrap input::placeholder { color: var(--faint); }
.search-wrap svg { color: var(--faint); flex-shrink: 0; }

/* ── BUTTONS ── */
.hbtn {
  height: 36px; padding: 0 15px; border-radius: 7px;
  font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; font-weight: 500;
  border: 1px solid var(--border); background: none; color: var(--muted);
  cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
  transition: all .15s; white-space: nowrap; text-decoration: none;
}
.hbtn:hover { border-color: var(--border2); color: var(--text); background: var(--bg3); }
.hbtn.primary { background: var(--green); border-color: var(--green); color: #fff; }
.hbtn.primary:hover { background: var(--green2); border-color: var(--green2); color: #fff; }
.hbtn svg { width: 14px; height: 14px; }

/* Theme toggle */
.theme-toggle {
  width: 36px; height: 36px; padding: 0; border-radius: 7px;
  border: 1px solid var(--border); background: none; color: var(--muted);
  cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
  transition: all .15s; flex-shrink: 0;
}
.theme-toggle:hover { border-color: var(--border2); color: var(--text); background: var(--bg3); }
.theme-toggle svg { width: 15px; height: 15px; }
.icon-sun  { display: none; }
.icon-moon { display: block; }
[data-theme="dark"] .icon-sun  { display: block; }
[data-theme="dark"] .icon-moon { display: none; }

/* ── SECTION HEADINGS ── */
.section-heading {
  font-family: 'DM Mono', monospace; font-size: 11px; font-weight: 500;
  letter-spacing: 0.08em; text-transform: uppercase; color: var(--faint);
  padding-bottom: 0.75rem; border-bottom: 1px solid var(--border); margin-bottom: 1.25rem;
  transition: color .25s, border-color .25s;
}

/* ── CATEGORY CARDS ── */
.cat-card {
  background: var(--bg2); border: 1px solid var(--border);
  border-radius: 9px; overflow: hidden; cursor: pointer;
  transition: border-color .2s, box-shadow .2s, background .25s;
}
.cat-card:hover { border-color: var(--border2); box-shadow: 0 2px 10px var(--shadow); }
.cat-card.active { border-color: var(--green); }
.cat-card.active .cat-label { color: var(--green); }

.cat-img {
  width: 100%; aspect-ratio: 16/9;
  display: flex; align-items: center; justify-content: center;
  border-bottom: 1px solid var(--border); transition: background .25s;
}
.cat-img.s1 { background: var(--cat-s1); }
.cat-img.s2 { background: var(--cat-s2); }
.cat-img.s3 { background: var(--cat-s3); }
.cat-img.s4 { background: var(--cat-s4); }
.cat-img.s5 { background: var(--cat-s5); }

.cat-body  { padding: 0.6rem 0.85rem; }
.cat-label { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 1px; transition: color .25s; }
.cat-count { font-family: 'DM Mono', monospace; font-size: 10px; color: var(--faint); }

/* ── ARTICLE BARS ── */
.article-bar {
  background: var(--bg2); border: 1px solid var(--border);
  border-radius: 8px; padding: 0.8rem 1rem;
  cursor: pointer; display: flex; align-items: baseline; gap: 1rem;
  transition: border-color .15s, box-shadow .15s, background .25s;
}
.article-bar:hover { border-color: var(--border2); box-shadow: 0 2px 8px var(--shadow); }

.art-tag {
  font-family: 'DM Mono', monospace; font-size: 10px; text-transform: uppercase;
  color: var(--green); background: rgba(61,139,48,0.09);
  border-radius: 4px; padding: 3px 7px; white-space: nowrap;
  flex-shrink: 0; position: relative; top: -1px;
}
[data-theme="dark"] .art-tag { background: rgba(90,158,74,0.12); }

.art-body    { flex: 1; min-width: 0; }
.art-title   { font-size: 14px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 1px; }
.art-excerpt { font-size: 12px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.art-meta    { font-family: 'DM Mono', monospace; font-size: 10px; color: var(--faint); white-space: nowrap; display: flex; gap: 0.5rem; align-items: center; flex-shrink: 0; }
.art-sep     { opacity: 0.4; }

.no-results  { padding: 2rem; text-align: center; color: var(--faint); font-family: 'DM Mono', monospace; font-size: 12px; }
</style>
</head>
<body>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="wiki.php" onclick="resetFilters();return false;">smiki</a>
      <div class="search-wrap flex-grow-1">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchInput" placeholder="Search articles…" oninput="filterArticles(this.value)">
      </div>
      <button class="theme-toggle" onclick="toggleTheme()" title="Toggle light/dark">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <a href="../forum/forum.php" class="hbtn primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Forum
      </a>
      <a href="../wiki/login.php" class="hbtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Account
      </a>
    </div>
  </div>
</header>

<div class="container-lg py-4">
  <div class="section-heading">Categories</div>
  <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-2 mb-4" id="catGrid"></div>

  <div class="section-heading mt-4">Articles</div>
  <div class="d-flex flex-column gap-1" id="articleList"></div>
</div>

<script>
/* ── THEME ── */
(function () {
  const saved = localStorage.getItem('smiki-theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();

function toggleTheme() {
  const html  = document.documentElement;
  const next  = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}

/* ── DATA ── */
/* Replace with: const categories = <?php echo json_encode($categories); ?>; */
const categories = [
  { id:'science',     name:'Science',     count:142, shade:'s1' },
  { id:'history',     name:'History',     count:98,  shade:'s2' },
  { id:'technology',  name:'Technology',  count:117, shade:'s3' },
  { id:'mathematics', name:'Mathematics', count:74,  shade:'s4' },
  { id:'philosophy',  name:'Philosophy',  count:63,  shade:'s5' },
  { id:'biology',     name:'Biology',     count:89,  shade:'s1' },
  { id:'geography',   name:'Geography',   count:55,  shade:'s2' },
  { id:'economics',   name:'Economics',   count:81,  shade:'s3' },
  { id:'arts',        name:'Arts',        count:47,  shade:'s4' },
  { id:'physics',     name:'Physics',     count:103, shade:'s5' },
];

/* Replace with: const articles = <?php echo json_encode($articles); ?>; */
const articles = [
  { tag:'Physics',     title:'Wave-Particle Duality and the Double-Slit Experiment', excerpt:'How a single experiment upended classical mechanics and revealed the probabilistic nature of light and matter.',        date:'Today',      read:'9 min',  cat:'physics'     },
  { tag:'Mathematics', title:"Gödel's Incompleteness Theorems Explained",            excerpt:'A tour through the proofs that forever changed the limits of formal mathematical systems and what truth means.',         date:'Today',      read:'12 min', cat:'mathematics' },
  { tag:'History',     title:'The Silk Road: Commerce, Culture & Contagion',         excerpt:"Tracing the ancient trade network that connected civilisations from Chang'an to Constantinople over centuries.",        date:'Yesterday',  read:'7 min',  cat:'history'     },
  { tag:'Biology',     title:'Mitochondria: Beyond the Powerhouse',                  excerpt:"Recent research reveals the organelle's roles in immune signalling and programmed cell death — far beyond energy.",      date:'2 days ago', read:'8 min',  cat:'biology'     },
  { tag:'Technology',  title:'How Large Language Models Actually Work',               excerpt:'Attention mechanisms, tokenisation, and training — a ground-up explanation of the transformer architecture.',          date:'2 days ago', read:'15 min', cat:'technology'  },
  { tag:'Philosophy',  title:'Free Will in the Age of Neuroscience',                 excerpt:'Can determinism and moral responsibility coexist? Compatibilist and incompatibilist positions surveyed.',               date:'3 days ago', read:'10 min', cat:'philosophy'  },
  { tag:'Geography',   title:"Plate Tectonics: The Engine of Earth's Surface",       excerpt:'From seafloor spreading to the supercontinent cycle — the slow, relentless motion that reshapes our world.',           date:'4 days ago', read:'6 min',  cat:'geography'   },
  { tag:'Economics',   title:'The Nash Equilibrium and Strategic Decision-Making',   excerpt:"Game theory's cornerstone concept and how it applies to markets, diplomacy, and everyday life.",                       date:'4 days ago', read:'8 min',  cat:'economics'   },
  { tag:'Science',     title:'Dark Matter: What We Know and Do Not Know',            excerpt:'Decades of observation point to invisible mass holding galaxies together — but its nature remains entirely unknown.',   date:'5 days ago', read:'11 min', cat:'science'     },
  { tag:'Arts',        title:'The Bauhaus and the Design Revolution It Sparked',     excerpt:'How a short-lived school in Weimar Germany permanently altered architecture, typography, and industrial design.',       date:'5 days ago', read:'7 min',  cat:'arts'        },
  { tag:'Physics',     title:'Special Relativity: Time Dilation for Beginners',     excerpt:"Einstein's 1905 paper reshaped our understanding of time, space, and simultaneity with elegant thought experiments.",  date:'6 days ago', read:'9 min',  cat:'physics'     },
  { tag:'History',     title:"The Byzantine Empire's Lasting Cultural Legacy",       excerpt:'Despite its fall in 1453, Byzantium shaped Orthodox Christianity, Roman law, and Renaissance art in lasting ways.',    date:'6 days ago', read:'8 min',  cat:'history'     },
];

/* ── STATE & RENDER ── */
let activeCat = null, searchTerm = '';

function renderCats() {
  document.getElementById('catGrid').innerHTML = categories.map(c => `
    <div class="col">
      <div class="cat-card${activeCat === c.id ? ' active' : ''}" onclick="setCat('${c.id}')">
        <div class="cat-img ${c.shade}">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--cat-icon)" stroke-width="1">
            <rect x="3" y="3" width="18" height="14" rx="2"/>
            <line x1="3" y1="21" x2="21" y2="21"/>
            <line x1="8" y1="17" x2="8" y2="21"/>
            <line x1="16" y1="17" x2="16" y2="21"/>
          </svg>
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
            <span>${a.date}</span><span class="art-sep">·</span><span>${a.read}</span>
          </div>
        </div>`).join('')
    : `<div class="no-results">No articles found.</div>`;
}

function setCat(id)          { activeCat = activeCat === id ? null : id; renderCats(); renderArticles(); }
function filterArticles(val) { searchTerm = val.toLowerCase(); renderArticles(); }
function resetFilters()      { activeCat = null; searchTerm = ''; document.getElementById('searchInput').value = ''; renderCats(); renderArticles(); }

renderCats();
renderArticles();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>