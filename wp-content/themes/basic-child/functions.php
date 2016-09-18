<?php
//* Code goes here
add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );

function enqueue_parent_styles() {
   wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
   wp_enqueue_style( 'child-style', get_stylesheet_directory_uri().'/style.css' );
   wp_enqueue_style( 'exp-cur-quotes-style', get_stylesheet_directory_uri().'/quotes-style.css' );
   wp_enqueue_style( 'exp-cur-theme-style', get_stylesheet_directory_uri().'/theme-styles.css' );
   wp_enqueue_style( 'exp-cur-menu-style', get_stylesheet_directory_uri().'/menu-style-3.8.css' );
   wp_enqueue_style( 'exp-cur-style', get_stylesheet_directory_uri().'/expresscurate.css' );
   wp_enqueue_style( 'exp-cur-dialog-style', get_stylesheet_directory_uri().'/dialog-style-3.9.css' );
   wp_enqueue_style( 'main-menu-1-style', get_stylesheet_directory_uri().'/menu_menu1.css' );
   wp_enqueue_style( 'drop-menu-1-style', get_stylesheet_directory_uri().'/drop_menu1.css' ); 
   wp_enqueue_style( 'gallery-shortcodes-style', get_stylesheet_directory_uri().'/galleries-shortcodes.css' );
   wp_enqueue_style( 'single-client-style', get_stylesheet_directory_uri().'/single-clients.css' );
   wp_enqueue_style( 'mc-embedded-style-2', get_stylesheet_directory_uri().'/mailchimp_embed.css' );
   wp_enqueue_style( 'footer-disc-style-1', get_stylesheet_directory_uri().'/css/footer-disclaimer.css' );
   wp_enqueue_style( 'lightbox-callout-style-1', get_stylesheet_directory_uri().'/css/lightbox-callout.css' );
   wp_enqueue_style( 'mc-embedded-style-3', get_stylesheet_directory_uri().'/css/mailchimp-embedded-form.css' );
//   wp_enqueue_style( 'contact-form-1', get_stylesheet_directory_uri().'/css/contact-form.css' ); //not yet enabled. this is intended to serve as contact form styling
   wp_enqueue_style( 'footer-inner-pg-style-1', get_stylesheet_directory_uri().'/css/footer-default-inner-page-template.css' );
   wp_enqueue_style( 'about-author-style-1', get_stylesheet_directory_uri().'/css/wp-about-author.css' );
   wp_enqueue_style( 'ns-slidebar-style-1', get_stylesheet_directory_uri().'/css/frontend.css' );
   wp_enqueue_style( 'search-form-style-1', get_stylesheet_directory_uri().'/searchForm.css' );
   wp_enqueue_style( 'search-style-1', get_stylesheet_directory_uri().'/font-awesome-4.6.3/css/font-awesome.min.css' );
}
//custom excerpt length used for blog posts
function wpfme_custom_excerpt_length( $length ) {
	//the amount of words to return
	return 55;
}
add_filter( 'excerpt_length', 'wpfme_custom_excerpt_length');

//triggers a confirmation message when the user presses "publish"
add_filter(
    'publish_confirm_message',
    function($msg) {
        return "You´re ready?\nSure!?";
    }
);

//enables the admin bar while viewing the site. Was having issues without this code.
add_filter('show_admin_bar', '__return_true');

/*Start MC top bar - show MC top bar on blog pages*/
/*
add_filter( 'mctb_show_bar', function( $show ) {
  return is_single();
} );
*/
/*MC topbar nsert group into request for new subscribers*/
add_action( 'mctb_before_submit_button', function() {
    echo '<input type="hidden" name="GROUP" value="Top Bar">';
});
add_filter( 'mctb_merge_vars', function( $vars ) {
    $vars['GROUP'] = ( isset( $_POST['GROUP'] ) ) ? sanitize_text_field( $_POST['GROUP'] ) : '';
    return $vars;
});
/*End MC Top Bar*/

include 'custom-type-functions.php';