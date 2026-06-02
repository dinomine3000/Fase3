<?php
// webapp/wiki/search.php

// TODO: Replace arrays below with DB queries, e.g.:
// $categories    = $db->query("SELECT id, name, shade, img FROM categories ORDER BY name")->fetchAll();
// $subcategories = $db->query("SELECT s.id, s.name, s.description, c.id AS cat_id, c.name AS cat_name FROM subcategories s JOIN categories c ON c.id = s.cat_id ORDER BY s.name")->fetchAll();
// $articles      = $db->query("SELECT a.tag, a.title, a.excerpt, a.created_at AS date, a.read_time AS `read`, c.name AS cat_name, COALESCE(s.name, c.name) AS path FROM articles a LEFT JOIN categories c ON c.id = a.cat LEFT JOIN subcategories s ON s.id = a.subcat ORDER BY a.created_at DESC")->fetchAll();

$categories = [
  ['id'=>'science',     'name'=>'Science',     'shade'=>'s1'],
  ['id'=>'history',     'name'=>'History',     'shade'=>'s2'],
  ['id'=>'technology',  'name'=>'Technology',  'shade'=>'s3'],
  ['id'=>'mathematics', 'name'=>'Mathematics', 'shade'=>'s4'],
  ['id'=>'philosophy',  'name'=>'Philosophy',  'shade'=>'s5'],
  ['id'=>'biology',     'name'=>'Biology',     'shade'=>'s1'],
  ['id'=>'geography',   'name'=>'Geography',   'shade'=>'s2'],
  ['id'=>'economics',   'name'=>'Economics',   'shade'=>'s3'],
  ['id'=>'arts',        'name'=>'Arts',        'shade'=>'s4'],
  ['id'=>'physics',     'name'=>'Physics',     'shade'=>'s5'],
];

