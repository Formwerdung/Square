<?php

namespace Formwerdung\Square\Lib;

abstract class Admin extends Module {
  public static $label_key;
  public static $submenu_labels = [];
  public static $node_id;
  protected static $remove_mbs = [];
  protected static $is_mb_cap = false;
  protected static $mb_cap = 'manage_options';

  public static function removeNavLabel() {
    if (!isset(static::$capability) || !current_user_can(static::$capability)) {
      global $menu;
      unset($menu[static::$label_key]);
    }
  }

  public static function hideSubmenuItems() {
    if (!isset(static::$capability) || !current_user_can(static::$capability)) {
      $submenu_labels = static::$submenu_labels;
      foreach ($submenu_labels as $menu_slug => $submenu_slug) {
        remove_submenu_page($menu_slug, $submenu_slug);
      }
    }
  }

  public static function removeNode($wp_admin_bar) {
    if (!isset(static::$capability) || !current_user_can(static::$capability)) {
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
   * Make a meta box array to comfortably loop through
   *
   * @mvc Controller
   */
  protected static function buildMetaBoxArray($screen = 'dashboard') {
    $meta_box_ids = static::$remove_mbs;
    $meta_boxes = array();

    foreach ($meta_box_ids as $meta_box_id) {
      $meta_boxes[] = array(
        'id' => $meta_box_id,
        'page' => $screen,
        'context' => static::evaluateMetaBoxContext($meta_box_id)
      );
    }

    return $meta_boxes;
  }

  protected static function getMetaBoxes( $screen = null, $context = 'advanced' ) {
    global $wp_meta_boxes;

    if ( empty( $screen ) )
        $screen = get_current_screen();
    elseif ( is_string( $screen ) )
        $screen = convert_to_screen( $screen );

    $page = $screen->id;

    return $wp_meta_boxes[$page][$context];
}

  /**
   * Get context of common meta boxes
   *
   * @mvc Controller
   */
  protected static function evaluateMetaBoxContext($id) {
    global $wp_meta_boxes;
    $mbs = self::getMetaBoxes('post','normal');
    d($wp_meta_boxes['post']['side']);
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

  public static function registerHookCallbacks() {
    add_action('admin_menu', [get_called_class(), 'removeNavLabel'], 10);
    add_action('admin_menu', [get_called_class(), 'hideSubmenuItems'], 11);
    add_action('admin_menu', [get_called_class(), 'removeMetaBoxes'], 12);
    add_action('admin_bar_menu', [get_called_class(), 'removeNode'], 999);
  }
}
