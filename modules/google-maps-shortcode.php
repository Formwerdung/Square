<?php

namespace Formwerdung\Square\Modules;

class GoogleMapsShortcode extends \Formwerdung\Square\Lib\Module {

  /**
   * @var    string $sc_beginning markup for the google maps shortcode
   * @access protected
   * @link   https://developers.google.com/maps/documentation/embed/guide#top_of_page
   */
  static protected $sc_beginning = '<div class="google-map"><iframe
    frameborder="0"
    scrolling="no"
    marginheight="0"
    marginwidth="0"
    src="https://www.google.com/maps/embed/v1/';

  /**
   * @var    string $sc_end markup for the google maps shortcode
   * @access protected
   * @link   https://developers.google.com/maps/documentation/embed/guide#top_of_page
   */
  static protected $sc_end = '"></iframe></div>';

  /**
   * @var    array $atts possible attributes for a Google Maps embed
   * @access protected
   * @link   https://developers.google.com/maps/documentation/embed/guide#top_of_page
   */
  static protected $atts = [
    'api_key' => 'API_KEY',
    'type'   => 'place',
    'search' => '', // place
    'center' => '', // required for view, optional for others
    'zoom'   => '', // general (optional)
    'maptype'   => '', // general (optional)
    'language' => '', // general (optional)
    'region' => '', // general (optional)
    'origin' => '', // directions
    'destination' => '', // directions
    'waypoints' => '', // directions (optional)
    'mode' => '', // directions (optional)
    'avoid' => '', // directions (optional)
    'units' => '', // directions (optional)
    'location' => '', // streetview
    'pano' => '', // streetview
    'heading' => '', // streetview (optional)
    'pitch' => '', // streetview (optional)
    'fov' => '' // streetview (optional)
  ];

  /**
   * @var array $optionals array of optional attributes for an embed
   * @access protected
   */
  static protected $optionals = [
    'zoom',
    'maptype',
    'waypoints',
    'language',
    'region',
    'mode',
    'avoid',
    'units',
    'heading',
    'pitch',
    'fov'
  ];

  /**
   * Build the embed markup string, a "constructor" for the shortcode.
   *
   * @since  0.0.7
   * @access public
   * @param  $atts array of attributes
   */
  public static function buildEmbedString($atts) {
    $atts = self::checkAtts($atts);

    $shortcode = self::$sc_beginning . $atts['type'] . '?key=' . $atts['api_key'];
    $shortcode .= self::addTypeBased($atts);
    $shortcode .= self::addOptionals($atts);
    $shortcode .= self::$sc_end;

    return $shortcode;
  }

  /**
   * Dynamically build a first big part of the embed markup based on logic relating
   * to the map types available
   *
   * @since  0.0.7
   * @access protected
   * @param  $atts array of shortcode attributes
   * @return $str  string of partial shortcode goodness
   */
  protected static function addTypeBased($atts) {
    $str = '';
    switch ($atts['type']) {
      case 'directions':
        $str .= '&origin=' . $atts['origin'] . '&destination=' . $atts['destination'];
        break;
      case 'view':
        $str .= '&center=' . $atts['center'];
        break;
      case 'streetview':
        if (empty($atts['pano'])) {
          $str .= '&location=' . $atts['location'];
        } else if (empty($atts['location'])) {
          $str .= '&pano=' . $atts['pano'];
        } else {
          $str .= '&location=' . $atts['location'] . '&pano=' . $atts['pano'];
        }
        break;
      default:
        $str .= '&q=' . $atts['search'];
        break;
    }
    return $str;
  }

  /**
   * Add optional parameters, should there be any. Optionals are defined by static array.
   *
   * @since  0.0.7
   * @access protected
   * @param  $atts array of shortcode attributes
   * @return $str string of partial shortcode goodness
   */
  protected static function addOptionals($atts) {
    $str = '';
    $optionals = self::$optionals;
    if ($atts['type'] != 'view') {
      $optionals[] = 'view';
    }
    foreach ($optionals as $optional) {
      if (array_key_exists($optional, $atts)) {
        $str .= '&' . $optional . '=' . $atts[$optional];
      }
    }
    return $str;
  }

  /**
   * Main checking function for attributes. Possible attributes/defaults are from static array.
   *
   * @since  0.0.7
   * @access protected
   * @param  $atts array of shortcode attributes
   * @uses   shortcode_atts()
   * @return $atts array of fixed stuff
   */
  protected static function checkAtts($atts) {
    if (array_key_exists('type', $atts)) {
      $atts['type'] = self::addFallbackValue($atts['type'], ['place', 'directions', 'search', 'view', 'streetview'], 'place'); }
    $atts = shortcode_atts(self::$atts, $atts);
    $atts = self::sanitize($atts);
    $atts = array_filter($atts);
    return $atts;
  }