$subcategories = [
  ['id'=>'classical',     'name'=>'Classical Mechanics',   'description'=>'Motion, forces, and energy in macroscopic systems.',           'cat_id'=>'physics',     'cat_name'=>'Physics'],
  ['id'=>'quantum',       'name'=>'Quantum Mechanics',     'description'=>'Probabilistic behaviour of particles at the smallest scales.',  'cat_id'=>'physics',     'cat_name'=>'Physics'],
  ['id'=>'relativity',    'name'=>'Relativity',            'description'=>'How space, time, and gravity are linked at all scales.',       'cat_id'=>'physics',     'cat_name'=>'Physics'],
  ['id'=>'optics',        'name'=>'Optics',                'description'=>'The behaviour and properties of light and its interactions.',   'cat_id'=>'physics',     'cat_name'=>'Physics'],
  ['id'=>'thermo',        'name'=>'Thermodynamics',        'description'=>'Heat, energy transfer, and the laws governing them.',          'cat_id'=>'physics',     'cat_name'=>'Physics'],
  ['id'=>'genetics',      'name'=>'Genetics',              'description'=>'Heredity, DNA structure, and gene expression.',                'cat_id'=>'biology',     'cat_name'=>'Biology'],
  ['id'=>'ecology',       'name'=>'Ecology',               'description'=>'Interactions between organisms and their environment.',         'cat_id'=>'biology',     'cat_name'=>'Biology'],
  ['id'=>'cell-bio',      'name'=>'Cell Biology',          'description'=>'Structure and function of the fundamental unit of life.',       'cat_id'=>'biology',     'cat_name'=>'Biology'],
  ['id'=>'calculus',      'name'=>'Calculus',              'description'=>'Differentiation, integration, and limits.',                    'cat_id'=>'mathematics', 'cat_name'=>'Mathematics'],
  ['id'=>'logic',         'name'=>'Logic',                 'description'=>'Formal reasoning, proofs, and deductive systems.',             'cat_id'=>'mathematics', 'cat_name'=>'Mathematics'],
  ['id'=>'statistics',    'name'=>'Statistics',            'description'=>'Probability, data analysis, and statistical inference.',        'cat_id'=>'mathematics', 'cat_name'=>'Mathematics'],
  ['id'=>'ancient',       'name'=>'Ancient History',       'description'=>'Civilisations and events before the Common Era.',             'cat_id'=>'history',     'cat_name'=>'History'],
  ['id'=>'medieval',      'name'=>'Medieval History',      'description'=>'Europe, the Islamic world, and Asia from 500 to 1500 CE.',    'cat_id'=>'history',     'cat_name'=>'History'],
  ['id'=>'modern',        'name'=>'Modern History',        'description'=>'The world from the Industrial Revolution to the present.',     'cat_id'=>'history',     'cat_name'=>'History'],
  ['id'=>'ai',            'name'=>'Artificial Intelligence','description'=>'Machine learning, neural networks, and intelligent systems.', 'cat_id'=>'technology',  'cat_name'=>'Technology'],
  ['id'=>'networks',      'name'=>'Computer Networks',     'description'=>'Protocols, infrastructure, and distributed systems.',          'cat_id'=>'technology',  'cat_name'=>'Technology'],
  ['id'=>'ethics',        'name'=>'Ethics',                'description'=>'Moral philosophy and theories of right action.',               'cat_id'=>'philosophy',  'cat_name'=>'Philosophy'],
  ['id'=>'epistemology',  'name'=>'Epistemology',          'description'=>'The nature, scope, and limits of knowledge.',                  'cat_id'=>'philosophy',  'cat_name'=>'Philosophy'],
  ['id'=>'metaphysics',   'name'=>'Metaphysics',           'description'=>'Questions of existence, reality, and the nature of being.',   'cat_id'=>'philosophy',  'cat_name'=>'Philosophy'],
  ['id'=>'cartography',   'name'=>'Cartography',           'description'=>'The art and science of map-making and spatial data.',          'cat_id'=>'geography',   'cat_name'=>'Geography'],
  ['id'=>'climate',       'name'=>'Climatology',           'description'=>'Long-term weather patterns and the climate system.',           'cat_id'=>'geography',   'cat_name'=>'Geography'],
  ['id'=>'macro',         'name'=>'Macroeconomics',        'description'=>'National output, employment, and monetary policy.',            'cat_id'=>'economics',   'cat_name'=>'Economics'],
  ['id'=>'micro',         'name'=>'Microeconomics',        'description'=>'Individual markets, consumer choice, and firm behaviour.',     'cat_id'=>'economics',   'cat_name'=>'Economics'],
  ['id'=>'visual-arts',   'name'=>'Visual Arts',           'description'=>'Painting, sculpture, photography, and design history.',        'cat_id'=>'arts',        'cat_name'=>'Arts'],
  ['id'=>'music',         'name'=>'Music',                 'description'=>'Theory, history, and cultural significance of music.',         'cat_id'=>'arts',        'cat_name'=>'Arts'],
];

