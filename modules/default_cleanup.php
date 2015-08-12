<?php

namespace Formwerdung\Square\Modules;

class DefaultCleanup extends \Formwerdung\Square\Lib\DashboardWidget {

  /**
   * Key of menu label that is to be removed
   * @url http://code.tutsplus.com/articles/customizing-your-wordpress-admin--wp-24941
   */
  public static $menu_label_key = 75; // Removes "Tools"

  /**
   * Capability user has to have for the menu label to NOT be hidden
   */
  public static $menu_label_cap = 'manage_options';

  /**
   * ID of new overview widget
   */
  protected static $widget_id = 'square-overview';

  /**
   * Name of new overview widget
   */
  protected static $widget_name = 'Overview';

  /**
   * Array of mbs for removal
   */
  protected static $remove_mbs = [
    'dashboard_right_now',
    'dashboard_recent_comments',
    'dashboard_incoming_links',
    'dashboard_plugins',
    'dashboard_quick_press',
    'dashboard_recent_drafts',
    'dashboard_primary',
    'dashboard_secondary',
    'dashboard_activity'
  ];

  /**
   * Call on the view and pass post type array to it
   */
  public static function widgetTemplate() {
    $post_types = static::buildPostTypeArray();
    echo static::renderTemplate('overview_widget.php', $post_types);
  }

  /**
   * Enqueues stylesheet for the overview widget
   *
   * @return void
   */
  public static function loadResources() {
    wp_register_style(
      'square_admin_css',
      plugins_url('assets/css/square-admin.css', dirname(__FILE__)),
      []
    );
    wp_enqueue_style('square_admin_css');
  }

  /**
   * Register hook callbacks
   *
   * @return void
   */
  public static function registerHookCallbacks() {
    add_action('admin_enqueue_scripts', [ get_called_class(), 'loadResources' ]);
    add_action('admin_menu', [ get_called_class(), 'hideMenuItems'], 10);
    add_action('admin_menu', [ get_called_class(), 'removeMetaBoxes'], 12);
    add_action('admin_bar_menu', [ get_called_class(), 'removeNode'], 999);
    add_action('wp_dashboard_setup', [ get_called_class() , 'widgetInit']);
  }
}
