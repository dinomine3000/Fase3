<?php
include_once("../../Lib/lib.php");
require_once( "../../Lib/wikiLib.php" );

if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['username']) || !authorizeUserByLevel($_SESSION['username'], "organizer")) {
    header("Location: ../index.php");
    exit();
}
$primaryCategories = getCategoryList('primary');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Wiki Page</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles/wiki.css?v=4">
<link rel="stylesheet" href="styles/form.css">
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<?php include('./header.php')?>

<div class="container-lg py-4">
  <div class="section-heading"><?php echo lang('create_wiki_page'); ?></div>

  <div class="form-card">
    <!--
    Source - https://stackoverflow.com/a/8814534
    Posted by Bajrang, modified by community. See post 'Timeline' for change history
    Retrieved 2026-05-21, License - CC BY-SA 3.0
    -->
    <form action="processCreatePage.php" method="POST">

      <div class="form-group">
        <label class="form-label" for="pageTitle"><?php echo lang('page_title'); ?></label>
        <input class="form-input" type="text" id="pageTitle" name="pageTitle"
               placeholder="<?php echo lang('page_title_placeholder'); ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="primaryCategory"><?php echo lang('primary_category'); ?></label>
        <select class="form-select" id="primaryCategory" name="primaryCategory"
                onchange="updateSecondaryCategories()" required>
          <option value=""><?php echo lang('select_primary_category'); ?></option>
          <?php foreach ($primaryCategories as $category): ?>
          <option value="<?php echo htmlspecialchars($category['primaryCategory']); ?>">
            <?php echo htmlspecialchars($category['primaryCategory']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="secondaryCategory"><?php echo lang('secondary_category'); ?></label>
        <select class="form-select" id="secondaryCategory" name="secondaryCategory" required>
          <option value=""><?php echo lang('select_secondary_category'); ?></option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="content"><?php echo lang('content_markdown'); ?></label>
        <div class="md-toolbar">
          <button type="button" class="md-btn" onclick="insertMd('**','**')"><b>B</b></button>
          <button type="button" class="md-btn" onclick="insertMd('*','*')"><em>I</em></button>
          <button type="button" class="md-btn" onclick="insertMd('# ','')">H1</button>
          <button type="button" class="md-btn" onclick="insertMd('## ','')">H2</button>
          <button type="button" class="md-btn" onclick="insertMd('### ','')">H3</button>
          <button type="button" class="md-btn" onclick="insertMd('`','`')">Code</button>
          <button type="button" class="md-btn" onclick="insertMd('\n```\n','\n```')">Block</button>
          <button type="button" class="md-btn" onclick="insertMd('- ','')">List</button>
          <button type="button" class="md-btn" onclick="insertMd('[','](url)')">Link</button>
        </div>
        <textarea class="form-textarea" id="content" name="content"
                  placeholder="<?php echo lang('content_placeholder'); ?>" required></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="hbtn primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          <?php echo lang('publish_wiki_page'); ?>
        </button>
        <button type="button" class="hbtn" onclick="history.back()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          <?php echo lang('go_back'); ?>
        </button>
      </div>

    </form>
  </div>
</div>

<script>
function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}

function insertMd(before, after) {
  const ta = document.getElementById('content');
  const start = ta.selectionStart;
  const end   = ta.selectionEnd;
  const sel   = ta.value.substring(start, end);
  ta.value = ta.value.substring(0, start) + before + sel + after + ta.value.substring(end);
  ta.selectionStart = start + before.length;
  ta.selectionEnd   = start + before.length + sel.length;
  ta.focus();
}
</script>
<script src="../scripts/ajaxHandler.js?v=3"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php
if (isset($GLOBALS['ligacao'])) {
    mysqli_close($GLOBALS['ligacao']);
}
?>
</body>
</html>
