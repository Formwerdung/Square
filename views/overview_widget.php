<div class="main">
  <ul>
    <?php
    // Loop through all post types
    $post_types = $vars;
    foreach ($post_types as $post_type) {
      // Count the number of posts in the post type (skips attachments)
      if ($post_type == 'comments') {
        continue;
      }
      $num_posts = wp_count_posts($post_type->name);
      // If there are posts and published posts
      if ($num_posts && $num_posts->publish) {
        if ($num_posts->publish == 1) {
          $text = '1 ' . $post_type->labels->singular_name;
        } else {
          $text = $num_posts->publish . ' ' . $post_type->labels->name;
        }
        if ($post_type && current_user_can($post_type->cap->edit_posts)) {
          printf('<li class="%1$s-count"><a href="edit.php?post_type=%1$s">%2$s</a></li>', $post_type->name, $text);
        } else {
          printf('<li class="%1$s-count"><span>%2$s</span></li>', $post_type->name, $text);
        }
      }
    }
    // Comments
    if (isset($post_types['comments'])) {
      $num_comm = wp_count_comments();
      if ($num_comm && $num_comm->approved) {
        $text = sprintf(_n('%s Comment', '%s Comments', $num_comm->approved), number_format_i18n($num_comm->approved));
        ?>
        <li class="comment-count"><a href="edit-comments.php"><?php echo $text; ?></a></li>
        <?php
        if ($num_comm->moderated) {
          /* translators: Number of comments in moderation */
          $text = sprintf(_nx('%s in moderation', '%s in moderation', $num_comm->moderated, 'comments'), number_format_i18n($num_comm->moderated));
          ?>
          <li class="comment-mod-count"><a href="edit-comments.php?comment_status=moderated"><?php echo $text; ?></a></li>
          <?php
        }
      }
    }

    /**
     * Filter the array of extra elements to list in the 'At a Glance'
     * dashboard widget.
     *
     * Prior to 3.8.0, the widget was named 'Right Now'. Each element
     * is wrapped in list-item tags on output.
     *
     * @since 3.8.0
     *
     * @param array $items Array of extra 'At a Glance' widget items.
     */
    $elements = apply_filters('square_glance_items', []);

    if ($elements) {
      echo '<li>' . implode("</li>\n<li>", $elements) . "</li>\n";
    }

    ?>
    </ul>
    <?php update_right_now_message();

    // Check if search engines are asked not to index this site.
    if (! is_network_admin() && ! is_user_admin() && current_user_can('manage_options') && '1' != get_option('blog_public')) {

    /**
     * Filter the link title attribute for the 'Search Engines Discouraged'
     * message displayed in the 'At a Glance' dashboard widget.
     *
     * Prior to 3.8.0, the widget was named 'Right Now'.
     *
     * @since 3.0.0
     *
     * @param string $title Default attribute text.
     */
      $title = __('Your site is asking search engines not to index its content');

    /**
     * Filter the link label for the 'Search Engines Discouraged' message
     * displayed in the 'At a Glance' dashboard widget.
     *
     * Prior to 3.8.0, the widget was named 'Right Now'.
     *
     * @since 3.0.0
     *
     * @param string $content Default text.
     */
      $content = __('Search Engines Discouraged');

      echo "<p><a href='options-reading.php' title='$title'>$content</a></p>";
    } ?>
</div>
<?php
/*
 * activity_box_end has a core action, but only prints content when multisite.
 * Using an output buffer is the only way to really check if anything's displayed here.
 */
ob_start();

$actions = ob_get_clean();

if (!empty($actions)) : ?>
  <div class="sub">
    <?php echo $actions; ?>
  </div>
<?php endif;
