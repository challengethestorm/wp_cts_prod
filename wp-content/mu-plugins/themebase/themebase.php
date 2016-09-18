<?php

/**
 *  @package CloudPress
 * @version 1.0
 *
 */


/*
  Plugin Name: Theme Base
  Plugin URI: 
  Description: Theme Base
  Author: Extend Studio
  Version: 1
  Author URI: cloud-press.net
*/

  require_once dirname(__FILE__).'/core/assets-include.php';
  require_once dirname(__FILE__).'/core/decorate.php';
  require_once dirname(__FILE__).'/core/overwrite-default.php';
  require_once dirname(__FILE__).'/plugins/plugins.php';
  require_once dirname(__FILE__).'/import/import.php';

  function xtd_wrap_blog_content($c) {
      $ret = $c;
      if(!is_page(get_the_ID())) {
        $postcontent = get_the_content();
        $empty = empty($postcontent) || trim($postcontent) === '';

        if(!$empty) {
            $type = get_post_type(get_the_ID());
            $ret = '<div class="'.$type.'-content-inner">' . $ret . '</div>';
        }
      }
      return $ret; 
  }

  
add_filter('image_send_to_editor','url_to_shortcode',5,8);

function url_to_shortcode($html, $id, $caption, $title, $align, $url, $size, $alt)
{
  $html = preg_replace('/' . preg_quote(do_shortcode('[tag_link_site_url]'), '/') . '/', '[tag_link_site_url]', $html);
  return $html;
}


  add_filter('the_content', 'xtd_wrap_blog_content', 999999);

  function theme_register_menus(){
   
  }


  function theme_init() {
    /*
     * Make theme available for translation.
     * Translations can be filed in the /languages/ directory.
     * If you're building a theme based on blank, use a find and replace
     * to change 'blank' to the name of your theme in all the template files
     */
    load_theme_textdomain( 'blank', get_template_directory() . '/languages' );
    
    if ( defined( "WP_INSTALLING" ) )
    return;

    CPPluginBase::check_common_files();
    theme_ensure_permalink();
    if (function_exists("xtd_add_files_decorations")) {
    theme_remove_core_updates();
    }
    theme_remove_filters();
    theme_register_menus();
  }

  add_action( 'after_setup_theme', 'theme_init' );

?>