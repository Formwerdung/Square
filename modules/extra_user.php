<?php

namespace Formwerdung\Square\Modules;

class ExtraUser {

  /**
   * Special constrctor for the extra-user-module
   *
   * @since  0.0.1
   * @access public
   * @uses   add_role()
   */
  public function __construct() {
    $caps = [
      'moderate_comments' => true,
      'manage_categories' => true,
      'manage_links' => true,
      'edit_others_posts' => true,
      'edit_pages' => true,
      'edit_others_pages' => true,
      'edit_published_pages' => true,
      'publish_pages' => true,
      'delete_pages' => true,
      'delete_others_pages' => true,
      'delete_published_pages' => true,
      'delete_others_posts' => true,
      'delete_private_posts' => true,
      'edit_private_posts' => true,
      'read_private_posts' => true,
      'delete_private_pages' => true,
      'edit_private_pages' => true,
      'read_private_pages' => true,
      'unfiltered_html' => true,
      'edit_published_posts' => true,
      'upload_files' => true,
      'publish_posts' => true,
      'delete_published_posts' => true,
      'edit_posts' => true,
      'delete_posts' => true,
      'read' => true,
      'edit_theme_options' => true
    ];
    add_role('square-manager', 'Manager', $caps);
  }
}
