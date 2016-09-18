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
/**
 * Register Widget Area.
 *
 */
function cts_widgets_init() {
  register_sidebar( array(
    'name' => 'Header Sidebar',
    'id' => 'header_sidebar',
    'before_widget' => '<div id="cts-widget">',
    'after_widget' => '</div>',
    'before_title' => '<h2 class="rounded">',
    'after_title' => '</h2>',
  ) );
}

add_action( 'widgets_init', 'cts_widgets_init' );
// force favicon icon to follow scheme (http or https) based on the site scheme
add_filter( 'get_site_icon_url', 'set_url_scheme' );



/*Start FB Image replace for share thumbnail*/
add_filter('wpseo_pre_analysis_post_content', 'mysite_opengraph_content');

function mysite_opengraph_content($val) {
return preg_replace("/<img[^>]+>/i", "", $val);
}
/*End FB Image replace*/

// Add widget ID to widget page
add_action('in_widget_form', 'spice_get_widget_id');
function spice_get_widget_id($widget_instance)
{
    // Check if the widget is already saved or not. 
    if ($widget_instance->number=="__i__"){
     echo "<p><strong>Widget ID is</strong>: Pls save the widget first!</p>"   ;
  }  else {
       echo "<p><strong>Widget ID is: </strong>" .$widget_instance->id. "</p>";
    }
}
//end widget ID addition

?>
