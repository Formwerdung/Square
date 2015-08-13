<?php

namespace Formwerdung\Square\Lib;

abstract class Module extends Utils {

  /**
   * Constructor for all modules. Enforces use of registerHookCallbacks, no add_action or add_filter
   * calls in the constructor allowed
   *
   * @since  0.0.1
   * @access protected
   */
  protected function __construct() {
    static::registerHookCallbacks();
  }

  /**
   * Render a template
   *
   * @since  0.0.1
   * @access protected
   * @param  string $temp_path  The path to the template, relative to the plugin's `views` folder
   * @param  array  $vars       An array of variables to pass into the template's scope, indexed with the variable name so that it can be extract()-ed
   * @param  string $req        'once' to use require_once() | 'always' to use require()
   * @return string
   */
  protected static function renderTemplate($temp_path = false, $vars = [], $req = 'once') {

    $template_path = locate_template(basename($temp_path));
    if (!$template_path) {
      $template_path = dirname(__DIR__) . '/views/' . $temp_path;
    }

    if (is_file($template_path)) {
      extract($vars);
      ob_start();

      if ('always' == $req) {
        require($template_path);
      } else {
        require_once($template_path);
      }
      $template_content = ob_get_clean();

    } else {
      $template_content = '';
    }

    return $template_content;
  }

  /**
   * Enforce use of hook callback function if default constructor is used.
   *
   * @since  0.0.1
   * @access protected
   */
  protected static function registerHookCallbacks() {
    throw new RuntimeException("Unimplemented");
  }
}
