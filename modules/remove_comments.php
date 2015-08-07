<?php

namespace Formwerdung\Square\Modules;

class RemoveComments extends \Formwerdung\Square\Lib\Admin {
  public static $label_key = 25;
  public static $node_id = 'comments';

  // This is in Roots/Soil
  // public static function filterHeaderComment( $headers ) {
  //  if (isset($header['X-Pingback']))
	//  	unset( $headers['X-Pingback'] );
	// 	return $headers;
	// }

  public static function redirectAdminPages() {
    global $pagenow;

    switch ($pagenow) {
      case 'comment.php':
      case 'edit-comments.php':
      case 'options-discussion.php':
        wp_safe_redirect(get_admin_url());
        exit;
        break;
    }
  }

  public static function filterQueryComment() {
		if (is_comment_feed()) {
			// we are inside a comment feed
			wp_die( 'There are no comments enabled on this site.', '', ['response' => 403]);
		}
	}

  public static function registerHookCallbacks() {
    global $submenu;
    var_dump($submenu);
    add_action('admin_menu', [get_called_class(), 'removeNavLabel'], 10);
    add_action('admin_menu', [get_called_class(), 'removeMetaBoxes'], 11);
    add_action('admin_bar_menu', [get_called_class(), 'removeNode'], 999);
    // add_filter('wp_headers', [get_called_class(), 'filterHeaderComment']);
    add_action('template_redirect', [get_called_class(), 'filterQueryComment'], 9);	// before redirect_canonical
    add_action('init', [get_called_class(), 'redirectAdminPages']);
  }
}
