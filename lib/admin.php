<?php

namespace Formwerdung\Square\Lib;

abstract class Admin extends Module {
  public static $label_key;
  public static $node_id;
  protected static $meta_boxes_for_removal = array();
  protected static $remove_meta_boxes_screen;
  protected static $use_capability_meta_boxes = false;
  protected static $capability_meta_box = 'manage_options';

  public static function removeNavLabel() {
    if (!isset(static::$capability) || !current_user_can(static::$capability)) {
      global $menu;
      unset($menu[static::$label_key]);
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
    if (static::$use_capability_meta_boxes) {
      if (!current_user_can(static::$capability_meta_box)) {
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
    $meta_box_ids = static::$meta_boxes_for_removal;
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

  public static function registerHookCallbacks() {
    add_action('admin_menu', array( get_called_class(), 'removeNavLabel'), 10);
    add_action('admin_menu', array( get_called_class(), 'removeMetaBoxes'), 11);
    add_action('admin_bar_menu', array( get_called_class(), 'removeNode'), 999);
  }
}
