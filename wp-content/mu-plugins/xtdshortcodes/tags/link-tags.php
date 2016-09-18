<?php
	
	
	function blank_shortcode_tag_home_url($atts) {
		$atts = shortcode_atts(
			array(
				'title' => 'Home',
			),
			$atts
		);
	
   		return '<a href="' . home_url() . '">'.$atts['title'].'</a>';
	}	
	$blank_link_tags = array(
		'home_url'
	);
// general in loc de post
	for ($i=0; $i < count($blank_link_tags); $i++) {
		$post_name = 'tag_'.$blank_link_tags[$i];
		$blank_tags_shortcodes['general'][] = $post_name;
		add_shortcode( $post_name, 'blank_shortcode_tag_'.$blank_link_tags[$i]);
	}
?>