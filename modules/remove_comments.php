<?php

namespace Formwerdung\Square\Modules;

class RemoveComments extends \Formwerdung\Square\Lib\Admin {

  /**
   * @var    string $menu_label_key the key of the top level menu label to be removed (25 = comments, see link)
   * @access protected
   * @link   http://code.tutsplus.com/articles/customizing-your-wordpress-admin--wp-24941
   */
  protected static $menu_label_key = 25;

  /**
   * @var    array key is the top level menu basename, value the submenu level basename (including '.php')
   * @access protected
   */
  protected static $submenu_labels = [
    "options-general.php" => "options-discussion.php"
  ];

  /**
   * @var    string id of the admin node to be removed
   * @access protected
   */
  protected static $node_id = 'comments';

  /**
   * Array of admin pages that should be redirected
   *
   * @var    array
   * @access protected
   */
  protected static $redirected_pages = [
    'comment.php',
    'edit-comments.php',
    'options-discussion.php'
  ];

  /**
   * Remove post type support for comments
   *
   * @since  0.0.2
   * @access public
   * @link   https://github.com/solarissmoke/disable-comments-mu
   * @uses   post_type_supports()
   * @uses   remove_post_type_support()
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
   * @since  0.0.2
   * @access public
   * @link   https://github.com/solarissmoke/disable-comments-mu
   * @todo   test
   */
  public static function removeNetworkCommentNodes($wp_admin_bar) {
    foreach ((array) $wp_admin_bar->user->blogs as $blog) {
        $wp_admin_bar->remove_node('blog-' . $blog->userblog_id . '-c'); }
  }

  /**
   * Filter out the comment feed
   *
   * @since  0.0.2
   * @access public
   * @link   https://github.com/solarissmoke/disable-comments-mu
   * @uses   wp_die()
   */
  public static function filterFeedComments() {
    if (is_comment_feed()) {
      wp_die('There are no comments enabled on this site.', '', ['response' => 403]);
    }
  }

  /**
   * Remove the recent comment list widget
   *
   * @since  0.0.2
   * @access public
   * @link   https://github.com/solarissmoke/disable-comments-mu
   * @uses   unregister_widget()
   */
  public static function disableRecCommWidget() {
    // This widget has been removed from the Dashboard in WP 3.8 and can be removed in a future version
    unregister_widget('WP_Widget_Recent_Comments');
  }

  /**
   * Filter the comment status to always be false
   *
   * @since  0.0.2
   * @access public
   * @link   https://github.com/solarissmoke/disable-comments-mu
   */
  public static function filterCommentStatus($open, $post_id) {
    return false;
  }

  /**
   * Brute force comment template
   *
   * @since  0.0.2
   * @access public
   * @link   https://github.com/solarissmoke/disable-comments-mu
   * @uses   is_singular()
   * @uses   add_filter()
   * @uses   wp_deregister_script()
   * @uses   remove_action()
   */
  public static function forceCommentTemplate() {
    if (is_singular()) {
      // Kill the comments template. This will deal with themes that don't check comment stati properly!
      add_filter('comments_template', [ get_called_class(), 'dummyCommentTemplate' ], 20);
      // Remove comment-reply script for themes that include it indiscriminately
      wp_deregister_script('comment-reply');
      // Remove feed action (this is in roots/soil)
      if (!static::isThemeFeature('soil-clean-up')) {
        remove_action('wp_head', 'feed_links_extra', 3); }
    }
  }

  /**
   * Render empty comments template
   *
   * @since  0.0.2
   * @access public
   * @link   https://github.com/solarissmoke/disable-comments-mu
   */
  public static function dummyCommentTemplate() {
    echo static::renderTemplate('comments-template.php');
  }

  /**
   * Remove pingback header shit (this is in roots/soil diable-trackbacks module)
   *
   * @since  0.0.5
   * @access public
   * @param  array $headers
   * @return array $headers
   */
  public static function filterHeadPingback($headers) {
    if (static::isThemeFeature('soil-disable-trackbacks')) {
      return; }
    unset($headers['X-Pingback']);
    return $headers;
  }

  /**
   * Filter feed
   *
   * @since  0.0.2
   * @access public
   * @link   https://github.com/solarissmoke/disable-comments-mu
   * @uses   has_action()
   * @uses   remove_action()
   * @uses   add_action()
   */
  public static function filterHeadFeed($args = []) {
    // if wp_head feed_links has not been tampered with (WP 4.1.1)
    if (has_action('wp_head', 'feedLinks') == 2) {
      // replace it with a modified version
      remove_action('wp_head', 'feedLinks', 2);
      add_action('wp_head', [ get_called_class(), 'feedLinks' ]);
    }
  }

  /**
   * replaces feed_links function, WP 4.1.1
   *
   * @since  0.0.2
   * @access public
   * @link   https://github.com/solarissmoke/disable-comments-mu
   * @uses   current_theme_supports()
   * @uses   wp_parse_args
   * @uses   feed_content_type()
   * @uses   esc_attr()
   * @uses   get_bloginfo()
   * @uses   get_feed_link()
   */
  public static function feedLinks($args = []) {
    if (!current_theme_supports('automatic-feed-links')) {
      return; }
    $defaults = [
      /* translators: Separator between blog name and feed type in feed links */
      'separator'     => _x('&raquo;', 'feed link'),
      /* translators: 1: blog title, 2: separator (raquo) */
      'feedtitle'     => __('%1$s %2$s Feed'),
    ];
    $args = wp_parse_args($args, $defaults);
    echo '<link rel="alternate" type="' . feed_content_type() . '" title="' . esc_attr(sprintf($args['feedtitle'], get_bloginfo('name'), $args['separator'])) . '" href="' . esc_url(get_feed_link()) . "\" />\n";
  }

  /**
   * Conditional hook callbacks (back-end)
   *
   * @since  0.0.3
   * @access protected
   * @uses   is_admin()
   * @todo   lost the link to these
   */
  public static function adminHookCallbacks() {
    if (is_admin()) {
      add_filter('pre_update_default_ping_status', '__return_false');
      add_filter('pre_option_default_ping_status', '__return_zero');
      add_filter('pre_update_default_pingback_flag', '__return_false');
      add_filter('pre_option_default_pingback_flag', '__return_zero');
    }
  }

  /**
   * Conditional hook callbacks (front-end)
   *
   * @since  0.0.3
   * @access protected
   * @uses   is_admin()
   */
  public static function frontendHookCallbacks() {
    if (!is_admin()) {
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

  /**
   * Conditional hook callbacks (multisite)
   *
   * @since  0.0.3
   * @access protected
   * @uses   is_multisite()
   */
  public static function multisiteHookCallbacks() {
    if (is_multisite()) {
      add_action('admin_bar_menu', [ get_called_class(), 'removeNetworkCommentNodes' ], 500); }
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
    add_filter('wp_headers', [ get_called_class(), 'filterHeadPingback' ]);
    add_action('template_redirect', [ get_called_class(), 'filterFeedComments' ], 9); // before redirect_canonical
    add_action('init', [ get_called_class(), 'removePostTypeSupportComments' ]);
    static::multisiteHookCallbacks();
    static::adminHookCallbacks();
    static::frontendHookCallbacks();
  }
}
