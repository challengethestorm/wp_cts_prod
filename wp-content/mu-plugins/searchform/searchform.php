<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: Search form
	Plugin URI:   
	Description: Basic search control
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [xtd_search_form]

class XTDSearchForm{
	static $id ="";
	static $config ="";
 
	static function init(){
			add_shortcode( 'search', array(__CLASS__,'handle_shortcode') );
			add_action('init', array(__CLASS__, 'register_assets'));
			add_action('wp_head', array(__CLASS__, 'display_assets'));
	}
	

	static function handle_shortcode($atts){
		
		$atts = shortcode_atts(
			array(
				'title' => null,
				'id' => 'SearchForm' . time(),
	        	'placeholder' => 'Search here...',
				'label' => null,
				'usesubmit' => '0',
				'btntext'=>'Search',
			),
			$atts
		);
		$form = '<div id="' . $atts['id'] . '" class="widget widget_search">';

		$form .= '<form role="search" method="get" class="searchform" action="' . home_url( '/' ) . '" >';
		if(!is_null($atts['label'])) {
			$form .= '<label class="screen-reader-text" for="s">' . $atts['label'] . '</label>';
		}
		$form .= '<input type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="' . $atts['placeholder'] . '" />';

		if($atts['usesubmit'] == '1') {
			$form .= '<input type="submit" id="searchsubmit" value="' . esc_attr_x( $atts['btntext'], "submit button" ) . '" />';
		}
		$form .= '</form></div>';

		return $form;
	}

	static function custom_scripts(){
		
	}

	static function register_assets(){
		
		
	}

	static function display_assets() {
		

	}
}

XTDSearchForm::init();