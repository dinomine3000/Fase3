<?php
// webapp/wiki/article.php

// TODO: Replace the array below with your DB query, e.g.:
// $article = $db->query("SELECT id, tag, title, created_at AS date, read_time AS `read`, cat, content FROM articles WHERE id = ?", [$_GET['id']])->fetch();

$article = [
  'id'      => 1,
  'tag'     => 'Physics',
  'parent'  => 'Science',
  'title'   => 'Wave-Particle Duality and the Double-Slit Experiment',
  'date'    => 'May 21, 2026 · 14:32',
  'read'    => '9 min',
  'cat'     => 'physics',
  'content' => <<<'MD'
## Introduction

Wave-particle duality is one of the most fascinating and counterintuitive concepts in modern physics. It describes how every quantum entity — whether electron, photon, or even molecule — exhibits both wave-like and particle-like properties depending on how it is observed.

## The Double-Slit Experiment

The classic demonstration of this duality is the **double-slit experiment**, first performed with light by Thomas Young in 1801. When a beam of light passes through two narrow slits, the resulting pattern on a screen shows alternating bands of light and dark — an *interference pattern* characteristic of waves.

```
Screen pattern:
  | |  ← bright bands (constructive interference)
    |  ← dark band (destructive interference)
  | |
```

The surprising result comes when we fire particles — say, electrons — one at a time. Even then, the interference pattern builds up over many individual impacts. Each electron seems to "go through both slits simultaneously" and interfere with itself.

## The Observer Effect

When physicists tried to detect *which* slit each electron passed through, the interference pattern vanished. The act of measurement forced the electron to behave as a particle, collapsing its wave function.

> "Anyone who is not shocked by quantum theory has not understood it."
> — Niels Bohr

## Mathematical Description

The probability of finding a particle at a given location is given by the square of its wave function `|ψ|²`. Between measurement events, the particle exists in a superposition of states described by:

```
ψ(x,t) = A·e^(i(kx − ωt))
```

where `k` is the wave number and `ω` is the angular frequency.

## Implications

Wave-particle duality is not a quirk of experimental setup — it reflects a fundamental property of nature. It underlies:

- The stability of atoms (electrons occupy standing-wave orbitals)
- The operation of electron microscopes
- The principles behind quantum computing

The double-slit experiment remains the clearest window into the strange reality that quantum mechanics describes.
MD,
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($article['title']); ?> — Portal Wiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css">
<link rel="stylesheet" href="styles/article.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="wiki.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div class="search-wrap flex-grow-1">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search articles…">
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
  <div class="art-nav mb-4">
    <a href="wiki.php" class="art-back">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Articles
    </a>
    <a href="article-editor.php?id=<?php echo (int)$article['id']; ?>" class="hbtn art-edit-btn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Edit
    </a>
  </div>

  <div class="art-header">
    <div class="art-header-meta">
      <span class="art-tag art-header-tag"><?php echo htmlspecialchars($article['tag']); ?></span>
      <?php if (!empty($article['parent'])): ?>
      <span class="art-meta-cat"><?php echo htmlspecialchars($article['parent']); ?></span>
      <?php endif; ?>
      <span class="art-meta-item"><?php echo htmlspecialchars($article['date']); ?></span>
    </div>
    <h1 class="art-h1"><?php echo htmlspecialchars($article['title']); ?></h1>
  </div>
  <div class="article-body" id="articleBody"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
const article = <?php echo json_encode($article); ?>;
</script>
<script src="js/article.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
