<?php
// webapp/wiki/wiki.php

// TODO: Replace the arrays below with your DB queries, e.g.:
// $categories = $db->query("SELECT id, name, shade FROM categories ORDER BY name")->fetchAll();
// $articles   = $db->query("SELECT tag, title, excerpt, created_at AS date, read_time AS `read`, cat FROM articles ORDER BY created_at DESC")->fetchAll();

$categories = [
  ['id'=>'science',     'name'=>'Science',     'shade'=>'s1', 'img'=>'img/davie.png'],
  ['id'=>'history',     'name'=>'History',     'shade'=>'s2', 'img'=>'img/davie.png'],
  ['id'=>'technology',  'name'=>'Technology',  'shade'=>'s3', 'img'=>'img/davie.png'],
  ['id'=>'mathematics', 'name'=>'Mathematics', 'shade'=>'s4', 'img'=>'img/davie.png'],
  ['id'=>'philosophy',  'name'=>'Philosophy',  'shade'=>'s5', 'img'=>'img/davie.png'],
  ['id'=>'biology',     'name'=>'Biology',     'shade'=>'s1', 'img'=>'img/davie.png'],
  ['id'=>'geography',   'name'=>'Geography',   'shade'=>'s2', 'img'=>'img/davie.png'],
  ['id'=>'economics',   'name'=>'Economics',   'shade'=>'s3', 'img'=>'img/davie.png'],
  ['id'=>'arts',        'name'=>'Arts',        'shade'=>'s4', 'img'=>'img/davie.png'],
  ['id'=>'physics',     'name'=>'Physics',     'shade'=>'s5', 'img'=>'img/davie.png'],
];

$articles = [
  ['tag'=>'Physics',     'path'=>'Science › Physics',     'title'=>'Wave-Particle Duality and the Double-Slit Experiment', 'excerpt'=>'How a single experiment upended classical mechanics and revealed the probabilistic nature of light and matter.',       'date'=>'Today',      'read'=>'9 min',  'cat'=>'physics'    ],
  ['tag'=>'Mathematics', 'path'=>'Science › Mathematics', 'title'=>"Gödel's Incompleteness Theorems Explained",            'excerpt'=>'A tour through the proofs that forever changed the limits of formal mathematical systems and what truth means.',        'date'=>'Today',      'read'=>'12 min', 'cat'=>'mathematics'],
  ['tag'=>'History',     'path'=>'History',               'title'=>'The Silk Road: Commerce, Culture & Contagion',         'excerpt'=>"Tracing the ancient trade network that connected civilisations from Chang'an to Constantinople over centuries.",       'date'=>'Yesterday',  'read'=>'7 min',  'cat'=>'history'    ],
  ['tag'=>'Biology',     'path'=>'Science › Biology',     'title'=>'Mitochondria: Beyond the Powerhouse',                  'excerpt'=>"Recent research reveals the organelle's roles in immune signalling and programmed cell death.",                         'date'=>'2 days ago', 'read'=>'8 min',  'cat'=>'biology'    ],
  ['tag'=>'Technology',  'path'=>'Technology',            'title'=>'How Large Language Models Actually Work',               'excerpt'=>'Attention mechanisms, tokenisation, and training — a ground-up explanation of the transformer architecture.',          'date'=>'2 days ago', 'read'=>'15 min', 'cat'=>'technology' ],
  ['tag'=>'Philosophy',  'path'=>'Philosophy',            'title'=>'Free Will in the Age of Neuroscience',                 'excerpt'=>'Can determinism and moral responsibility coexist? Compatibilist and incompatibilist positions surveyed.',              'date'=>'3 days ago', 'read'=>'10 min', 'cat'=>'philosophy' ],
  ['tag'=>'Geography',   'path'=>'Geography',             'title'=>"Plate Tectonics: The Engine of Earth's Surface",       'excerpt'=>'From seafloor spreading to the supercontinent cycle — the slow, relentless motion that reshapes our world.',           'date'=>'4 days ago', 'read'=>'6 min',  'cat'=>'geography'  ],
  ['tag'=>'Economics',   'path'=>'Economics',             'title'=>'The Nash Equilibrium and Strategic Decision-Making',   'excerpt'=>"Game theory's cornerstone concept and how it applies to markets, diplomacy, and everyday life.",                      'date'=>'4 days ago', 'read'=>'8 min',  'cat'=>'economics'  ],
  ['tag'=>'Science',     'path'=>'Science',               'title'=>'Dark Matter: What We Know and Do Not Know',            'excerpt'=>'Decades of observation point to invisible mass holding galaxies together — but its nature remains entirely unknown.',  'date'=>'5 days ago', 'read'=>'11 min', 'cat'=>'science'    ],
  ['tag'=>'Arts',        'path'=>'Arts',                  'title'=>'The Bauhaus and the Design Revolution It Sparked',     'excerpt'=>'How a short-lived school in Weimar Germany permanently altered architecture, typography, and industrial design.',      'date'=>'5 days ago', 'read'=>'7 min',  'cat'=>'arts'       ],
  ['tag'=>'Physics',     'path'=>'Science › Physics',     'title'=>'Special Relativity: Time Dilation for Beginners',     'excerpt'=>"Einstein's 1905 paper reshaped our understanding of time, space, and simultaneity with elegant thought experiments.", 'date'=>'6 days ago', 'read'=>'9 min',  'cat'=>'physics'    ],
  ['tag'=>'History',     'path'=>'History',               'title'=>"The Byzantine Empire's Lasting Cultural Legacy",       'excerpt'=>'Despite its fall in 1453, Byzantium shaped Orthodox Christianity, Roman law, and Renaissance art in lasting ways.',   'date'=>'6 days ago', 'read'=>'8 min',  'cat'=>'history'    ],
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Wiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="wiki.php" onclick="resetFilters();return false;">Portal <span class="logo-wiki">Wiki</span></a>
      <div class="search-wrap flex-grow-1">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchInput" placeholder="Search wiki…" oninput="filterArticles(this.value)" onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='search.php?q='+encodeURIComponent(this.value.trim())">
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
const categories = <?php echo json_encode($categories); ?>;
const articles   = <?php echo json_encode($articles); ?>;
</script>
<script src="js/wiki.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
