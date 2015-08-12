<?php

namespace Formwerdung\Square\Modules;

class RemovePosts extends \Formwerdung\Square\Lib\Admin {
  public static $menu_label_key = 5;
  public static $node_id = 'new-post';

  /**
   * Array of admin pages that should be redirected
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
   * @param  array  $pages
   * @uses   global $pagenow
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


  public static function registerHookCallbacks() {
    add_action('init', [ get_called_class(), 'redirectAdminPages']);
    add_action('admin_menu', array(get_called_class(), 'hideMenuItems'), 10);
    add_action('admin_bar_menu', array(get_called_class(), 'removeNode'), 999);
    add_filter('custom_menu_order', '__return_true');
    add_filter('menu_order', array(get_called_class(), 'customMenuOrder'));
  }
}
