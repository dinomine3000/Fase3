# CLAUDE.md — Smiki Frontend Design System

This file governs all frontend work on the Smiki PHP project. Follow every rule here without being asked. Do not introduce new patterns unless the existing ones cannot solve the problem.

---

## Project Structure

```
webapp/
  index.php              ← auth gate — redirects to home.php when logged in
  home.php               ← main landing page: DB-backed category grid + role-gated action cards
  auth/
    formLogin.php        ← login form
    formSignUp.php       ← signup form (captcha preserved as-is)
    processFormLogin.php ← handles login POST
    processFormSignUp.php← handles signup POST
    emailVerification.php
    emailLink.php
    logout.php
    forms.js
  captcha/               ← captcha handling
  files/
    list.php             ← image/file browser grid (DB-backed, organizer upload gate)
    formUpload.php       ← upload form (organizer-gated, POST → processFormUpload.php)
    processFormUpload.php← handles file upload POST
    showFileThumb.php    ← serves image thumbnails by ?id=
    getFileContents.php  ← serves raw file downloads by ?id=
    config.php           ← files module config
  foruns/                ← forum (note spelling: "foruns", not "forum")
    forum.php            ← forum main page
    forum-embed.php      ← embedded forum content (filter buttons + JS entry points)
    createDiscussions.php
    createPost.php
    getDiscussions.php
    getPost.php
    likes.php
    js/
      composer.js        ← new discussion form logic (injected HTML)
      discussions.js     ← discussion list + post stream logic (injected HTML)
    styles/
      composer.css       ← composer form styles (Smiki tokens)
      discussions.css    ← discussion/post styles (Smiki tokens)
  wiki/
    header.php           ← shared header partial (see Header section below)
    viewPage.php         ← DB-backed wiki browser (4 URL states — see below)
    editPage.php         ← edit wiki page (referenced from viewPage article view)
    create.php           ← create wiki page (editor+ role, POST → processCreatePage.php)
    processCreatePage.php
    processEditPage.php
    getSecondaryCategories.php ← JSON API for secondary-category AJAX (used by create.php)
    search.php           ← DB-backed full-text search (categories, subcategories, pages, users)
    proposals.php        ← moderation queue (organizer role)
    manage_categories.php← category management (organizer/admin)
    profile.php          ← user profile page (own profile: bio edit + role/ban controls)
    processSearchWiki.php← JSON AJAX endpoint: search categories, subcategories, pages
    processSearchUsers.php← JSON AJAX endpoint: search users by name
    js/
      profile.js         ← bio edit toggle + insertBio() markdown shortcuts (own profile only)
      search.js          ← legacy; superseded by ajaxHandler.js for autocomplete
    styles/
      wiki.css           ← design system foundation: tokens + all shared components
      viewpage.css       ← wiki browser multi-state styles
      form.css           ← shared form/input styles (create, editPage, manage_categories, profile, formUpload)
      proposals.css      ← moderation queue card styles
      home.css           ← home.php welcome block + action card grid
      search.css         ← search results page styles
      profile.css        ← profile hero, avatar, stats, bio card, find-user
    img/
  scripts/
    ajaxHandler.js       ← shared AJAX: updateSecondaryCategories, searchUsers,
                            searchWiki, searchAllHeader, escHtml
    category.js          ← (legacy alias path — real file is scripts/ajaxHandler.js)
```

**viewPage.php URL states:**
1. `?` — primary category cards grid
2. `?primaryCategory=X` — secondary category banners
3. `?primaryCategory=X&secondaryCategory=Y` — pages list (article bars)
4. `?pageTitle=X` — article reader (Markdown rendered server-side via ExtendedParsedown)

**Key rules:**
- `home.php` is the real landing page — it shows DB-backed categories and role-gated action cards.
- `index.php` is only the auth gate; it does not render content.
- The forum folder is `foruns/` (Portuguese spelling) — never write `forum/`.
- Backend includes (`../../Lib/lib.php`, `../../Lib/wikiLib.php`, `../../Lib/db.php`) live two levels up from `wiki/`. Auth-required pages call `authorizeUserByLevel()` from that lib.
- Static assets use `?v=3` query-string versioning for cache busting (e.g. `wiki.css?v=3`). Bump the number when modifying any shared CSS or JS file.

