<?php

namespace Formwerdung\Square\Modules;

class DefaultCleanup extends \Formwerdung\Square\Lib\DashboardWidget {

  /**
   * @var    string $menu_label_key the key of the top level menu label to be removed (75 = tools, see link)
   * @access protected
   * @link   http://code.tutsplus.com/articles/customizing-your-wordpress-admin--wp-24941
   */
  protected static $menu_label_key = 75;

  /**
   * @var    string $menu_label_cap capability that user needs to have to not have the menu label hidden
   * if it is not set, label will be hidden for all users
   * @access protected
   */
  protected static $menu_label_cap = 'manage_options';

  /**
   * @var    array of meta-box-ids for their removal
   * @access protected
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
   * @var   string ID of the widget we're gonna create
   * @access protected
   */
  protected static $widget_id = 'square-overview';

  /**
   * @var    string name of the widget we're gonna create
   * @access protected
   */
  protected static $widget_name = 'Overview';

  /**
   * Overview widget implementation of widgetTemplate
   *
   * @since  0.0.1
   * @access public
   */
  public static function widgetTemplate() {
    $post_types = static::buildPostTypeArray();
    echo static::renderTemplate('overview_widget.php', $post_types);
  }

  /**
   * Enqueue a stylesheet for the overview widget
   *
   * @since  0.0.1
   * @access public
   * @uses   wp_register_style()
   * @uses   wp_enqueue_style()
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
   * Implement register hook callback function
   *
   * @since  0.0.1
   * @access public
   * @uses   add_action()
   */
  public static function registerHookCallbacks() {
    add_action('admin_enqueue_scripts', [ get_called_class(), 'loadResources' ]);
    add_action('admin_menu', [ get_called_class(), 'hideMenuItems' ], 10);
    add_action('admin_menu', [ get_called_class(), 'removeMetaBoxes' ], 12);
    add_action('admin_bar_menu', [ get_called_class(), 'removeNode' ], 999);
    add_action('wp_dashboard_setup', [ get_called_class() , 'widgetInit' ]);
  }
}
