<?php

namespace Formwerdung\Square;

class Modules extends Lib\Utils {

  public function __construct() {
    $this->loadModules();
    register_activation_hook(__FILE__, array($this, 'activate'));
    if (!static::isThemeFeature('square-extra-user')) {
      remove_role('square-manager');
    }
  }

  /**
   * Loop through available modules, create feature name and basename from file
   */
  protected function loadModules() {
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
   * Load single module, create class name from basename
   */
  protected function loadModule($f, $bn) {
    require_once($f);
    $class = \Formwerdung\Triangle::dashesToCamelCase($bn, true);
    $class = '\Formwerdung\Square\Modules\\' . $class;
    new $class;
  }

  /**
   * Load default module
   */
  protected function loadDefaultModule() {
    require_once('modules/default_cleanup.php');
    new \Formwerdung\Square\Modules\DefaultCleanup;
  }



  protected function activate() {
    flush_rewrite_rules();
  }
}
