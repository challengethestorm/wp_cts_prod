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

class XtdCustomCarousel{
	static $id = "";
	static $config = "";
	static $styles = array();
	static $scripts = array();
 
	static function init(){
			add_shortcode( 'xtd_custom_carousel', array(__CLASS__,'handle_shortcode') );
			//add_action('init', array(__CLASS__, 'register_assets'));
			//add_action('wp_head', array(__CLASS__, 'display_assets'));
	}
	

	static function handle_shortcode($atts){
		self::$id = $atts['id'];

		self::register_assets();

		xtd_add_scripts(array('owl_carousel_min_js'));
		array_push(self::$styles, 'owl_carousel_theme_css');
		array_push(self::$styles, 'owl_carousel_css');
		array_push(self::$styles, 'owl_carousel_transition_css');
		xtd_add_styles(self::$styles);
		$styles = xtd_create_preview_styles(self::$styles);

		$atts = shortcode_atts(
			array(
				'id' => 'xtd_gal_'. time(),
				'type'=> '',
				'prevnext' => '1',
				'bullets' => '0',
				'speed' => '10000',
		        'category' => array(),
		        'tag' => '',
				'duration' => '10',
				'autoplay' => '1',
				'items' => '0',
				'posts' => '5',
				
			),
			$atts
		);

		if(!empty($atts['category'])) {
			$category = $atts['category'];
			$atts['category'] = array();
			$category = preg_replace("/\s*,\s*/", ",", $category);
			//categories need to be IDs
			$categories = explode(',', $category);
			for($i = 0; $i < count($categories); $i++) {
				$id = get_cat_ID($categories[$i]);
				if($id !== 0) {
					$atts['category'][] = $id;
				}
			}
		}

		if(!empty($atts['tag'])) {
			$tag = $atts['tag'];
			$atts['tag'] = array();
			$tag = preg_replace("/\s*,\s*/", ",", $tag);
			//tags need to be IDs
			$tags = explode(',', $tag);
			for($i = 0; $i < count($tags); $i++) {
				$term = get_term_by('name', $tags[$i], 'post_tag');
				if($term) {
					$atts['tag'][] = $term->term_id;
				}
			}
		}

		$post_args = array(
			'post_type' => $atts['type'],
			'post_status' => 'publish',
		);
		if(!empty($atts['category'])) {
			$post_args['cat'] = implode(',', $atts['category']);
		} 
		if(!empty($atts['tag'])) {
			$post_args['tag__in'] = $atts['tag'];
		}

		if(!$atts['duration']){
			$atts['duration'] == 500;
		}


		$post = new WP_Query( $post_args );
		$result = "";
		$result .= '<div id="'.$atts['id'].'">';
			$result .= '<div class="'.$atts['id'].'_content owl-carousel owl-theme">';
			if($post->have_posts()) {
				//foreach ($posts as $_post) {
				ob_start();
				while ( $post->have_posts() ) : $post->the_post();
					echo '<div class="item">';
					get_template_part( 'partial-templates/'. $atts['type'] .'-post-list');	
					echo '</div>';
				endwhile;
				$result .= ob_get_contents();
				ob_end_clean();
			} else {
				$result .= 'Sorry, no posts matched your criteria.';
			}	
			$result .=  "</div>";

	 		if($atts['prevnext'] =="1"){
		 		$result .=  ''.
		            '<div class="owl-prev" data-slider="'.$atts['id'].'-prev"></div>' .
		            '<div class="owl-next" data-slider="'.$atts['id'].'-next"></div>' ;
	         }

			$result .=  $styles .  '<script data-carousel="true">
			 jQuery(document).ready( function(){
			 		'.$atts['id'].' = jQuery("[id=\''.$atts['id'].'\']").last();
			 		'.$atts['id'].'_content = jQuery(".'.$atts['id'].'_content").last();
			 		'.$atts['id'].'_content.children("[data-fake-pagination=\"true\"]").remove();
			 		'.$atts['id'].'_content.owlCarousel({ 
			 			navigation : false, 
			 			slideSpeed  : '.$atts['speed'].', 
			 			pagination: '.$atts['bullets'].' ==1, 
			 			autoPlay: '.$atts['autoplay'].' ==1 ? '.$atts['duration'].' : false,
			 			navigationText: ["",""],
			 			items: '.$atts['posts'].'
			 		});

					jQuery("[data-slider=\''.$atts['id'].'-prev\']").click(function(e){ e.preventDefault(); e.stopPropagation(); '.$atts['id'].'_content.trigger("owl.prev") });
					jQuery("[data-slider=\''.$atts['id'].'-next\']").click(function(e){ e.preventDefault(); e.stopPropagation(); '.$atts['id'].'_content.trigger("owl.next") });
			 })
			 </script>';

		$result .=  "</div>";

		wp_reset_postdata();

		return $result;
		
	}

	static function custom_scripts(){
		
	}

	static function register_assets() {
		wp_register_style('owl_carousel_css', plugins_url('../owl-carousel/owl.carousel.css', __FILE__) . '', false);
		wp_register_style('owl_carousel_theme_css', plugins_url('/../owl-carousel/owl.theme.css', __FILE__) . '', false);
		wp_register_style('owl_carousel_transition_css', plugins_url('/../owl-carousel/owl.transitions.css', __FILE__), false);
		wp_register_script('owl_carousel_min_js', plugins_url('../owl-carousel/owl.carousel.js', __FILE__) . '', array('jquery'));

	}

	static function display_assets() {
		/*wp_print_scripts('owl_carousel_min_js');
		wp_print_styles('owl_carousel_css');*/

	}
}

XtdCustomCarousel::init();