$articles = [
  ['tag'=>'Physics',     'title'=>'Wave-Particle Duality and the Double-Slit Experiment',    'excerpt'=>'How a single experiment upended classical mechanics and revealed the probabilistic nature of light and matter.',       'date'=>'Today',       'read'=>'9 min',  'path'=>'Physics › Quantum Mechanics'],
  ['tag'=>'Mathematics', 'title'=>"Gödel's Incompleteness Theorems Explained",               'excerpt'=>'A tour through the proofs that forever changed the limits of formal mathematical systems and what truth means.',        'date'=>'Today',       'read'=>'12 min', 'path'=>'Mathematics'],
  ['tag'=>'History',     'title'=>'The Silk Road: Commerce, Culture & Contagion',            'excerpt'=>"Tracing the ancient trade network that connected civilisations from Chang'an to Constantinople over centuries.",       'date'=>'Yesterday',   'read'=>'7 min',  'path'=>'History › Ancient History'],
  ['tag'=>'Biology',     'title'=>'Mitochondria: Beyond the Powerhouse',                     'excerpt'=>"Recent research reveals the organelle's roles in immune signalling and programmed cell death.",                        'date'=>'2 days ago',  'read'=>'8 min',  'path'=>'Biology › Cell Biology'],
  ['tag'=>'Technology',  'title'=>'How Large Language Models Actually Work',                  'excerpt'=>'Attention mechanisms, tokenisation, and training — a ground-up explanation of the transformer architecture.',          'date'=>'2 days ago',  'read'=>'15 min', 'path'=>'Technology › Artificial Intelligence'],
  ['tag'=>'Philosophy',  'title'=>'Free Will in the Age of Neuroscience',                    'excerpt'=>'Can determinism and moral responsibility coexist? Compatibilist and incompatibilist positions surveyed.',              'date'=>'3 days ago',  'read'=>'10 min', 'path'=>'Philosophy › Ethics'],
  ['tag'=>'Geography',   'title'=>"Plate Tectonics: The Engine of Earth's Surface",          'excerpt'=>'From seafloor spreading to the supercontinent cycle — the slow, relentless motion that reshapes our world.',           'date'=>'4 days ago',  'read'=>'6 min',  'path'=>'Geography › Climatology'],
  ['tag'=>'Economics',   'title'=>'The Nash Equilibrium and Strategic Decision-Making',       'excerpt'=>"Game theory's cornerstone concept and how it applies to markets, diplomacy, and everyday life.",                      'date'=>'4 days ago',  'read'=>'8 min',  'path'=>'Economics › Microeconomics'],
  ['tag'=>'Science',     'title'=>'Dark Matter: What We Know and Do Not Know',               'excerpt'=>'Decades of observation point to invisible mass holding galaxies together — but its nature remains entirely unknown.',  'date'=>'5 days ago',  'read'=>'11 min', 'path'=>'Science'],
  ['tag'=>'Arts',        'title'=>'The Bauhaus and the Design Revolution It Sparked',        'excerpt'=>'How a short-lived school in Weimar Germany permanently altered architecture, typography, and industrial design.',      'date'=>'5 days ago',  'read'=>'7 min',  'path'=>'Arts › Visual Arts'],
  ['tag'=>'Physics',     'title'=>'Special Relativity: Time Dilation for Beginners',        'excerpt'=>"Einstein's 1905 paper reshaped our understanding of time, space, and simultaneity with elegant thought experiments.", 'date'=>'6 days ago',  'read'=>'9 min',  'path'=>'Physics › Relativity'],
  ['tag'=>'History',     'title'=>"The Byzantine Empire's Lasting Cultural Legacy",          'excerpt'=>'Despite its fall in 1453, Byzantium shaped Orthodox Christianity, Roman law, and Renaissance art in lasting ways.',   'date'=>'6 days ago',  'read'=>'8 min',  'path'=>'History › Medieval History'],
  ['tag'=>'Physics',     'title'=>"Newton's Laws of Motion and Their Modern Applications",  'excerpt'=>'From the apple to orbital mechanics — how the three laws underpin everything in classical physics.',                  'date'=>'1 week ago',  'read'=>'7 min',  'path'=>'Physics › Classical Mechanics'],
  ['tag'=>'Physics',     'title'=>'The Photoelectric Effect and the Birth of Quantum Theory','excerpt'=>'How Einstein explained light quanta and why it earned him the Nobel Prize over relativity.',                          'date'=>'2 weeks ago', 'read'=>'8 min',  'path'=>'Physics › Quantum Mechanics'],
  ['tag'=>'Physics',     'title'=>'Thermodynamics: The Four Laws Explained',                'excerpt'=>"From absolute zero to entropy — a clear walkthrough of thermodynamics' foundational laws.",                           'date'=>'3 weeks ago', 'read'=>'10 min', 'path'=>'Physics › Thermodynamics'],
  ['tag'=>'Physics',     'title'=>"Snell's Law and the Behaviour of Light at Boundaries",   'excerpt'=>'Refraction, total internal reflection, and why your straw looks bent in water.',                                      'date'=>'1 month ago', 'read'=>'5 min',  'path'=>'Physics › Optics'],
];

$q  = trim($_GET['q'] ?? '');
$ql = strtolower($q);

function matches(string $haystack, string $needle): bool {
  return str_contains(strtolower($haystack), $needle);
}

if ($q !== '') {
  $catResults = array_values(array_filter($categories, fn($c) =>
    matches($c['name'], $ql)
  ));
  $subcatResults = array_values(array_filter($subcategories, fn($s) =>
    matches($s['name'], $ql) || matches($s['description'] ?? '', $ql) || matches($s['cat_name'], $ql)
  ));
  $artResults = array_values(array_filter($articles, fn($a) =>
    matches($a['title'], $ql) || matches($a['excerpt'], $ql) || matches($a['tag'], $ql) || matches($a['path'], $ql)
  ));
} else {
  $catResults = $subcatResults = $artResults = [];
}

