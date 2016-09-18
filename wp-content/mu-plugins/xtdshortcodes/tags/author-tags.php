<?php
	
	function blank_shortcode_tag_author_name($args) {
		$a = shortcode_atts( array(
	        'link' => '0'
	    ), $args );
		ob_start();
		
		if($a['link'] == '1'){
			the_author_posts_link();
		} else {
			the_author();
		}
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_author_link() {
		ob_start();
		the_author_link();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_author_no_of_posts() {
		ob_start();
		the_author_posts();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_author_posts_links($args) {
		$a = shortcode_atts( array(
	        'label' => "%name%'s articles"
	    ), $args );
		ob_start();
		global $authordata;
		$label = str_replace('%name%', get_the_author(), $a['label']);
		echo '<a href="'. get_author_posts_url( $authordata->ID, $authordata->user_nicename ).'" title="'.$label.'" rel="author">'.$label.'</a>';
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_author_get_author_posts_url() {
		ob_start();
		echo '<a href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'">' . the_author_meta( 'display_name' ) .'</a>';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_author_image($atts) {
		$atts = shortcode_atts(
			array(
				'size' => 64
			),
			$atts
		);
		ob_start();
		echo get_avatar(get_the_author_meta('ID'),$atts['size']);
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	$blank_author_tags = array(
		'author_name', 
		'author_no_of_posts',
		'author_posts_links',
		'author_image'
	);
	for ($i=0; $i < count($blank_author_tags); $i++) {
		$post_name = 'tag_'.$blank_author_tags[$i];
		$blank_tags_shortcodes['post'][] = $post_name;
		$blank_tags_shortcodes['page'][] = $post_name;
		add_shortcode( $post_name, 'blank_shortcode_tag_'.$blank_author_tags[$i]);
	}
?>