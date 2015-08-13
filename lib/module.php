<?php

namespace Formwerdung\Square\Lib;

abstract class Module extends Utils {
  public static $capability;

  public function __construct() {
    static::registerHookCallbacks();
  }

  /**
   * Render a template
   *
   * Allows parent/child themes to override the markup by placing the a file named basename( $default_template_path ) in their root folder,
   * and also allows plugins or themes to override the markup by a filter. Themes might prefer that method if they place their templates
   * in sub-directories to avoid cluttering the root folder. In both cases, the theme/plugin will have access to the variables so they can
   * fully customize the output.
   *
   * @mvc @model
   *
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
   * Hook callbacks
   */
  public static function registerHookCallbacks() {
    throw new RuntimeException("Unimplemented");
  }
}
