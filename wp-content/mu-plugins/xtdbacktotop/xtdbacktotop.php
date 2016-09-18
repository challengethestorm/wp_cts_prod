<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: Back to top
	Plugin URI:   
	Description: Basic back to top control
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [xtd_backtotop]

class XTDBackToTop{
	static $id ="";
	static $config ="";
 
	static function init(){
			add_shortcode( 'xtd_backtotop', array(__CLASS__,'handle_shortcode') );
	}
	

	static function handle_shortcode($atts){
		
		self::register_assets();
		xtd_add_scripts(array('BackToTop'));
		
		$atts = shortcode_atts(
			array(
				'duration' => '500',
				'easing' => 'linear',
				'id' => 'BackToTop' . time(),
			),
			$atts
		);
		$form = '<div data-duration="'.$atts['duration'].'" data-easing="'.$atts['easing'].'" id="' . $atts['id'] . '" class="widget widget_backtotop">';
		
		$form .= '</div>';

		return $form;
	}

	static function custom_scripts(){
		
	}

	static function register_assets(){
		wp_register_script('BackToTop', plugins_url('', __FILE__) .  '/assets/BackToTop.js', array('jquery'));
	}
}

XTDBackToTop::init();