  /**
   * Sanitation beyond WordPress built in stuff. Makes more forgiving user inputs possible.
   *
   * @since  0.0.7
   * @access protected
   * @param  $atts array of shortcode attributes
   * @return $atts array of fixed stuff
   */
  protected static function sanitize($atts) {
    $atts['search']    = self::plusSpaces($atts['search']);
    $atts['maptype']   = self::addFallbackValue($atts['maptype'], ['roadmap', 'satellite'], '');
    $atts['avoid']     = self::addFallbackValue($atts['avoid'], ['tolls', 'ferries', 'highways'], '');
    $atts['avoid']     = self::pipeCommas($atts['avoid']);
    $atts['waypoints'] = self::removeCommaSpace($atts['waypoints']);
    $atts['waypoints'] = self::pipeSpaces($atts['waypoints']);
    $atts['mode']      = self::addFallbackValue($atts['mode'], ['driving', 'walking', 'bicycling', 'transit', 'flying'], 'driving');
    $atts['units']     = self::addFallbackValue($atts['units'], ['metric', 'imperial'], 'metric');
    return $atts;
  }

  /**
   * Compare a string with an array. If it is not in the array, replace with a specified
   * fallback string. Sanitizes for certain keyword entries in the Google Maps Embed API.
   *
   * @since  0.0.7
   * @access protected
   * @param  $str string to compare
   * @param  $cases array to compare string against
   * @param  $default string to take over if other is not in array
   * @return $str either $default now or unchanged
   */
  protected static function addFallbackValue($str, $cases, $default) {
    if (!empty($str)) {
      if (!in_array($str, $cases, true)) {
        $str = $default;
      }
    }
    return $str;
  }

  /**
   * String manipulation. Removes a space behind the commas so we can use "Berlin, Germany" as waypoints.
   *
   * @since  0.0.7
   * @access protected
   * @param  $str
   * @return $str
   */
  protected static function removeCommaSpace($str) {
    $str = preg_replace('/,\s+/', ',', $str);
    return $str;
  }

  /**
   * String manipulation. Removes a space behind the commas so we can multiple avoids, waypoints etc.
   * Should be called after removeCommaSpace in sanitizing waypoints.
   *
   * @since  0.0.7
   * @access protected
   * @param  $str
   * @return $str
   */
  protected static function pipeSpaces($str) {
    $str = preg_replace('/\s+/', '|', $str);
    return $str;
  }

  /**
   * String manipulation. Allow the user to use comma separated lists, when really he should be
   * using the pipe according to the API.
   *
   * @since  0.0.7
   * @access protected
   * @param  $str
   * @return $str
   */
  protected static function pipeCommas($str) {
    $str = preg_replace('/,\s+/', '|', $str);
    $str = preg_replace('/,/', '|', $str);
    return $str;
  }

  /**
   * String manipulation. Allows the user to use spaces like a normal person.
   *
   * @since  0.0.7
   * @access protected
   * @param  $str
   * @return $str
   */
  protected static function plusSpaces($str) {
    $str = preg_replace('/\s+/', '+', $str);
    return $str;
  }

  /**
   * Enqueue a stylesheet and a script for the overview widget
   *
   * @since  0.0.7
   * @access public
   * @uses   wp_register_style()
   * @uses   wp_enqueue_style()
   */
  public static function loadResources() {
    wp_register_style(
      'square_gmap_css',
      plugins_url('assets/css/square-gmap.css', dirname(__FILE__)),
      []
    );
    wp_enqueue_style('square_gmap_css');

    wp_enqueue_script(
        'square_gmap_js',
      plugins_url('assets/js/square-gmap.js', dirname(__FILE__)),
      ['jquery'],
      false,
      true
    );
  }

  /**
   * Register my hook callbacks, here it's assets.
   *
   * @since  0.0.7
   * @access public
   * @uses   add_action() @ wp_enqueue_scripts
   */
  public static function registerHookCallbacks() {
    add_action('wp_enqueue_scripts', [get_called_class(), 'loadResources']);
  }
}
// Add that shortcode
add_shortcode("googlemap", [__NAMESPACE__ . '\GoogleMapsShortcode', 'buildEmbedString']);
