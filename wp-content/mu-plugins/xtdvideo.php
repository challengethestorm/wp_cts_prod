<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: Layouts nav menu
	Plugin URI: 
	Description: Layouts nav menu
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [xtd_video]

class XtdVideo{
	static function init(){
			add_shortcode( 'xtd_video', array(__CLASS__,'handle_shortcode') );
	}
	

	static function handle_shortcode($atts){
		$atts = shortcode_atts(
			array(
				'id' => 'xtd_video' . time(),
				'src' => 'https://www.youtube.com/watch?v=Lrzf8Acgj4k',
			),
			$atts
		);

		 ob_start();
		 
		 $video = wp_oembed_get($atts['src']);

		 if($video){

			$pattern = "/height=\"[0-9]*\"/";
			$video = preg_replace($pattern, "", $video);
			$pattern = "/width=\"[0-9]*\"/";
			$video = preg_replace($pattern, "", $video);

			$video = str_replace("<iframe ", "<iframe width=\"100%\" height=\"100%\" ", $video);

		 	 echo '<div id="'.$atts['id'].'">' .  $video . "</div>";

		 } else {
		 	 echo  '<div id="'.$atts['id'].'"><iframe style="background:url(\''. plugins_url('', __FILE__) . '/xtdvideo/no_signal.gif\')"></iframe></div>';
		 }


		 $content = ob_get_contents();

		 ob_end_clean();

		 return $content;
	}
	
}

XtdVideo::init();