<?php
	
	function blank_shortcode_tag_category_description() {
		ob_start();
		echo category_description();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_category_link() {
		ob_start();
		the_category();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_category_tags() {
		ob_start();
		the_tags();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	$blank_cat_tags = array(
	);
	for ($i=0; $i < count($blank_cat_tags); $i++) {
		$post_name = 'tag_'.$blank_cat_tags[$i];
		$blank_tags_shortcodes['post'][] = $post_name;
		$blank_tags_shortcodes['page'][] = $post_name;
		add_shortcode( $post_name, 'blank_shortcode_tag_'.$blank_cat_tags[$i]);
	}
?>