<?php
// forum-embed.php
include_once("../../Lib/lib.php");
?>
<div id="forum-root">

  <div class="forum-nav">
    <button class="filter-btn active" data-category=""
            onclick="setFilter(this, '')">All</button>
    <button class="filter-btn" data-category="Geral"
            onclick="setFilter(this, 'Geral')">General</button>
    <button class="filter-btn" data-category="Suporte"
            onclick="setFilter(this, 'Suporte')">Support</button>
  </div>

  <div id="forum-main-content">
    <p class="loading">Loading discussions…</p>
  </div>

  <script>
  function setFilter(btn, category) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    loadDiscussions(category);
  }
  </script>
  <script src="js/composer.js"></script>
  <script src="js/discussions.js"></script>
</div>
