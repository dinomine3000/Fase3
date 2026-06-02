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
    viewPage.php         ← DB-backed wiki browser (4 URL states — see below)
    editPage.php         ← edit wiki page (referenced from viewPage article view)
    create.php           ← create wiki page (organizer role, POST → processCreatePage.php)
    processCreatePage.php
    processEditPage.php
    getSecondaryCategories.php ← JSON API for secondary-category AJAX (used by create.php)
    search.php           ← full-text search (placeholder data, real filter logic)
    proposals.php        ← moderation queue (organizer role)
    manage_categories.php← category management (organizer/admin)
    js/
      search.js          ← search page keyboard handler
    styles/
      wiki.css           ← design system foundation: tokens + all shared components
      viewpage.css       ← wiki browser multi-state styles
      form.css           ← shared form/input styles (create.php, manage_categories.php)
      proposals.css      ← moderation queue card styles
      home.css           ← home.php welcome block + action card grid
      search.css         ← search results page styles
    img/
  scripts/
    category.js          ← AJAX secondary-category loader (used by create.php)
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
- Backend includes (`../../Lib/lib.php`, `../../Lib/extendedParsedown.php`) live two levels up from `wiki/`. Auth-required pages call `authorizeUserByLevel()` from that lib.

**Navigation map:**
- Logo (all pages) → `home.php` (or `../home.php` from subdirs)
- Last header button (wiki/forum/files pages) → Login → `auth/formLogin.php`
- `home.php` last button → Logout (form POST to `auth/logout.php`)
- Auth pages → logo + theme toggle only, no other header buttons
- Forum → `foruns/forum.php`; wiki browsing → `wiki/viewPage.php`

---

## Stack

- **PHP** (`.php` files). HTML written directly; dynamic data injected with `<?php echo ... ?>`.
- **Bootstrap 5.3** via CDN — grid and spacing utilities only. Do not use Bootstrap components (`.card`, `.btn`, `.badge`, `.navbar`, modals, dropdowns). All components are custom.
- **Plus Jakarta Sans** (300, 400, 500, 600) — all body and UI text.
- **Outfit** (400, 500) — tags, meta, section headings, counts. Loaded from Google Fonts.
- **Vanilla JS only.** No frameworks, no npm, no build step.
- **Markdown rendering** — article content stored as Markdown and rendered server-side via `ExtendedParsedown` in `viewPage.php`.

---

## Theming — Light & Dark Mode

The theme system uses `data-theme` on `<html>`. Both themes are defined as CSS custom property sets. JS reads from and writes to `localStorage` under the key `smiki-theme`.

**Always apply the theme before paint** — the IIFE must be the first `<script>` block in `<body>`:

```js
(function () {
  const saved = localStorage.getItem('smiki-theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();
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

Fixed structure, left → right:

1. **Logo** — `<a class="logo" href="../home.php">Portal <span class="logo-wiki">Wiki</span></a>`
2. **Search** — `.search-wrap`, `flex-grow-1`. On Enter navigates to `search.php?q=...` (relative from wiki/ pages) or `wiki/search.php?q=...` (from home.php).
3. **Theme toggle** — `.theme-toggle`, icon-only, 36×36px square button
4. **Primary action** — `.hbtn.primary`: Forum on wiki pages, Wiki on forum page
5. **Login** — `.hbtn` (ghost), links to `../auth/formLogin.php`

```html
<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="../home.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div class="search-wrap flex-grow-1">
        <svg width="13" height="13" …><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search wiki…"
               onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='search.php?q='+encodeURIComponent(this.value.trim())">
      </div>
      <button class="theme-toggle" onclick="toggleTheme()">…</button>
      <a href="../foruns/forum.php" class="hbtn primary" style="text-decoration:none">…Forum</a>
      <a href="../auth/formLogin.php" class="hbtn" style="text-decoration:none">…Login</a>
    </div>
  </div>
</header>
```

**Exceptions:**
- `home.php`: logo href is `home.php`; primary = Forum (`foruns/forum.php`); last button = Logout (form POST to `auth/logout.php`)
- `foruns/forum.php`: primary = Wiki (`../wiki/viewPage.php`); last = Login (`../auth/formLogin.php`)
- `auth/formLogin.php`, `auth/formSignUp.php`: logo + theme toggle only — no search, no action buttons
- `files/list.php`: logo + theme toggle + Upload (organizer only) + Go Back — no search, no forum

Rules:
- Never more than one `.hbtn.primary` in the header.
- Never duplicate a navigation destination.
- The theme toggle always comes immediately before the primary button.

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

- Use `<a class="hbtn">` with `style="text-decoration:none"` for page links.
- Use `<button class="hbtn">` for in-page actions.
- Always include a 14×14 SVG icon on the left. The theme toggle has no label.

---

## JS Conventions

- **Theme IIFE runs first** — inline `<script>` as the first element in `<body>`.
- **`toggleTheme()` is defined inline** on every page in a `<script>` block — there is no shared JS file for it.
- **DB-backed pages render server-side.** Client JS is minimal: search key handler, theme toggle, forum fetch calls (discussions.js / composer.js).
- The forum JS files (`discussions.js`, `composer.js`) inject HTML strings with class names defined in `styles/discussions.css` and `styles/composer.css` — do not rename those classes without updating both files.
- Script load order: theme IIFE → inline data (if any) → page JS → Bootstrap bundle.
- Wiki CSS/JS live in `wiki/styles/` and `wiki/js/`. Paths in tags are relative.

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
