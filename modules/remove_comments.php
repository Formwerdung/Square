<?php

namespace Formwerdung\Square\Modules;

class RemoveComments extends \Formwerdung\Square\Lib\Admin {

  /**
   * Key of menu label that is to be removed, here "comments"
   * @url http://code.tutsplus.com/articles/customizing-your-wordpress-admin--wp-24941
   */
  public static $menu_label_key = 25;

  /**
   * Hide the "discussion options" submenu
   */
  public static $submenu_labels = [
    "options-general.php" => "options-discussion.php"
  ];

  /**
   * Remove the comments node of the admin bar
   */
  public static $node_id = 'comments';

  /**
   * Redirect all requests for comment-related pages to the dashboard
   *
   * @uses   global $pagenow;
   * @return void
   */
  public static function redirectAdminPages() {
    global $pagenow;

    switch ($pagenow) {
      case 'comment.php':
      case 'edit-comments.php':
      case 'options-discussion.php':
        wp_safe_redirect(get_admin_url());
        break;
    }
  }

  /**
   * Remove post type support for comments
   */
  public static function removePostTypeSupportComments() {
    $post_types = static::buildPostTypeArray(true);
    if (!empty($post_types)) {
      foreach ($post_types as $type) {
          // we need to know what native support was for later
        if (post_type_supports($type, 'comments')) {
            remove_post_type_support($type, 'comments');
            remove_post_type_support($type, 'trackbacks');
        }
      }
    }
  }

  /**
   * Remove nodes on multisite (untested)
   *
   * @link https://github.com/solarissmoke/disable-comments-mu
   * @return void
   */
  public static function removeNetworkCommentNodes($wp_admin_bar) {
    foreach ((array) $wp_admin_bar->user->blogs as $blog) {
        $wp_admin_bar->remove_node('blog-' . $blog->userblog_id . '-c'); }
  }

  /**
   * Filter out the comment feed
   *
   * @since  0.0.1
   * @uses   wp_die()
   * @return void
   */
  public static function filterFeedComments() {
    if (is_comment_feed()) {
      wp_die('There are no comments enabled on this site.', '', ['response' => 403]);
    }
  }

  /**
   * Remove the recent comment list widget
   *
   * @return void
   */
  public static function disableRecCommWidget() {
        // This widget has been removed from the Dashboard in WP 3.8 and can be removed in a future version
        unregister_widget('WP_Widget_Recent_Comments');
  }

  /**
   * Filter the comment status to always be false
   */
  public static function filterCommentStatus($open, $post_id) {
    return false;
  }

  /**
   * Brute force comment template
   * @link https://github.com/solarissmoke/disable-comments-mu
   */
  public static function forceCommentTemplate() {
    if (is_singular()) {
        // Kill the comments template. This will deal with themes that don't check comment stati properly!
        add_filter('comments_template', [ get_called_class(), 'dummyCommentTemplate' ], 20);
        // Remove comment-reply script for themes that include it indiscriminately
        wp_deregister_script('comment-reply');
        // Remove feed action (this is in roots/soil)
        // remove_action( 'wp_head', 'feed_links_extra', 3 );
    }
  }

  /**
   * Render empty comments template
   */
  public static function dummyCommentTemplate() {
    echo static::renderTemplate('comments-template.php');
  }

  /**
   * Remove pingback header shit (this is in roots/soil diable-trackbacks module)
   *
   * @param  array $headers
   * @return array $headers
   */
  // function filterHeadPingback($headers) {
  // 	unset( $headers['X-Pingback'] );
  // 	return $headers;
  // }

  /**
   * filter feed
   */
  public static function filterHeadFeed($args = array()) {
        // if wp_head feed_links has not been tampered with (WP 4.1.1)
    if (has_action('wp_head', 'feedLinks') == 2) {
        // replace it with a modified version
        remove_action('wp_head', 'feedLinks', 2);
        add_action('wp_head', [ get_called_class(), 'feedLinks' ]);
    }
  }

    // replaces feed_links function, WP 4.1.1
    public static function feedLinks($args = array()) {
    if (!current_theme_supports('automatic-feed-links')) {
        return; }
        $defaults = array(
            /* translators: Separator between blog name and feed type in feed links */
            'separator'     => _x('&raquo;', 'feed link'),
            /* translators: 1: blog title, 2: separator (raquo) */
            'feedtitle'     => __('%1$s %2$s Feed'),
        );
        $args = wp_parse_args($args, $defaults);
        echo '<link rel="alternate" type="' . feed_content_type() . '" title="' . esc_attr(sprintf($args['feedtitle'], get_bloginfo('name'), $args['separator'])) . '" href="' . esc_url(get_feed_link()) . "\" />\n";
    }

  /**
   * Register hook callbacks
   *
   * @since  0.0.1
   * @uses   add_action()
   * @return void
   */
    public static function registerHookCallbacks() {
      add_action('admin_menu', [ get_called_class(), 'hideMenuItems' ], 10);
      add_action('admin_menu', [ get_called_class(), 'hideSubmenuItems' ], 11);
      add_action('admin_menu', [ get_called_class(), 'removeMetaBoxes' ], 12);
      add_action('admin_bar_menu', [ get_called_class(), 'removeNode' ], 999);

      add_action('init', [ get_called_class(), 'redirectAdminPages']);

      add_action('widgets_init', [ get_called_class(), 'disableRecCommWidget' ]);
      // add_filter('wp_headers', [ get_called_class(), 'filterHeadPingback' ]);
      add_action('template_redirect', [ get_called_class(), 'filterFeedComments' ], 9); // before redirect_canonical

      if (is_multisite()) {
        add_action('admin_bar_menu', [ get_called_class(), 'removeNetworkCommentNodes' ], 500); }

      add_action('init', [ get_called_class(), 'removePostTypeSupportComments' ]);

      if (is_admin()) {
        add_filter('pre_update_default_ping_status', '__return_false');
        add_filter('pre_option_default_ping_status', '__return_zero');
        add_filter('pre_update_default_pingback_flag', '__return_false');
        add_filter('pre_option_default_pingback_flag', '__return_zero');
      } else {
        add_action('template_redirect', [ get_called_class(), 'forceCommentTemplate']);
        add_filter('comments_open', [ get_called_class(), 'filterCommentStatus' ], 20, 2);
        add_filter('pings_open', [ get_called_class(), 'filterCommentStatus' ], 20, 2);
        // remove comments links from feed
        add_filter('post_comments_feed_link', '__return_false', 10, 1);
            add_filter('comments_link_feed', '__return_false', 10, 1);
            add_filter('comment_link', '__return_false', 10, 1);
        // remove comment count from feed
        add_filter('get_comments_number', '__return_false', 10, 2);
        // run when wp_head executes
        add_action('wp_head', [ get_called_class(), 'filterHeadFeed' ], 0);
      }
    }
}
