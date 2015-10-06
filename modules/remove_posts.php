<?php

namespace Formwerdung\Square\Modules;

class RemovePosts extends \Formwerdung\Square\Lib\Admin {

  /**
   * @var    string $menu_label_key the key of the top level menu label to be removed (5 = posts, see link)
   * @access protected
   * @link   http://code.tutsplus.com/articles/customizing-your-wordpress-admin--wp-24941
   */
  protected static $menu_label_key = 5;

  /**
   * @var    string id of the admin node to be removed
   * @access protected
   */
  protected static $node_id = 'new-post';

  /**
   * Array of admin pages that should be redirected
   *
   * @var array
   * @access protected
   */
  protected static $redirected_pages = [
    'edit.php',
    'edit-tags.php',
    'post-new.php'
  ];

  /**
   * Custom implementation of redirecting certain links in the WordPress admin to the Dashboard
   * to allow for post types to shine through
   *
   * @since  0.0.2
   * @access public
   * @link   http://tonykwon.com/wordpress-plugins/wp-disable-posts/
   * @uses   $pagenow global string
   * @uses   $wp global object
   * @uses   wp_safe_redirect() function
   */
  public static function redirectAdminPages() {
    global $pagenow, $wp;

    $pages = static::$redirected_pages;
    if ($pages && is_array($pages)) {
      foreach ($pages as $page) {
        switch ($pagenow) {
          case $page:
            if (!array_key_exists('post_type', $_GET) && !array_key_exists('taxonomy', $_GET)) {
              wp_safe_redirect(get_admin_url(), 301);
            }
            break;
        }
      }
    }
  }

  /**
   * Implement a custom menu order. If posts are hidden, pages will be shown before Media
   *
   * @since  0.0.1
   * @access public
   * @return array order of the menu items
   */
  public static function customMenuOrder() {
    $menu_order = [
      'index.php',               // Dashboard
      'separator1',              // First separator
      'edit.php?post_type=page', // Pages
      'edit.php',                // Posts
      'upload.php',              // Media
      'edit-comments.php',       // Comments
      'separator2',              // Second separator
      'themes.php',              // Appearance
      'plugins.php',             // Plugins
      'users.php',               // Users
      'profile.php',             // Profile (for non-Admins)
      'tools.php',               // Tools
      'options-general.php',     // Settings
      'separator-last',          // Last separator
    ];
    return $menu_order;
  }

  /**
   * Checks the SQL statement to see if we are trying to fetch post_type `post`
   *
   * @since  0.0.3
   * @access public
   * @link   http://tonykwon.com/wordpress-plugins/wp-disable-posts/
   * @uses   $wp_query global object
   * @param  array $posts, found posts based on supplied SQL Query ($wp_query->request)
   * @return array $posts, found posts
   */
  public static function checkPostType($posts = []) {
    global $wp_query;

    $look_for = "wp_posts.post_type = 'post'";
    $instance = strpos($wp_query->request, $look_for);
    /*
		  http://localhost/?m=2013		- yearly archives
		  http://localhost/?m=201303		- monthly archives
		  http://localhost/?m=20130327	- daily archives
		  http://localhost/?cat=1			- category archives
		  http://localhost/?tag=foobar	- tag archives
		  http://localhost/?p=1			- single post
		*/
    if ($instance !== false) {
      $posts = []; // we are querying for post type `post`
    }
    return $posts;
  }

  /**
   * Excludes post type `post` to be returned from search
   *
   * @since  0.0.2
   * @access public
   * @link   http://tonykwon.com/wordpress-plugins/wp-disable-posts/
   * @return object $query wp_query object
   */
  public static function removeFromSearchFilter($query) {
    if (!is_search()) {
      return $query;
    }
    $post_types = get_post_types();
    if (array_key_exists('post', $post_types)) {
      unset($post_types['post']);
    }
    $query->set('post_type', array_values($post_types));
    return $query;
  }

  /**
   * Conditional hook callbacks (not admin or login)
   *
   * @since  0.0.3
   * @access protected
   * @link   http://tonykwon.com/wordpress-plugins/wp-disable-posts/
   * @uses   $pagenow global string
   */
  protected static function pagenowHookCallbacks() {
    global $pagenow;

    if (!is_admin() && ($pagenow != 'wp-login.php')) {
      add_action('posts_results', [ get_called_class(), 'checkPostType' ]);
      add_filter('pre_get_posts', [ get_called_class(), 'removeFromSearchFilter' ]);
    }
  }

  /**
   * Implement register hook callback function
   *
   * @since  0.0.1
   * @access public
   * @uses   add_action()
   * @uses   add_filter()
   */
  public static function registerHookCallbacks() {
    add_action('init', [ get_called_class(), 'redirectAdminPages' ]);
    add_action('admin_menu', [ get_called_class(), 'hideMenuItems' ], 10);
    add_action('admin_bar_menu', [ get_called_class(), 'removeNode' ], 999);
    add_filter('custom_menu_order', '__return_true');
    add_filter('menu_order', [ get_called_class(), 'customMenuOrder' ]);
    static::pagenowHookCallbacks();
  }
}
