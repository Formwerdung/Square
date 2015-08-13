<?php

namespace Formwerdung\Square\Lib;

class DashboardWidget extends Admin {

  /**
   * @var   string ID of the widget we're gonna create
   * @access protected
   */
  protected static $widget_id;

  /**
   * @var    string name of the widget we're gonna create
   * @access protected
   */
  protected static $widget_name;

  /**
   * Wrapper function for wp_add_dashboard_widget
   *
   * @since  0.0.1
   * @access public
   * @uses   wp_add_dashboard_widget()
   */
  public static function widgetInit() {
    wp_add_dashboard_widget(
      static::$widget_id,
      static::$widget_name,
      get_called_class() . '::widgetTemplate'
    );
  }

  /**
   * Enforce implementation of widgetTemplate whose sole purpose is to pass data to the view
   *
   * @since  0.0.1
   * @access public
   */
  public static function widgetTemplate() {
    throw new RuntimeException("Unimplemented");
  }
}