---

## Stack

- **PHP** (`.php` files). HTML written directly; dynamic data injected with `<?php echo ... ?>`.
- **Bootstrap 5.3** via CDN — grid and spacing utilities only. Do not use Bootstrap components (`.card`, `.btn`, `.badge`, `.navbar`, modals, dropdowns). All components are custom.
- **Plus Jakarta Sans** (300, 400, 500, 600) — all body and UI text.
- **Outfit** (400, 500) — tags, meta, section headings, counts. Loaded from Google Fonts.
- **Vanilla JS only.** No frameworks, no npm, no build step.
- **Markdown rendering** — article content stored as Markdown and rendered server-side via `ExtendedParsedown` in `viewPage.php`. Also used for user bio in `profile.php`.

---

## Theming — Light & Dark Mode

The theme system uses `data-theme` on `<html>`. Both themes are defined as CSS custom property sets. JS reads from and writes to `localStorage` under the key `smiki-theme`.

**Always apply the theme before paint** — the IIFE must be the first `<script>` block in `<body>`, before the `header.php` include:

```html
<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>
<?php include('./header.php') ?>
```

**`toggleTheme()` is defined inline** on every page (no shared JS file for it):
```js
function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}
```

The toggle button shows a **moon icon in light mode** and a **sun icon in dark mode**:
```css
.icon-sun                { display: none;  }
.icon-moon               { display: block; }
[data-theme="dark"] .icon-sun  { display: block; }
[data-theme="dark"] .icon-moon { display: none;  }
```

---

## Color Tokens

Always use CSS custom properties. Never hardcode hex values in component styles.

### Light theme (`[data-theme="light"]`)
```css
--bg:      #f7f6f3;
--bg2:     #ffffff;
--bg3:     #efefec;
--border:  rgba(0,0,0,0.08);
--border2: rgba(0,0,0,0.18);
--text:    #1c1c1a;
--muted:   #6b6b67;
--faint:   #b0afa9;
--accent:  #1f7bc4;
--accent2: #185fa3;
--shadow:  rgba(0,0,0,0.06);
--cat-s1 … --cat-s5:  subtle warm/cool tints (#eef0eb … #f0efeb)
--cat-icon: #ccc;
```

### Dark theme (`[data-theme="dark"]`)
```css
--bg:      #111214;
--bg2:     #18191c;
--bg3:     #1f2023;
--border:  rgba(255,255,255,0.07);
--border2: rgba(255,255,255,0.16);
--text:    #e2e2e0;
--muted:   #888880;
--faint:   #444440;
--accent:  #4aaee8;
--accent2: #5dc0f7;
--shadow:  rgba(0,0,0,0.25);
--cat-s1 … --cat-s5:  dark tints (#181c18 … #1a1c18)
--cat-icon: #333;
```

**Accent discipline:** `--accent` appears only on: the logo, active card border, article tags, and the primary header button. Do not add blue decoratively.

**All components must work in both themes.** Any new component must define its colors using only the tokens above.

---

## Typography

| Use case | Font | Size | Weight | Color |
|---|---|---|---|---|
| Body text | Plus Jakarta Sans | 15px | 400 | `--text` |
| Article title | Plus Jakarta Sans | 14px | 600 | `--text` |
| Article excerpt | Plus Jakarta Sans | 12px | 400 | `--muted` |
| Section headings | Plus Jakarta Sans | 17px | 600 | `--text` |
| Labels (Outfit mono) | Outfit | 11px, 0.08em tracking, uppercase | 500 | `--faint` |
| Tags / badges | Outfit | 10px, uppercase | 400 | `--accent` |
| Meta (date, read time) | Outfit | 10px | 400 | `--faint` |
| Logo | Plus Jakarta Sans | 1.05rem | 700 | `--accent` |
| Buttons | Plus Jakarta Sans | 13px | 500 | varies |