$total = count($catResults) + count($subcatResults) + count($artResults);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $q ? htmlspecialchars($q).' — ' : ''; ?>Search — Portal Wiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css">
<link rel="stylesheet" href="styles/search.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="../home.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div class="search-wrap flex-grow-1">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="headerSearch" placeholder="Search wiki…" value="<?php echo htmlspecialchars($q); ?>" onkeydown="if(event.key==='Enter'&&this.value.trim())location.href='search.php?q='+encodeURIComponent(this.value.trim())">
      </div>
      <button class="theme-toggle" onclick="toggleTheme()" title="Toggle light/dark">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <a href="../foruns/forum.php" class="hbtn primary" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Forum
      </a>
      <a href="../auth/formLogin.php" class="hbtn" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Login
      </a>
    </div>
  </div>
</header>

<div class="container-lg py-4">

  <?php if ($q === ''): ?>
    <!-- Empty state — no query -->
    <div class="search-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <div class="search-empty-title">Search the wiki</div>
      <div class="search-empty-sub">Type to find categories, subcategories and articles.</div>
    </div>

  <?php elseif ($total === 0): ?>
    <!-- No results -->
    <div class="search-empty">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <div class="search-empty-title">No results for &ldquo;<?php echo htmlspecialchars($q); ?>&rdquo;</div>
      <div class="search-empty-sub">Try different keywords or check your spelling.</div>
    </div>

  <?php else: ?>
    <!-- Summary -->
    <div class="search-summary">
      <?php echo $total; ?> result<?php echo $total !== 1 ? 's' : ''; ?> for &ldquo;<?php echo htmlspecialchars($q); ?>&rdquo;
    </div>

    <!-- Categories -->
    <?php if ($catResults): ?>
    <div class="section-heading">
      Categories <span class="res-count"><?php echo count($catResults); ?></span>
    </div>
    <div class="d-flex flex-column gap-1 mb-4">
      <?php foreach ($catResults as $c): ?>
      <a class="result-bar" href="viewPage.php?primaryCategory=<?php echo urlencode($c['name']); ?>">
        <span class="res-chip">Category</span>
        <div class="art-body">
          <div class="art-title"><?php echo htmlspecialchars($c['name']); ?></div>
        </div>
        <div class="art-meta">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Subcategories -->
    <?php if ($subcatResults): ?>
    <div class="section-heading">
      Subcategories <span class="res-count"><?php echo count($subcatResults); ?></span>
    </div>
    <div class="d-flex flex-column gap-1 mb-4">
      <?php foreach ($subcatResults as $s): ?>
      <a class="result-bar" href="viewPage.php?primaryCategory=<?php echo urlencode($s['cat_name']); ?>&secondaryCategory=<?php echo urlencode($s['name']); ?>">
        <span class="res-chip">Subcategory</span>
        <div class="art-body">
          <div class="art-title"><?php echo htmlspecialchars($s['name']); ?></div>
          <?php if ($s['description']): ?>
          <div class="art-excerpt"><?php echo htmlspecialchars($s['description']); ?></div>
          <?php endif; ?>
        </div>
        <div class="art-meta">
          <span><?php echo htmlspecialchars($s['cat_name']); ?></span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><polyline points="9 18 15 12 9 6"/></svg>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Articles -->
    <?php if ($artResults): ?>
    <div class="section-heading">
      Articles <span class="res-count"><?php echo count($artResults); ?></span>
    </div>
    <div class="d-flex flex-column gap-1 mb-4">
      <?php foreach ($artResults as $a): ?>
      <a class="result-bar" href="viewPage.php">
        <span class="art-tag"><?php echo htmlspecialchars($a['tag']); ?></span>
        <div class="art-body">
          <div class="art-title"><?php echo htmlspecialchars($a['title']); ?></div>
          <div class="art-excerpt"><?php echo htmlspecialchars($a['excerpt']); ?></div>
        </div>
        <div class="art-meta">
          <span><?php echo htmlspecialchars($a['path']); ?></span>
          <span class="art-sep">·</span>
          <span><?php echo htmlspecialchars($a['date']); ?></span>
          <span class="art-sep">·</span>
          <span><?php echo htmlspecialchars($a['read']); ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  <?php endif; ?>

</div>

<script src="js/search.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
