<?php

namespace Formwerdung\Square\Modules;

class GoogleMapsShortcode {

  static protected $sc_beginning = '<div class="google-map"><iframe
    frameborder="0"
    scrolling="no"
    marginheight="0"
    marginwidth="0"
    src="https://www.google.com/maps/embed/v1/';

  static protected $api_key = 'API_KEY';

  static protected $sc_end = '"></iframe></div>';

  static protected $atts = [
    'mode'   => 'place',
    'search' => '', // place
    'center' => '', // required for view, optional for others
    'zoom'   => '', // general (optional)
    'maptype'   => '', // general (optional)
    'language' => '', // general (optional)
    'region' => '', // general (optional)
    'origin' => '', // directions
    'destination' => '', // directions
    'waypoints' => '', // directions (optional)
    'directions_mode' => '', // directions (optional)
    'avoid' => '', // directions (optional)
    'units' => '', // directions (optional)
    'location' => '', // streetview
    'pano' => '', // streetview
    'heading' => '', // streetview (optional)
    'pitch' => '', // streetview (optional)
    'fov' => '' // streetview (optional)
  ];

  /**
   * Make neat shortcode.
   */
  public static function buildEmbedString($atts) {
    $atts = self::checkAtts($atts);
    extract($atts);

    $shortcode = self::$sc_beginning . $mode . '?key=' . self::$api_key;

    if ($mode == 'directions') {
      $shortcode .= '&origin=' . $origin . '&destination=' . $destination;
    } else if ($mode === 'view') {
      $shortcode .= '&center=' . $center;
    } else if ($mode === 'streetview') {
      if (empty($pano)) {
        $shortcode .= '&location=' . $location;
      } else if (empty($location)) {
        $shortcode .= '&pano=' . $pano;
      } else {
        $shortcode .= '&location=' . $location . '&pano=' . $pano;
      }
    } else {
      $shortcode .= '&q=' . $search;
    }

    $optionals_array = [
      'zoom' => $zoom,
      'maptype' => $maptype,
      'waypoints' => $waypoints,
      'language' => $language,
      'region' => $region,
      'mode' => $directions_mode,
      'avoid' => $avoid,
      'units' => $units,
      'heading' => $heading,
      'pitch' => $pitch,
      'fov' => $fov
    ];
    if ($mode != 'view') {
      $optionals_array['center'] = $center;
    }
    $optional_str = self::createOptionalsStr($optionals_array);
    $shortcode .= $optional_str;

    $shortcode .= self::$sc_end;

    return $shortcode;
  }

  /**
   * Main checking function for attributes
   */
  protected static function checkAtts($atts) {
    if (array_key_exists('mode', $atts))
      $atts['mode'] = self::addFallbackValue($atts['mode'], ['place', 'directions', 'search', 'view', 'streetview'], 'place');
    $atts = shortcode_atts(self::$atts, $atts);
    $atts = self::sanitize($atts);
    return $atts;
  }

  /**
   * @url https://developers.google.com/maps/documentation/embed/guide
   */
  protected static function sanitize($atts) {
    $atts['search']          = self::plusSpaces($atts['search']);
    $atts['zoom']            = intval($atts['zoom']);
    $atts['maptype']         = self::addFallbackValue($atts['maptype'], ['roadmap', 'satellite'], '');
    $atts['avoid']           = self::addFallbackValue($atts['avoid'], ['tolls', 'ferries', 'highways'], '');
    $atts['avoid']           = self::pipeCommas($atts['avoid']);
    $atts['waypoints']       = self::removeCommaSpace($atts['waypoints']);
    $atts['waypoints']       = self::pipeSpaces($atts['waypoints']);
    $atts['directions_mode'] = self::addFallbackValue($atts['directions_mode'], ['driving', 'walking', 'bicycling', 'transit', 'flying'], 'driving');
    $atts['units']           = self::addFallbackValue($atts['units'], ['metric', 'imperial'], 'metric');
    $atts['heading']         = intval($atts['heading']);
    $atts['pitch']           = intval($atts['pitch']);
    $atts['fov']             = intval($atts['fov']);
    return $atts;
  }

  /**
   * Compare a string with an array. If it is not in the array, replace with a specified
   * fallback string. Sanitizes for certain keyword entries in the Google Maps Embed API.
   *
   */
  protected static function addFallbackValue($str, $cases = [], $default) {
    if (!empty($str)) {
      if (!in_array($str, $cases, true)) {
        $str = $default;
      }
    }
    return $str;
  }

  protected static function createOptionalsStr($optionals_array = []) {
    $optionals = self::prepOptionals($optionals_array);
    if (!empty($optionals)) {
      foreach ($optionals as $key => $value) {
        $optionals_str = '&' . $key . '=' . $value;
      }
      return $optionals_str;
    }
  }

  protected static function prepOptionals($optionals = []) {
    $new_optionals = [];
    foreach ($optionals as $key => $value) {
      if (!empty($value)) {
        $new_optionals[$key] = $value;
      }
    }
    return $new_optionals;
  }

  protected static function removeCommaSpace($str) {
    $str = preg_replace('/,\s+/', ',', $str);
    return $str;
  }

  protected static function pipeSpaces($str) {
    $str = preg_replace('/\s+/', '|', $str);
    return $str;
  }

  protected static function pipeCommas($str) {
    $str = preg_replace('/,\s+/', '|', $str);
    $str = preg_replace('/,/', '|', $str);
    return $str;
  }

  protected static function plusSpaces($str) {
    $str = preg_replace('/\s+/', '+', $str);
    return $str;
  }
}

add_shortcode("googlemap", [__NAMESPACE__ . '\GoogleMapsShortcode', 'buildEmbedString']);
