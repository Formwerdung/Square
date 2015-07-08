<?php

namespace Formwerdung\Square\Modules;

class RemovePosts extends \Formwerdung\Square\Lib\DashboardWidget {
  public static $label_key = 5;
  public static $node_id = 'new-post';

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
    add_action('admin_menu', array(get_called_class(), 'removeNavLabel'), 10);
    add_action('admin_bar_menu', array(get_called_class(), 'removeNode'), 999);
    add_filter('custom_menu_order', '__return_true');
    add_filter('menu_order', array(get_called_class(), 'customMenuOrder'));
  }
}
