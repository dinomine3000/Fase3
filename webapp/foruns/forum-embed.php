<?php
// forum-embed.php
include_once("../../Lib/lib.php");
include_once("../../Lib/wikiLib.php");
include_once("../../Lib/lang/translator.php");

$categories = [];
if (function_exists('getCategoryList')) {
    $categories = getCategoryList('primary');
}
?>
<script>
const forumLang = {
  loading:                   <?php echo json_encode(lang('forum_loading')); ?>,
  new_topic:                 <?php echo json_encode(lang('forum_new_topic')); ?>,
  author:                    <?php echo json_encode(lang('forum_author')); ?>,
  category_label:            <?php echo json_encode(lang('forum_category_label')); ?>,
  replies:                   <?php echo json_encode(lang('forum_replies')); ?>,
  back:                      <?php echo json_encode(lang('forum_back')); ?>,
  at:                        <?php echo json_encode(lang('forum_at')); ?>,
  delete_post:               <?php echo json_encode(lang('forum_delete_post')); ?>,
  likes:                     <?php echo json_encode(lang('forum_likes')); ?>,
  leave_reply:               <?php echo json_encode(lang('forum_leave_reply')); ?>,
  reply_placeholder:         <?php echo json_encode(lang('forum_reply_placeholder')); ?>,
  post_reply:                <?php echo json_encode(lang('forum_post_reply')); ?>,
  empty:                     <?php echo json_encode(lang('forum_empty')); ?>,
  error_load:                <?php echo json_encode(lang('forum_error_load')); ?>,
  error_discussion:          <?php echo json_encode(lang('forum_error_discussion')); ?>,
  login_to_like:             <?php echo json_encode(lang('forum_login_to_like')); ?>,
  not_authenticated:         <?php echo json_encode(lang('forum_not_authenticated')); ?>,
  confirm_delete_post:       <?php echo json_encode(lang('forum_confirm_delete_post')); ?>,
  discussion_auto_removed:   <?php echo json_encode(lang('forum_discussion_auto_removed')); ?>,
  error_delete_post:         <?php echo json_encode(lang('forum_error_delete_post')); ?>,
  confirm_delete_discussion: <?php echo json_encode(lang('forum_confirm_delete_discussion')); ?>,
  discussion_deleted:        <?php echo json_encode(lang('forum_discussion_deleted')); ?>,
  error_delete_discussion:   <?php echo json_encode(lang('forum_error_delete_discussion')); ?>,
  loading_categories:        <?php echo json_encode(lang('forum_loading_categories')); ?>,
  cancel:                    <?php echo json_encode(lang('cancel')); ?>,
  create_discussion_title:   <?php echo json_encode(lang('forum_create_discussion_title')); ?>,
  title_label:               <?php echo json_encode(lang('forum_title_label')); ?>,
  title_placeholder:         <?php echo json_encode(lang('forum_title_placeholder')); ?>,
  primary_category_label:    <?php echo json_encode(lang('forum_primary_category_label')); ?>,
  initial_message:           <?php echo json_encode(lang('forum_initial_message')); ?>,
  message_placeholder:       <?php echo json_encode(lang('forum_message_placeholder')); ?>,
  launch_discussion:         <?php echo json_encode(lang('forum_launch_discussion')); ?>,
};
</script>

<div id="forum-root">

  <div class="forum-nav">
    <button class="filter-btn active" data-category=""
            onclick="setFilter(this, '')"><?php echo lang('forum_all'); ?></button>

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
    <p class="loading"><?php echo lang('forum_loading'); ?></p>
  </div>

  <script src="js/composer.js"></script>
  <script src="js/discussions.js"></script>
</div>
