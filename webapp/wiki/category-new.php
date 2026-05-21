<?php
// webapp/wiki/category-new.php

// TODO: Replace the array below with your DB query, e.g.:
// $categories = $db->query("SELECT id, name FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll();

$categories = [
  ['id'=>'science',     'name'=>'Science'    ],
  ['id'=>'history',     'name'=>'History'    ],
  ['id'=>'technology',  'name'=>'Technology' ],
  ['id'=>'mathematics', 'name'=>'Mathematics'],
  ['id'=>'philosophy',  'name'=>'Philosophy' ],
  ['id'=>'biology',     'name'=>'Biology'    ],
  ['id'=>'geography',   'name'=>'Geography'  ],
  ['id'=>'economics',   'name'=>'Economics'  ],
  ['id'=>'arts',        'name'=>'Arts'       ],
  ['id'=>'physics',     'name'=>'Physics'    ],
];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Category — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css">
<link rel="stylesheet" href="styles/category-new.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="wiki.php">smiki</a>
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
  <div class="section-heading">New Category</div>

  <div class="editor-form">
    <!-- Name -->
    <div class="editor-field">
      <label class="editor-label" for="fieldName">Name</label>
      <input class="editor-input" type="text" id="fieldName" placeholder="Category name…">
    </div>

    <!-- Parent category -->
    <div class="editor-field">
      <label class="editor-label" for="fieldParent">Parent category <span class="editor-label-hint">optional — creates a subcategory</span></label>
      <select class="editor-input editor-select" id="fieldParent">
        <option value="">None (top-level)</option>
      </select>
    </div>

    <!-- Image upload -->
    <div class="editor-field">
      <label class="editor-label" for="fieldImage">Image</label>
      <label class="img-upload-area" for="fieldImage" id="imgUploadArea">
        <input type="file" id="fieldImage" accept="image/*" onchange="previewImage(this)">
        <div class="img-upload-placeholder" id="imgPlaceholder">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="22" height="22"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span>Choose an image</span>
        </div>
        <img id="imgPreview" alt="Preview">
      </label>
    </div>

    <!-- Actions -->
    <div class="editor-actions">
      <a href="wiki.php" class="hbtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Cancel
      </a>
      <button class="hbtn primary" onclick="createCategory()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Create
      </button>
    </div>
  </div>
</div>

<script>
const categories = <?php echo json_encode($categories); ?>;
</script>
<script src="js/category-new.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
