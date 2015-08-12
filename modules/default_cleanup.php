<?php

namespace Formwerdung\Square\Modules;

class DefaultCleanup extends \Formwerdung\Square\Lib\DashboardWidget {
  public static $menu_label_key = 75; // Removes "Tools"
  public static $menu_label_cap = 'manage_options';
  protected static $widget_id = 'square-overview';
  protected static $widget_name = 'Overview';
  protected static $remove_mbs = array(
    'dashboard_right_now',
    'dashboard_recent_comments',
    'dashboard_incoming_links',
    'dashboard_plugins',
    'dashboard_quick_press',
    'dashboard_recent_drafts',
    'dashboard_primary',
    'dashboard_secondary',
    'dashboard_activity'
  );

  public static function widgetTemplate() {
    $post_types = static::buildPostTypeArray();
    echo static::renderTemplate('overview_widget.php', $post_types);
  }

  protected static function buildPostTypeArray() {
    // Loop through all post types
    $post_types = get_post_types(array('public' => true, '_builtin' => true), 'objects');
    if (static::isThemeFeature('square-remove-posts')) {
      unset($post_types['post']);
    }
    if (!static::isThemeFeature('square-remove-comments')) {
      $post_types['comments'] = 'comments';
    }
    return $post_types;
  }

  /**
   * Enqueues CSS, JavaScript, etc
   *
   * @mvc Controller
   */
  public static function loadResources() {
    wp_register_style(
      'square_admin_css',
      plugins_url( 'assets/css/square-admin.css', dirname( __FILE__ ) ),
      []
    );
    wp_enqueue_style( 'square_admin_css' );
  }

  public static function registerHookCallbacks() {
    add_action( 'admin_enqueue_scripts', array( get_called_class(), 'loadResources' ));
    add_action('admin_menu', array( get_called_class(), 'hideMenuItems'), 10);
    add_action('admin_menu', array( get_called_class(), 'removeMetaBoxes'), 12);
    add_action('admin_bar_menu', array( get_called_class(), 'removeNode'), 999);
    add_action('wp_dashboard_setup', array( get_called_class() , 'widgetInit') );
  }
}
