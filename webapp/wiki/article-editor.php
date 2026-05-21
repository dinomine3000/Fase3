<?php
// webapp/wiki/article-editor.php
// ?id=N → edit mode; no id → create mode

// TODO: Replace the arrays below with your DB queries, e.g.:
// $categories = $db->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
// $editArticle = isset($_GET['id'])
//   ? $db->query("SELECT id, tag, title, cat, content FROM articles WHERE id = ?", [$_GET['id']])->fetch()
//   : null;

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

$editArticle = null; // set to fetched article array when editing
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Article Editor — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css">
<link rel="stylesheet" href="styles/editor.css">
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
  <div class="section-heading" id="editorHeading">New Article</div>

  <div class="editor-form">
    <!-- Title -->
    <div class="editor-field">
      <label class="editor-label" for="fieldTitle">Title</label>
      <input class="editor-input" type="text" id="fieldTitle" placeholder="Article title…">
    </div>

    <!-- Category + Tags row -->
    <div class="editor-row">
      <div class="editor-field">
        <label class="editor-label" for="fieldCategory">Category</label>
        <select class="editor-input editor-select" id="fieldCategory">
          <option value="">Select category…</option>
        </select>
      </div>
      <div class="editor-field">
        <label class="editor-label" for="fieldTags">Tags</label>
        <input class="editor-input" type="text" id="fieldTags" placeholder="e.g. Physics, Quantum">
      </div>
    </div>

    <!-- Markdown toolbar + textarea -->
    <div class="editor-field">
      <label class="editor-label">Content <span class="editor-label-hint">markdown</span></label>
      <div class="md-toolbar">
        <button class="md-btn" title="Bold" onclick="insertMd('**','**')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>
        </button>
        <button class="md-btn" title="Italic" onclick="insertMd('*','*')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
        </button>
        <button class="md-btn" title="Heading" onclick="insertMd('## ','')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12h16M4 6v12M20 6v12"/></svg>
        </button>
        <button class="md-btn" title="Inline code" onclick="insertMd('\`','\`')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        </button>
        <button class="md-btn" title="Code block" onclick="insertMd('\`\`\`\n','\n\`\`\`')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </button>
        <button class="md-btn" title="Link" onclick="insertMd('[','](url)')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
        </button>
        <button class="md-btn" title="Blockquote" onclick="insertMd('> ','')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>
        </button>
        <button class="md-btn" title="Bullet list" onclick="insertMd('- ','')">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </button>
      </div>
      <textarea class="editor-textarea" id="fieldContent" placeholder="Write your article in markdown…"></textarea>
    </div>

    <!-- Actions -->
    <div class="editor-actions">
      <a href="wiki.php" class="hbtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Cancel
      </a>
      <button class="hbtn primary" onclick="saveArticle()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        Save
      </button>
    </div>
  </div>
</div>

<script>
const categories  = <?php echo json_encode($categories); ?>;
const editArticle = <?php echo json_encode($editArticle); ?>;
</script>
<script src="js/article-editor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
