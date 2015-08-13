<?php

namespace Formwerdung\Square\Lib;

abstract class Utils {

  /**
   * Check if a given theme feature is activated
   *
   * @since  0.0.1
   * @access protected
   * @uses   $_wp_theme_features array global
   * @return bool
   */
  protected static function isThemeFeature($feat) {
    global $_wp_theme_features;
    if (isset($_wp_theme_features[$feat])) {
      return true;
    }
    return false;
  }
}
