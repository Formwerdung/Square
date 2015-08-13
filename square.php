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

  /**
   * Plugin requires php version
   *
   * @var string constant
   */
  const REQUIRED_PHP_VERSION = '5.4';

  /**
   * Plugin requires WordPress version
   *
   * @var string constant
   */
  const REQUIRED_WP_VERSION = '4.2.4';

  /**
   * Is the requirements problem php
   *
   * @var bool
   */
  public static $is_problem_php;

  /**
   * Constructor, pass to bootstrapper
   *
   * @since  0.0.1
   * @access public
   */
  public function __construct() {
    $this->bootstrap();
  }

  /**
   * Bootstrapper, loads all the files that are required anyway
   * and the module loader for further checking of features
   *
   * @since  0.0.1
   * @access protected
   * @uses   add_action function
   * @return void
   */
  protected function bootstrap() {
    if ($this->requirementsMet(self::REQUIRED_PHP_VERSION, self::REQUIRED_WP_VERSION)) {
      require_once('lib/utils.php');
      require_once('lib/module.php');
      require_once('lib/admin.php');
      require_once('lib/dashboard_widget.php');
      require_once('modules.php');
      $modules = new Square\Modules();
    } else {
      add_action( 'admin_notices', [$this, 'requirementsError']);
    }
  }

  /**
   * Include requirement error view
   *
   * @since  0.0.5
   * @access public
   * @uses   $wp_version global string
   * @return void
   */
  public function requirementsError() {
    global $wp_version;

    include_once('views/requirements_error.php');
  }

  /**
   * Check if php & wp version requirements are met
   *
   * @since       0.0.1
   * @lastchanged 0.0.5
   * @access      protected
   * @uses        $wp_version      global string
   * @param       $req_php_version string
   * @param       $req_wp_version  string
   * @return      bool
   */
  protected function requirementsMet($req_php_version, $req_wp_version) {
    global $wp_version;
    if (version_compare(PHP_VERSION, $req_php_version, '<')) {
      self::$is_problem_php = true;
      return false;
    }
    if (version_compare($wp_version, $req_wp_version, '<')) {
      return false;
    }
    return true;
  }
}

/**
 * Init function
 *
 * @since       0.0.1
 * @lastchanged 0.0.5
 * @access      public
 * @return      void
 */
if (!function_exists('square_init')) {
  function square_init() {
    new Square();
  }
}
add_action('after_setup_theme', __NAMESPACE__ . '\\square_init');
