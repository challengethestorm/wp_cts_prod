<?php
/**
  * blank functions and definitions
  *
  * @package blank
  */

  'These functions are called indirectly from tags:
  
   wp_list_comments();
   comments_template();
   paginate_comments_links(); 
   next_comments_link();
   previous_comments_link();
   comment_form();
   the_tags();
  
   wp_link_pages();
   next_posts_link();
   previous_posts_link();
  
  
   language_attributes();
   body_class();
   post_class();
   ';


if (!file_exists(WPMU_PLUGIN_DIR . "/themebase.php") && file_exists(dirname(__FILE__).'/mu-plugins/themebase/themebase.php')){
  require_once dirname(__FILE__).'/mu-plugins/themebase/themebase.php';
}


if ( ! isset( $content_width ) ) {
  $content_width = 1176;
}

if ( ! function_exists( 'theme_setup' ) ) {

  function  theme_setup() {
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'post-thumbnails' );
    add_editor_style();
  }

}


function theme_enqueue_scripts() {
  if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
    wp_enqueue_script( 'comment-reply' );
  }
}

add_action( 'wp_enqueue_scripts', 'theme_enqueue_scripts' );
add_action( 'after_setup_theme', 'theme_setup' );

?>