Letter-spacing: restrained — 0.08em max for mono labels, -0.01em on logo. No wide tracking in body copy.

---

## Layout

- **Max content width:** Bootstrap `container-lg` (~1140px), centered, `padding: 0 2rem`.
- **Page padding:** `py-4` inside the container.
- **Sticky header:** `position: sticky; top: 0; z-index: 100;` Height exactly `56px`.
- All `background`, `color`, and `border-color` properties that vary by theme must include `transition: .25s` so theme switching is smooth.

---

## Header

### Shared partial: `wiki/header.php`

Most pages use `wiki/header.php` as a shared include rather than inlining the header HTML. It handles session detection, login state, and renders the full header.

**Include pattern** (path is relative to the including file):
```php
// From wiki/ pages:
<?php include('./header.php') ?>

// From home.php:
<?php include('./wiki/header.php') ?>

// From auth/ pages (uses absolute server path):
<?php include('/works/webapp/wiki/header.php') ?>
```

`header.php` uses **absolute `/works/webapp/` paths** for all internal navigation links — this is the deployment convention for this server. Do not change navigation links to relative paths inside `header.php`.

The `searchAllHeader` call in `header.php` hardcodes `basePath='wiki/'` which routes AJAX to `/works/webapp/wiki/processSearchWiki.php` and `/works/webapp/wiki/processSearchUsers.php`.

### Header structure (left → right)

1. **Logo** — `<a class="logo" href="/works/webapp/home.php">Portal <span class="logo-wiki">Wiki</span></a>`
2. **Search** — `.search-outer` (flex-grow) wrapping `.search-wrap` + `#hdr-suggest .search-suggest` dropdown
3. **Theme toggle** — `.theme-toggle`, icon-only, 36×36px
4. **Primary action** — `.hbtn.primary`: Forum button (links to `/works/webapp/foruns/forum.php`)
5. **Profile** — `.hbtn.icon` (logged-in only): links to profile.php
6. **Logout / Login** — `.hbtn.icon` logout form (logged-in) or `.hbtn` Login link (logged-out)

```html
<!-- search area in header.php -->
<div class="search-outer">
  <div class="search-wrap">
    <svg …></svg>
    <input type="text" placeholder="Search wiki…"
           oninput="searchAllHeader(this.value,'hdr-suggest','wiki/')"
           onblur="setTimeout(()=>{let s=document.getElementById('hdr-suggest');if(s){s.innerHTML='';s.classList.remove('has-results');}},150)"
           onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='/works/webapp/wiki/search.php?q='+encodeURIComponent(this.value.trim())">
  </div>
  <div id="hdr-suggest" class="search-suggest"></div>
</div>
```

### Pages with custom inline headers (do NOT use `header.php`)

These pages have a distinct header layout and must keep their inline `<header class="site-header">` HTML:

| Page | Header contents |
|------|----------------|
| `files/list.php` | Logo · spacer · Go Back · theme toggle · profile icon · logout icon |
| `files/formUpload.php` | Logo · spacer · Go Back · theme toggle · profile icon · logout icon |
| `foruns/forum.php` | Logo · search-outer · theme toggle · Wiki (primary) · profile icon · logout/login |

Rules:
- Never more than one `.hbtn.primary` in the header.
- Never duplicate a navigation destination.
- The theme toggle always comes immediately before the primary button (or profile icon if no primary).
- `toggleTheme()` is **always defined inline** on the page — it is not in `header.php`.

---

## Component: Section Heading

```html
<div class="section-heading">Categories</div>
```

Single short label only. No sub-labels, no icons, no explanatory text beneath.

---

## Component: Category Card

Grid: `row-cols-2 row-cols-sm-3 row-cols-md-5`, gap `g-2`.

```html
<a href="viewPage.php?primaryCategory=Name" style="text-decoration:none;display:block">
  <div class="cat-card">
    <div class="cat-img s1">  <!-- s1–s5 shade variant -->
      <!-- placeholder SVG with stroke="var(--cat-icon)" or <img> -->
    </div>
    <div class="cat-body">
      <div class="cat-label">Name</div>
    </div>
  </div>
</a>
```

