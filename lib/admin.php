<?php

namespace Formwerdung\Square\Lib;

abstract class Admin extends Module {
  public static $menu_label_key;
  public static $menu_label_cap;
  public static $submenu_labels = [];
  public static $submenu_label_cap;
  public static $node_id;
  public static $remove_node_cap;
  protected static $remove_mbs = [];
  protected static $is_mb_cap = false;
  protected static $mb_cap = 'manage_options';

  public static function hideMenuItems() {
    if (!isset(static::$menu_label_cap) || !current_user_can(static::$menu_label_cap)) {
      global $menu;
      unset($menu[static::$menu_label_key]);
    }
  }

  public static function hideSubmenuItems() {
    if (!isset(static::$submenu_label_cap) || !current_user_can(static::$submenu_label_cap)) {
      $submenu_labels = static::$submenu_labels;
      foreach ($submenu_labels as $menu_slug => $submenu_slug) {
        remove_submenu_page($menu_slug, $submenu_slug);
      }
    }
  }

  public static function removeNode($wp_admin_bar) {
    if (!isset(static::$remove_node_cap) || !current_user_can(static::$remove_node_cap)) {
      $wp_admin_bar->remove_node(static::$node_id);
    }
  }

  /**
   * Loop through meta-boxes to remove them
   *
   * @mvc Controller
   */
  public static function removeMetaBoxes() {
    $meta_boxes = static::buildMetaBoxArray();
    if (static::$is_mb_cap) {
      if (!current_user_can(static::$mb_cap)) {
        foreach ($meta_boxes as $meta_box) {
          remove_meta_box($meta_box['id'], $meta_box['page'], $meta_box['context']);
        }
      }
    } else {
      foreach ($meta_boxes as $meta_box) {
        remove_meta_box($meta_box['id'], $meta_box['page'], $meta_box['context']);
      }
    }
  }

  /**
   * Redirect certain links in the WordPress admin to the Dashboard
   *
   * @access public
   * @param  none   is used in add_action
   * @uses   global $pagenow
   * @return void
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
   * @mvc Controller
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
   * @mvc Controller
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
   * Build an array of post types to be shown with the overview widget
   *
   * @return array $post_types
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

  /**
   * Register hook callbacks
   */
  public static function registerHookCallbacks() {
    add_action('admin_menu', [get_called_class(), 'hideMenuItems'], 10);
    add_action('admin_menu', [get_called_class(), 'hideSubmenuItems'], 11);
    add_action('admin_menu', [get_called_class(), 'removeMetaBoxes'], 12);
    add_action('admin_bar_menu', [get_called_class(), 'removeNode'], 999);
  }
}
