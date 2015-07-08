<?php

namespace Formwerdung\Square\Modules;

class DefaultCleanup extends \Formwerdung\Square\Lib\DashboardWidget {
  public static $label_key = 75; // Removes "Tools"
  public static $capability = 'manage_options';
  protected static $widget_id = 'overview';
  protected static $widget_name = 'Overview';
  protected static $meta_boxes_for_removal = array(
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
  protected static $use_capability_meta_boxes = false;
  protected static $capability_meta_box = 'manage_options';

  public static function widgetTemplate() {
    $post_types = static::buildPostTypeArray();
    echo static::renderTemplate('overview_widget.php', $post_types);
  }

  protected static function buildPostTypeArray() {
    // Loop through all post types
    $post_types = get_post_types(array('public' => true, '_builtin' => true), 'objects');
    // @todo this should not be repeated, it's already in \Formwerdung\Square\Modules
    if (static::isThemeFeature('square-remove-posts')) {
      unset($post_types['post']);
    }
    if (!static::isThemeFeature('square-remove-comments')) {
      $post_types['comments'] = 'comments';
    }
    return $post_types;
  }

  public static function registerHookCallbacks() {
    add_action('admin_menu', array( get_called_class(), 'removeNavLabel'), 10);
    add_action('admin_menu', array(get_called_class(), 'removeNavLabel'), 11);
    add_action('admin_menu', array( get_called_class(), 'removeMetaBoxes'), 12);
    add_action('admin_bar_menu', array( get_called_class(), 'removeNode'), 999);
    add_action('wp_dashboard_setup', array(get_called_class() , 'widgetInit'));
  }
}
