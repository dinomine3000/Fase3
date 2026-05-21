<?php
// webapp/wiki/category.php

// TODO: Replace the arrays below with your DB queries, e.g.:
// $category      = $db->query("SELECT id, name, count, shade FROM categories WHERE id = ?", [$_GET['id']])->fetch();
// $subcategories = $db->query("SELECT id, name FROM categories WHERE parent_id = ?", [$_GET['id']])->fetchAll();
// $articles      = $db->query("SELECT tag, title, excerpt, created_at AS date, read_time AS `read`, subcat FROM articles WHERE cat = ?", [$_GET['id']])->fetchAll();

$category = ['id'=>'physics', 'name'=>'Physics', 'count'=>103, 'shade'=>'s5'];

$subcategories = [
  ['id'=>'classical',  'name'=>'Classical Mechanics'],
  ['id'=>'quantum',    'name'=>'Quantum Mechanics'  ],
  ['id'=>'relativity', 'name'=>'Relativity'         ],
  ['id'=>'optics',     'name'=>'Optics'             ],
  ['id'=>'thermo',     'name'=>'Thermodynamics'     ],
];

$articles = [
  ['tag'=>'Physics', 'title'=>'Wave-Particle Duality and the Double-Slit Experiment',  'excerpt'=>'How a single experiment upended classical mechanics and revealed the probabilistic nature of light and matter.', 'date'=>'Today',       'read'=>'9 min',  'subcat'=>'quantum'   ],
  ['tag'=>'Physics', 'title'=>'Special Relativity: Time Dilation for Beginners',       'excerpt'=>"Einstein's 1905 paper reshaped our understanding of time, space, and simultaneity.",                            'date'=>'6 days ago',  'read'=>'9 min',  'subcat'=>'relativity'],
  ['tag'=>'Physics', 'title'=>"Newton's Laws of Motion and Their Modern Applications", 'excerpt'=>'From the apple to orbital mechanics — how the three laws underpin everything in classical physics.',           'date'=>'1 week ago',  'read'=>'7 min',  'subcat'=>'classical' ],
  ['tag'=>'Physics', 'title'=>'The Photoelectric Effect and the Birth of Quantum Theory','excerpt'=>'How Einstein explained light quanta and why it earned him the Nobel Prize over relativity.',                  'date'=>'2 weeks ago', 'read'=>'8 min',  'subcat'=>'quantum'   ],
  ['tag'=>'Physics', 'title'=>'Thermodynamics: The Four Laws Explained',               'excerpt'=>"From absolute zero to entropy — a clear walkthrough of thermodynamics' foundational laws.",                   'date'=>'3 weeks ago', 'read'=>'10 min', 'subcat'=>'thermo'    ],
  ['tag'=>'Physics', 'title'=>"Snell's Law and the Behaviour of Light at Boundaries",  'excerpt'=>'Refraction, total internal reflection, and why your straw looks bent in water.',                              'date'=>'1 month ago', 'read'=>'5 min',  'subcat'=>'optics'    ],
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($category['name']); ?> — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css">
<link rel="stylesheet" href="styles/category.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="wiki.php">smiki</a>
      <div class="search-wrap flex-grow-1">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="searchInput" placeholder="Search in category…" oninput="filterArticles(this.value)">
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

<!-- Category hero strip -->
<div class="cat-hero <?php echo htmlspecialchars($category['shade']); ?>">
  <div class="container-lg">
    <div class="cat-hero-inner">
      <a href="wiki.php" class="cat-hero-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      </a>
      <div>
        <div class="cat-hero-name"><?php echo htmlspecialchars($category['name']); ?></div>
        <div class="cat-hero-count"><?php echo (int)$category['count']; ?> articles</div>
      </div>
      <a href="article-editor.php" class="hbtn cat-hero-edit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New article
      </a>
    </div>
  </div>
</div>

<div class="container-lg py-4">
  <!-- Subcategory chips -->
  <div class="subcat-row" id="subcatRow"></div>

  <div class="section-heading mt-3">Articles</div>
  <div class="d-flex flex-column gap-1" id="articleList"></div>
</div>

<script>
const category      = <?php echo json_encode($category); ?>;
const subcategories = <?php echo json_encode($subcategories); ?>;
const articles      = <?php echo json_encode($articles); ?>;
</script>
<script src="js/category.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
