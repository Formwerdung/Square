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

  /**
   * Replace underscores with dashes in string
   */
  public static function underscoresToDashes($str) {
    $string = str_replace('_', '-', $str);
    return $string;
  }

  /**
   * Remove dashes in strings and convert to camel case
   *
   * @url http://stackoverflow.com/questions/2791998/convert-dashes-to-camelcase-in-php
   */
  public static function dashesToCamelCase($str, $capFirstChar = false) {
    $string = str_replace(' ', '', ucwords(str_replace('-', ' ', $str)));
    if (!$capFirstChar) {
      $string = lcfirst($string);
    }
    return $string;
  }
}
