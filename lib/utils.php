<?php

namespace Formwerdung\Square\Lib;

abstract class Utils {

  /**
   * Check if a given theme feature is activated
   */
  protected static function isThemeFeature($feat) {
    global $_wp_theme_features;
    if (isset($_wp_theme_features[$feat])) {
      return true;
    }
    return false;
  }
}