- Active state: `border-color: var(--accent)`, label color: `var(--accent)`.
- Placeholder SVG uses `stroke="var(--cat-icon)"` so it adapts to both themes.

---

## Component: Article Bar

```html
<div class="article-bar">
  <span class="art-tag">Tag</span>
  <div class="art-body">
    <div class="art-title">Title</div>
    <div class="art-excerpt">Excerpt</div>
  </div>
  <div class="art-meta">
    <span>2 days ago</span><span class="art-sep">·</span><span>8 min</span>
  </div>
</div>
```

---

## Component: Buttons

**Ghost (default):**
```css
.hbtn { height:36px; padding:0 15px; border-radius:7px; font-size:13px; font-weight:500;
        border:1px solid var(--border); background:none; color:var(--muted); }
.hbtn:hover { border-color:var(--border2); color:var(--text); background:var(--bg3); }
```

**Primary:**
```css
.hbtn.primary { background:var(--accent); border-color:var(--accent); color:#fff; }
.hbtn.primary:hover { background:var(--accent2); border-color:var(--accent2); }
```

**Icon-only:**
```css
.hbtn.icon { width:36px; padding:0; justify-content:center; }
```

- Use `<a class="hbtn">` with `style="text-decoration:none"` for page links.
- Use `<button class="hbtn">` for in-page actions.
- Regular buttons: always include a 14×14 SVG icon on the left.
- `.hbtn.icon`: SVG only, no label. Used for profile and logout in the header.
- The theme toggle has no label and uses `.theme-toggle` (not `.hbtn`).

---

## Component: Header Autocomplete Dropdown

The search bar uses a two-level wrapper. CSS is in `wiki/styles/wiki.css`:

```html
<div class="search-outer">          <!-- flex:1 1 auto; min-width:0; position:relative -->
  <div class="search-wrap">         <!-- 100% width, 36px tall -->
    <svg …></svg>
    <input …>
  </div>
  <div id="hdr-suggest" class="search-suggest"></div>   <!-- hidden until .has-results -->
</div>
```

Rendered by `searchAllHeader()` in `ajaxHandler.js`. Groups: Topics, Subtopics, Pages, Users — max 2 per group. Class names: `.suggest-group-label`, `.suggest-item`, `.suggest-item-type`, `.suggest-item-name`, `.suggest-item-meta`.

---

## JS Conventions

- **Theme IIFE runs first** — inline `<script>` as the first element in `<body>`, before the `header.php` include.
- **`toggleTheme()` is defined inline** on every page in a `<script>` block — there is no shared JS file for it.
- **DB-backed pages render server-side.** Client JS is minimal: search autocomplete, theme toggle, forum fetch calls.
- **`scripts/ajaxHandler.js`** — shared script loaded on all pages with live search. Contains:
  - `updateSecondaryCategories()` — populates secondary category `<select>` on create.php
  - `searchUsers(query)` — profile page find-user autocomplete (uses `#autocomplete-suggestions`, shared `xmlHttp` global — do not touch)
  - `escHtml(str)` — HTML-escaping helper
  - `searchWiki(query, basePath, callback)` — hits `processSearchWiki.php`, uses its own local XHR
  - `searchAllHeader(query, containerId, basePath)` — fires parallel wiki + users XHRs, renders grouped dropdown
- **`wiki/js/profile.js`** — loaded only on `profile.php` when viewing own profile. Handles bio edit form show/hide and `insertBio()` markdown shortcuts targeting `#bio-content`.
- **`wiki/js/search.js`** — legacy file; contains duplicate `toggleTheme()` and an Enter-key listener. Effectively superseded by the `oninput`/`onblur` handlers on the search input. Do not add new logic here.
- The forum JS files (`discussions.js`, `composer.js`) inject HTML strings with class names defined in `styles/discussions.css` and `styles/composer.css` — do not rename those classes without updating both files.
- Script load order: theme IIFE → `header.php` include → inline data (if any) → page JS → `ajaxHandler.js` → Bootstrap bundle.

