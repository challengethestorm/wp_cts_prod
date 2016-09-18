<?php
	
	function blank_shortcode_tag_site_title() {
		ob_start();
		bloginfo( 'name' );
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_blog_description() {
		ob_start();
		bloginfo( 'description' );
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_blog_version() {
		ob_start();
		bloginfo( 'version' );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_blog_admin_email() {
		ob_start();
		bloginfo( 'admin_email' );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_loginout() {
		ob_start();
		wp_loginout();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_logout_url() {
		ob_start();
		echo '<a href="' . wp_logout_url() .'">Log out</a>';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_login_url() {
		ob_start();
		echo '<a href="' . wp_login_url() .'">Log in</a>';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_lostpassword_url() {
		ob_start();
		echo '<a href="' . wp_lostpassword_url() .'">Lost your password?</a>';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_register() {
		ob_start();
		echo wp_register('', '');
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_blog_title() {
		ob_start();
		$title =  (is_home() || is_front_page()) ? bloginfo('title') : wp_title(''); // homepage && landing page has no title; use site name instead
		echo $title;
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_title() {
		ob_start();
		echo is_single() ? single_post_title() : wp_title('');
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_single_cat_title() {
		ob_start();
		single_cat_title();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	function blank_shortcode_tag_single_tag_title() {
		ob_start();
		single_tag_title();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	$blank_general_tags = array(
		'blog_description',
		'site_title',
		'title'
	);
	function blank_add_tag_shortcodes($tags) {
		global $blank_tags_shortcodes;
		for ($i=0; $i < count($tags); $i++) { 
			$blank_tags_shortcodes['general'][] = 'tag_'.$tags[$i];
			add_shortcode( 'tag_'.$tags[$i], 'blank_shortcode_tag_'.$tags[$i]);
		}
	}
	
	blank_add_tag_shortcodes($blank_general_tags);
?>