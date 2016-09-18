<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: Extend Shortcodes
	Plugin URI: 
	Description: Layouts Shortcodes
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

	require_once dirname(__FILE__).'/widgets/wp-widgets.php';
	require_once dirname(__FILE__).'/widgets/widgets.php';
	require_once dirname(__FILE__).'/tags/tags.php';
	require_once dirname(__FILE__).'/tags/cp_post_meta_tags.php';

	function blank_shortcode_tag_link_site_url() {
		return get_site_url();
	}

	function blank_shortcode_tag_wp_title() {
		return wp_title('');
	}
	add_shortcode( 'tag_link_site_url', 'blank_shortcode_tag_link_site_url');
	add_shortcode( 'tag_wp_title', 'blank_shortcode_tag_wp_title');
?>