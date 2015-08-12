<?php

namespace Formwerdung\Square\Modules;

class RemovePosts extends \Formwerdung\Square\Lib\Admin {
  public static $menu_label_key = 5;
  public static $node_id = 'new-post';

  /**
   * Array of admin pages that should be redirected
   *
   * @access protected
   */
  protected static $redirected_pages = [
    'edit.php',
    'edit-tags.php',
    'post-new.php'
  ];


  /**
   * Redirect certain links in the WordPress admin to the Dashboard
   *
   * @access public
   * @param  none,   is used in add_action
   * @uses   global $pagenow
   * @uses   wp_safe_redirect()
   * @return void
   */
  public static function redirectAdminPages() {
    global $pagenow, $wp;

    $pages = static::$redirected_pages;
    if ($pages && is_array($pages)) {
      foreach ($pages as $page) {
        switch ($pagenow) {
          case $page:
            if (!array_key_exists('post_type', $_GET) && !array_key_exists('taxonomy', $_GET) && !$_POST) {
              wp_safe_redirect(get_admin_url(), 301);
            }
            break;
        }
      }
    }
  }

  public static function customMenuOrder() {
    $menu_order = array(
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
    );
    return $menu_order;
  }

  /**
     * Checks the SQL statement to see if we are trying to fetch post_type `post`
     *
   * @link   http://tonykwon.com/wordpress-plugins/wp-disable-posts/
   *
     * @access public
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
        $posts = array(); // we are querying for post type `post`
    }

        return $posts;
    }

  /**
     * excludes post type `post` to be returned from search
     *
   * @link   http://tonykwon.com/wordpress-plugins/wp-disable-posts/
   *
     * @access public
     * @param  null
     * @return object $query, wp_query object
     */
    public static function removeFromSearchFilter($query) {
      if (!is_search()) {
          return $query;
      }

        $post_types = get_post_types();

      if (array_key_exists('post', $post_types)) {
          /* exclude post_type `post` from the query results */
          unset($post_types['post']);
      }
        $query->set('post_type', array_values($post_types));

        return $query;
    }

  /**
   * Conditional hook callbacks
   *
   * @uses   global $pagenow
   *
   * @access protected
   * @param  none
   * @return void
   */
    protected static function pagenowHookCallbacks() {
      global $pagenow;

      if (!is_admin() && ($pagenow != 'wp-login.php')) {
            add_action('posts_results', [ get_called_class(), 'checkPostType' ]);
            add_filter('pre_get_posts', [ get_called_class(), 'removeFromSearchFilter' ]);
      }
    }

    public static function registerHookCallbacks() {
      add_action('init', [ get_called_class(), 'redirectAdminPages']);
      add_action('admin_menu', array(get_called_class(), 'hideMenuItems'), 10);
      add_action('admin_bar_menu', array(get_called_class(), 'removeNode'), 999);
      add_filter('custom_menu_order', '__return_true');
      add_filter('menu_order', array(get_called_class(), 'customMenuOrder'));

      static::pagenowHookCallbacks();
    }
}
