<?php
// forum-embed.php
include_once("../../Lib/lib.php");
include_once("../../Lib/wikiLib.php");

$categories = [];
if (function_exists('getCategoryList')) {
    $categories = getCategoryList('primary');
}
?>
<div id="forum-root">

  <div class="forum-nav">
    <button class="filter-btn active" data-category=""
            onclick="setFilter(this, '')">All</button>
    
    <?php foreach ($categories as $cat): 
        $categoryName = $cat['primaryCategory']; 
    ?>
        <button class="filter-btn" 
                data-category="<?php echo htmlspecialchars($categoryName); ?>"
                onclick="setFilter(this, '<?php echo htmlspecialchars($categoryName); ?>')">
            <?php echo htmlspecialchars($categoryName); ?>
        </button>
    <?php endforeach; ?>
  </div>

  <div id="forum-main-content">
    <p class="loading">Loading discussions…</p>
  </div>

  <script src="js/composer.js"></script>
  <script src="js/discussions.js"></script>
</div>
