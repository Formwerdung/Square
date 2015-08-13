<?php

namespace Formwerdung\Square;

class Modules extends Lib\Utils {

  /**
   * Constructor. Load modules, check activation hook and remove the role
   * (since we have no uninstall hook) if the feature is no longer set
   *
   * @since  0.0.1
   * @access public
   * @uses   register_activation_hook()
   * @uses   remove_role()
   */
  public function __construct() {
    $this->loadModules();
    register_activation_hook(__FILE__, [$this, 'activate']);
    if (!static::isThemeFeature('square-extra-user')) {
      remove_role('square-manager');
    }
  }

  /**
   * Loop through available modules, create feature name and basename from file
   *
   * @since  0.0.1
   * @access private
   */
  private function loadModules() {
    $this->loadDefaultModule();
    foreach (glob(__DIR__ . '/modules/*.php') as $file) {
      $basename = basename($file, '.php');
      $basename = \Formwerdung\Triangle::underscoresToDashes($basename);
      $feature = 'square-' . $basename;
      if ($this->isThemeFeature($feature)) {
        $this->loadModule($file, $basename);
      }
    }
  }

  /**
   * Load single module (=require module file), create class name from basename
   *
   * @since  0.0.1
   * @access private
   * @param  string  $f  file
   * @param  $tring  $bn basename
   */
  private function loadModule($f, $bn) {
    require_once($f);
    $class = \Formwerdung\Triangle::dashesToCamelCase($bn, true);
    $class = '\Formwerdung\Square\Modules\\' . $class;
    new $class;
  }

  /**
   * Load default module
   *
   * @since  0.0.1
   * @access private
   */
  private function loadDefaultModule() {
    require_once('modules/default_cleanup.php');
    new \Formwerdung\Square\Modules\DefaultCleanup;
  }

  /**
   * Things we have to do on activation
   *
   * @since  0.0.1
   * @access private
   * @uses   flush_rewrite_rules()
   */
  private function activate() {
    flush_rewrite_rules();
  }
}
