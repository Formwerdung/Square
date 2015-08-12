<?php

namespace Formwerdung\Square\Modules;

class RemoveComments extends \Formwerdung\Square\Lib\Admin {

  /**
   * Key of menu label that is to be removed
   * @url http://code.tutsplus.com/articles/customizing-your-wordpress-admin--wp-24941
   */
  public static $menu_label_key = 25;

  /**
   * Array of submenu labels for removal
   */
  public static $submenu_labels = [
    "options-general.php" => "options-discussion.php"
  ];

  /**
   * Admin bar node slug for removal
   */
  public static $node_id = 'comments';

  // This is in Roots/Soil
  // public static function filterHeaderComment( $headers ) {
  //  if (isset($header['X-Pingback']))
    //  	unset( $headers['X-Pingback'] );
    // 	return $headers;
    // }

  /**
   * Redirect all requests for comment-related pages to the dashboard
   *
   * @return void
   */
  public static function redirectAdminPages() {
    global $pagenow;

    switch ($pagenow) {
      case 'comment.php':
      case 'edit-comments.php':
      case 'options-discussion.php':
        wp_safe_redirect(get_admin_url());
        break;
    }
  }

  /**
   *
   */
  public static function filterQueryComment() {
    if (is_comment_feed()) {
        // we are inside a comment feed
        wp_die('There are no comments enabled on this site.', '', ['response' => 403]);
    }
  }

  /**
   * Register hook callbacks
   *
   * @return void
   */
  public static function registerHookCallbacks() {
    add_action('admin_menu', [ get_called_class(), 'hideMenuItems' ], 10);
    add_action('admin_menu', [ get_called_class(), 'hideSubmenuItems' ], 11);
    add_action('admin_menu', [ get_called_class(), 'removeMetaBoxes' ], 12);
    add_action('admin_bar_menu', [ get_called_class(), 'removeNode' ], 999);
    add_action('template_redirect', [ get_called_class(), 'filterQueryComment' ], 9);     // before redirect_canonical
    add_action('init', [ get_called_class(), 'redirectAdminPages']);
  }
}
