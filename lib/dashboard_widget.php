<?php

namespace Formwerdung\Square\Lib;

abstract class DashboardWidget extends Admin {
  protected static $widget_id;
  protected static $widget_name;

  /**
   * Init function
   */
  public static function widgetInit() {
    wp_add_dashboard_widget(
      static::$widget_id,
      static::$widget_name,
      get_called_class() . '::widgetTemplate'
    );
  }

  /**
   * Render template
   */
  public static function widgetTemplate() {
    throw new RuntimeException("Unimplemented");
  }

  /**
   * Register hook callbacks
   */
  public static function registerHookCallbacks() {
    add_action('wp_dashboard_setup', [get_called_class() , 'widgetInit']);
    add_action('admin_menu', [ get_called_class(), 'removeNavLabel'], 10);
    add_action('admin_bar_menu', [ get_called_class(), 'removeNode'], 999);
  }
}
