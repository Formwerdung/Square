<?php
/*
Plugin Name: Square
Plugin URI:  https://github.com/johnnyicarus/square
Description: Easily customize the WordPress Backend without settings.
Version:     0.1.0
Author:      Formwerdung
Author URI:  http://formwerdung.ch

License:     MIT License
License URI: http://opensource.org/licenses/MIT
*/

namespace Formwerdung;

class Square {
  const REQUIRED_PHP_VERSION = '5.4';
  const REQUIRED_WP_VERSION = '4.0';

  // go go factory
  public function __construct() {
    $this->bootstrap();
  }

  protected function bootstrap() {
    if ($this->requirementsMet(self::REQUIRED_PHP_VERSION, self::REQUIRED_WP_VERSION)) {
      require_once('lib/utils.php');
      require_once('lib/module.php');
      require_once('lib/admin.php');
      require_once('lib/dashboard_widget.php');
      require_once('modules.php');
      $modules = new Square\Modules();
    } else {
      return false;
      // @todo: actual error message
    }
  }

  // check if php & wp version requirements are met
  public function requirementsMet($req_php_version, $req_wp_version) {
    global $wp_version;
    if (version_compare(PHP_VERSION, $req_php_version, '<')) {
      return false;
    }
    if (version_compare($wp_version, $req_wp_version, '<')) {
      return false;
    }
    return true;
  }
}

function square_init() {
  new Square();
}
add_action('after_setup_theme', __NAMESPACE__ . '\\square_init');
