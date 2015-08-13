<?php
/*
Plugin Name: Square
Plugin URI:  https://github.com/johnnyicarus/square
Description: A WordPress mu-plugin which contains a collection of modules to apply theme-agnostic back-end modifications.
Version:     0.0.4
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
   * @access public
   * @var    string
   */
  public $required_php_version = '5.4';

  /**
   * Plugin requires WordPress version
   *
   * @access public
   * @var    string
   */
  public $required_wp_version = '4.2.4';

  /**
   * Is the requirements problem php
   *
   * @access public
   * @var    bool
   */
  public $is_problem_php = false;

  /**
   * Constructor, pass to bootstrapper
   *
   * @since  0.0.1
   * @access public
   * @return void
   */
  public function __construct() {
    $this->bootstrap();
  }

  /**
   * Bootstrapper, loads all the files that are required anyway
   * and the module loader for further checking of features
   *
   * @since  0.0.1
   * @access private
   * @uses   add_action function
   * @return void
   */
  private function bootstrap() {
    if ($this->requirementsMet($this->required_php_version, $this->required_wp_version)) {
      require_once('lib/utils.php');
      require_once('lib/module.php');
      require_once('lib/admin.php');
      require_once('lib/dashboard_widget.php');
      require_once('modules.php');
      $modules = new Square\Modules();
    } else {
      add_action('admin_notices', [$this, 'requirementsError']);
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
   * @access      private
   * @uses        $wp_version      global string
   * @param       $req_php_version string
   * @param       $req_wp_version  string
   * @return      bool
   */
  private function requirementsMet($req_php_version, $req_wp_version) {
    global $wp_version;
    if (version_compare(PHP_VERSION, $req_php_version, '<')) {
      $this->is_problem_php = true;
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
