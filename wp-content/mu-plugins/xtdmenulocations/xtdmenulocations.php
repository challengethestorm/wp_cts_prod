<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: SimpleSlider
	Plugin URI: 
	Description: Layouts Gallery
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [xtd_simple_slider]

class XtdMenuLocations{
 
	static function init(){
			//add_shortcode( 'xtd_gallery', array(__CLASS__,'handle_shortcode') );
		add_action('init', array(__CLASS__,'register_types'));
	}
	
	static function register_types() {
		$path = get_plugin_data_folder() . '/menu_locations.php';
		if(file_exists($path)) {
			$obj = json_decode(require ($path), true);
			register_nav_menus($obj);
		}
	}

	static function handle_shortcode($atts){
		
	}
}

XtdMenuLocations::init();