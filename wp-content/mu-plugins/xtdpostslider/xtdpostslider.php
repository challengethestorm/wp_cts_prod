<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: SimpleSlider
	Plugin URI: 
	Description: Layouts simple slider
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [xtd_simple_slider]

class XtdPostSlider{
	
 
	static function init(){
			add_shortcode( 'xtd_post_slider', array(__CLASS__,'handle_shortcode') );
			//add_action('init', array(__CLASS__, 'register_assets'));
			//add_action('wp_head', array(__CLASS__, 'display_assets'));
	}
	

	static function handle_shortcode($atts){

		$atts = shortcode_atts(
			array(
				'id' => 'xtd_gal_'. time(),
				'tag'=> '',
				'prevnext' => '1',
				'bullets' => '0',
				'speed' => '0',
				'duration' => '0',
				'autoplay' => '0',
				'items' => '0',
				'posts' => '5',
				
			),
			$atts
		);

		$args = array(
		    'tag' => $atts['tag'],
		    'posts_per_page' => $atts['posts'],
		);
		
		if(!$atts['duration']){
			$atts['duration'] == 500;
		}

		$posts = get_posts($args);
		$result  = "";
		$_itemsCounter = 0;		

		$result .= '<div id="'.$atts['id'].'">';
			$result .= '<div class="'.$atts['id'].'_content owl-carousel owl-theme">';
				foreach ($posts as $_post) {
					$result .= '<div class="item">';
						$result .=  '<a class="post-title" href="' . $_post->guid. '"><h2>' .$_post->post_title. '</h2></a>';
						$result .=  '<div class="post-content">' .$_post->post_content. '</div>';
					$result .= '</div>';
					$_itemsCounter +=1;
				}

				if(count($posts) == 0){
					$result .= '<div class="item">';
						$result .=  '<div><p>Sorry, no posts matched your criteria.</p></div>';
					$result .= '</div>';
				}

				if($atts['bullets'] == "1"){
					$result .= '<div class="owl-controls clickable" data-fake-pagination="true" style="display:block"><div class="owl-pagination">';
						for ($i=0; $i < $_itemsCounter ; $i++) { 
							$result .= '<div class="owl-page active"><span class=""></span></div>';
						}
		            $result .=  '</div></div>';

				}
			$result .=  "</div>";



			if($atts['prevnext'] =="1"){
		 		$result .=  ''.
		            '<div class="owl-prev" data-slider="'.$atts['id'].'-prev"></div>' .
		            '<div class="owl-next" data-slider="'.$atts['id'].'-next"></div>' ;
	         }



			$result .=   '<script type="text/javascript">
			 jQuery(document).ready( function(){
			 		'.$atts['id'].' = jQuery("[id=\''.$atts['id'].'\']").last();
			 		'.$atts['id'].'_content = jQuery(".'.$atts['id'].'_content").last();
			 		'.$atts['id'].'_content.children("[data-fake-pagination=\"true\"]").remove();
			 		'.$atts['id'].'_content.owlCarousel({ 
			 			navigation : false, 
			 			slideSpeed : '.$atts['speed'].', 
			 			singleItem:true,
			 			pagination: '.$atts['bullets'].' ==1, 
			 			autoPlay: '.$atts['autoplay'].' ==1 ? '.$atts['duration'].' : false,
			 			navigationText: ["",""]
			 		});
					
					//controls
					jQuery("[data-slider=\''.$atts['id'].'-prev\']").last().unbind("click").click(function(e){ e.preventDefault(); e.stopPropagation();'.$atts['id'].'_content.trigger("owl.prev") });
					jQuery("[data-slider=\''.$atts['id'].'-next\']").last().unbind("click").click(function(e){ e.preventDefault(); e.stopPropagation();  '.$atts['id'].'_content.trigger("owl.next") });
			 })
			 </script>';

		$result .=  "</div>";

		 wp_reset_postdata();

		return $result;
		
	}

	static function custom_scripts(){
		
	}

	static function register_assets(){
		//wp_enqueue_style('owl_carousel_css', plugins_url('', __FILE__) .  '/../owl-carousel/owl.carousel.css',false);
		//wp_register_script('owl_carousel_min_js', plugins_url('', __FILE__) .  '/../owl-carousel/owl.carousel.js', array('jquery'));
		// wp_register_script('jquery-visible', plugins_url('', __FILE__) .  '/../owl-carousel/jquery.visible.js', array('jquery'));
		//wp_enqueue_style('owl_carousel_theme_css', plugins_url('', __FILE__) .  '/../owl-carousel/owl.theme.css',false);
		
	}

	static function display_assets() {
		//wp_print_scripts('owl_carousel_min_js');
		//wp_print_styles('owl_carousel_css');

	}
}

XtdPostSlider::init();