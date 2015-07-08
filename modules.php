<?php

namespace Formwerdung\Square;

class Modules extends Lib\Utils {
  protected static $i = 0;

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
    $this->loadDefaultModule(self::$i);
    self::$i++;
    foreach (glob(__DIR__ . '/modules/*.php') as $file) {
      $basename = basename($file, '.php');
      $basename = $this->underscoresToDashes($basename);
      $feature = 'square-' . $basename;
      if ($this->isThemeFeature($feature)) {
        $this->loadModule($file, $basename, self::$i);
        self::$i++;
      }
    }
  }

  /**
   * Load single module, create class name from basename
   */
  protected function loadModule($f, $bn, $i) {
    require_once($f);
    $class = $this->dashesToCamelCase($bn, true);
    $class = '\Formwerdung\Square\Modules\\' . $class;
    $i = new $class;
  }

  /**
   * Load default module
   */
  protected function loadDefaultModule($i) {
    require_once('modules/default_cleanup.php');
    $i = new \Formwerdung\Square\Modules\DefaultCleanup;
  }

  protected function activate() {
    flush_rewrite_rules();
  }
}