---

## What Not To Do

- Do not use Bootstrap's `.card`, `.btn`, `.badge`, `.navbar` — custom components only.
- Do not add gradients, `box-shadow` beyond hover subtlety, or animations beyond `transition`.
- Do not introduce a second accent color.
- Do not add section sub-labels or descriptive text under headings.
- Do not use emoji as UI elements — SVG icons only.
- Do not duplicate navigation (if a link is in the header, it is nowhere else on the same page).
- Do not hardcode hex colors in component rules — always use the token variables.
- Do not forget to test new components in both light and dark themes.
- Do not write forum paths as `../forum/forum.php` — the folder is `foruns/`.
- Do not link the logo to `wiki.php` — it no longer exists. Logo always links to `home.php`.
- Do not use relative paths in `header.php` — it uses absolute `/works/webapp/` paths.
- Do not add shared JS/CSS files without bumping the `?v=N` cache-buster on every reference.
- Do not inline the header HTML on pages that use `header.php` — and conversely, do not add `header.php` includes to `files/formUpload.php` or `foruns/forum.php` which have custom headers.

---

## Internacionalização (i18n) — PT / EN

### Como funciona

O sistema usa `Lib/lang/translator.php`, que:
1. Lê `$_GET['lang']` e guarda em `$_SESSION['lang']` se for `'en'` ou `'pt'`
2. Carrega o ficheiro de língua correspondente (`Lib/lang/en.php` ou `Lib/lang/pt.php`)
3. Define a função `lang($key)` — devolve a tradução escapada com `htmlspecialchars`

A língua padrão é `'en'`. A escolha persiste na sessão.

### Como usar em PHP

```php
echo lang('categories');          // numa expressão HTML normal
placeholder="<?php echo lang('search_wiki'); ?>"  // em atributos
```

### Disponibilidade do `lang()` em cada página

| Situação | O que fazer |
|---|---|
| Página usa `wiki/header.php` | `lang()` fica disponível após o include do header |
| Página tem header inline (`formUpload.php`, `forum.php`) | Adicionar `include_once("../../Lib/lang/translator.php")` no topo do bloco PHP |
| Texto gerado **antes** do include do header (ex: mensagens de erro em `manage_categories.php`, `profile.php`) | Adicionar `include_once("../../Lib/lang/translator.php")` explicitamente antes de usar `lang()` |

O `include_once` garante que o translator não é carregado duas vezes mesmo que `header.php` também o inclua.

### Lang toggle no header

O toggle mostra a língua PARA A QUAL se vai mudar (ex: mostra "PT" quando estás em EN).

**Páginas com `header.php`** — o toggle já está lá. É calculado no bloco PHP de `header.php`:
```php
$_langSwitch = ($current_lang === 'en') ? 'pt' : 'en';
$_qp = $_GET; $_qp['lang'] = $_langSwitch;
$_langToggleUrl = '?' . http_build_query($_qp);
```

**Páginas com header inline** — calcular o mesmo bloco PHP no topo e inserir o elemento antes do `.theme-toggle`:
```html
<a href="<?= $_langToggleUrl ?>" class="lang-toggle" title="<?= lang('switch_language') ?>" style="text-decoration:none"><?= strtoupper($_langSwitch) ?></a>
```

**Ordem obrigatória no header:** Logo → Search → Lang toggle → Theme toggle → Primary button → Profile → Logout/Login

O `.lang-toggle` está definido em `wiki/styles/wiki.css`.

### Adicionar novas chaves de tradução

1. Adicionar a chave a **`Lib/lang/en.php`** (inglês)
2. Adicionar a mesma chave a **`Lib/lang/pt.php`** (português europeu)
3. Usar `lang('chave')` no PHP

**Nunca adicionar uma chave a um ficheiro sem adicionar ao outro.** Se uma chave não existir, `lang()` devolve a própria chave como fallback.
