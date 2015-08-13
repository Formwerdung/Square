<?php

namespace Formwerdung\Square\Lib;

class Admin extends Module {

  /**
   * @var    string the key of the top level menu label to be removed (see link for list)
   * @access protected
   * @link   http://code.tutsplus.com/articles/customizing-your-wordpress-admin--wp-24941
   */
  protected static $menu_label_key;

  /**
   * @var    string capability that user needs to have to not have the menu label hidden (not set = hidden for all users)
   * @access protected
   */
  protected static $menu_label_cap;

  /**
   * @var    array key is the top level menu basename, value the submenu level basename (including '.php')
   * @access protected
   */
  protected static $submenu_labels = [];

  /**
   * @var    string capability that user needs to have to not have submenu labels hidden (not set = hidden for all users)
   * @access protected
   */
  protected static $submenu_label_cap;

  /**
   * @var    string id of the admin node to be removed
   * @access protected
   */
  protected static $node_id;

  /**
   * @var    string capability that user needs to have to not have the admin node removed (not set = removed for all users)
   * @access protected
   */
  protected static $remove_node_cap;

  /**
   * @var    array of meta-box-ids for their removal
   * @access protected
   */
  protected static $remove_mbs = [];

  /**
   * @var    string capability that user needs to have to not have the admin node removed (not set = removed for all users)
   * @access protected
   */
  protected static $remove_mbs_cap;

  /**
   * Array of admin pages that should be redirected
   *
   * @var array
   * @access protected
   */
  protected static $redirected_pages = [];

  /**
   * Hide top level menu items (1 per child class)
   *
   * @since  0.0.1
   * @access public
   * @uses   current_user_can() function
   * @uses   $menu global object/array
   */
  public static function hideMenuItems() {
    if (!isset(static::$menu_label_cap) || !current_user_can(static::$menu_label_cap)) {
      global $menu;
      unset($menu[static::$menu_label_key]);
    }
  }

  /**
   * Hide submenu items (based on capability if set)
   *
   * @since  0.0.1
   * @access public
   * @uses   current_user_can() function
   * @uses   remove_submenu_page() function
   */
  public static function hideSubmenuItems() {
    if (!isset(static::$submenu_label_cap) || !current_user_can(static::$submenu_label_cap)) {
      $submenu_labels = static::$submenu_labels;
      foreach ($submenu_labels as $menu_slug => $submenu_slug) {
        remove_submenu_page($menu_slug, $submenu_slug);
      }
    }
  }

  /**
   * Remove admin node (based on capability if set)
   *
   * @since  0.0.1
   * @access public
   * @uses   current_user_can() function
   * @uses   remove_node() function
   */
  public static function removeNode($wp_admin_bar) {
    if (!isset(static::$remove_node_cap) || !current_user_can(static::$remove_node_cap)) {
      $wp_admin_bar->remove_node(static::$node_id);
    }
  }

  /**
   * Loop through meta-boxes to remove them
   *
   * @since  0.0.1
   * @access public
   * @uses   current_user_can() function
   * @uses   remove_meta_box() function
   */
  public static function removeMetaBoxes() {
    $meta_boxes = static::buildMetaBoxArray();
    if (!isset(static::$mb_cap) || !current_user_can(static::$mb_cap)) {
      foreach ($meta_boxes as $meta_box) {
        remove_meta_box($meta_box['id'], $meta_box['page'], $meta_box['context']);
      }
    }
  }

  /**
   * Redirect certain links in the WordPress admin to the Dashboard
   *
   * @since       0.0.2
   * @lastchanged 0.0.3
   * @access      public
   * @uses        global $pagenow
   * @uses        wp_safe_redirect() function
   */
  public static function redirectAdminPages() {
    global $pagenow;

    $pages = static::$redirected_pages;
    if ($pages && is_array($pages)) {
      foreach ($pages as $page) {
        switch ($pagenow) {
          case $page:
            wp_safe_redirect(get_admin_url(), 301);
            break;
        }
      }
    }
  }

  /**
   * Make a meta box array to comfortably loop through
   *
   * @since  0.0.1
   * @access protected
   * @param  $screen string the screen on which to find the widgets
   * @return $meta_boxes array of boxes usable for removeMetaBoxes()
   */
  protected static function buildMetaBoxArray($screen = 'dashboard') {
    $meta_box_ids = static::$remove_mbs;
    $meta_boxes = [];
    foreach ($meta_box_ids as $meta_box_id) {
      $meta_boxes[] = [
        'id' => $meta_box_id,
        'page' => $screen,
        'context' => static::evaluateMetaBoxContext($meta_box_id)
      ];
    }
    return $meta_boxes;
  }

  /**
   * Get context of common meta boxes
   *
   * @since  0.0.1
   * @access protected
   * @param  $id string slug of meta box
   * @return string context of the given box
   * @todo   furnish with complete list of built-in metaboxes for complete evaluation
   */
  protected static function evaluateMetaBoxContext($id) {
    switch ($id) {
      case 'dashboard_quick_press':
      case 'dashbaord_recent_drafts':
      case 'dashboard_primary':
      case 'dashboard_secondary':
      case 'tagsdiv-post_tag':
        return 'side';
        break;
      default:
        return 'normal';
    }
  }

  /**
   * Build an array of post types the installation is using, taking into account the square features that are used
   * and custom post types
   *
   * @since       0.0.1
   * @lastchanged 0.0.2
   * @param       bool  $as_array if we return objects or just names
   * @return      array $post_types all the content post types the installation is currently using
   */
  protected static function buildPostTypeArray($as_array = false) {

    $output = $as_array ? 'names' : 'objects';
    // Loop through all post types
    $post_types = get_post_types(
      ['public' => true, '_builtin' => true],
      $output
    );
    if (static::isThemeFeature('square-remove-posts')) {
      unset($post_types['post']);
    }
    if (!static::isThemeFeature('square-remove-comments')) {
      $post_types['comments'] = 'comments';
    }
    return $post_types;
  }
}
