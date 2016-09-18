<?php

function theme_remove_core_updates() {
	function remove_core_updates() {
		global $wp_version;
		return (object) array('last_checked' => time(), 'version_checked' => $wp_version, 'updates' => array());
	}

	add_filter('pre_site_transient_update_core', 'remove_core_updates');
	add_filter('pre_site_transient_update_plugins', 'remove_core_updates');
	add_filter('pre_site_transient_update_themes', 'remove_core_updates');
}

function theme_remove_filters() {
	remove_filter('the_content', 'wpautop');
	remove_filter('the_excerpt', 'wpautop');

	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'wp_shortlink_wp_head');
	remove_action('wp_head', 'start_post_rel_link');
	remove_action('wp_head', 'parent_post_rel_link');
	remove_action('wp_head', 'adjacent_posts_rel_link');
	remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
}

add_filter('site_url', 'xtd_site_url', 99999);

function xtd_site_url($url, $path = '', $scheme = null, $blog_id = null) {
	$nurl = "${_SERVER['HTTP_HOST']}";
	if (get_option('siteurl')) {
		$urlobj = parse_url(get_option('siteurl'));
		$hostname = $urlobj['host'];
		if (isset($urlobj['port']) && $urlobj['port']) {
			$hostname = $hostname . ":" . $urlobj['port'];
		}
		return str_replace($hostname, $nurl, $url);
	}
	return $url;
}

// TinyMCE SETTINGS
function format_TinyMCE($init) {
	$init['verify_html'] = false;
	$init['remove_linebreaks'] = false;
	$init['paste_remove_spans'] = false;
	$init['keep_styles'] = false;
	$init['wpautop'] = false;
	$init['extended_valid_elements'] = "*[*]";
	$init['valid_elements'] = "*[*]";
	$init['paste_strip_class_attributes'] = 'none';
	$init['paste_strip_class_attributes'] = 'none';
	$init['apply_source_formatting'] = false;
	$init['paste_strip_class_attributes'] = 'none';
	$init['paste_text_use_dialog'] = true;
	$init['forced_root_block'] = false;
	return $init;
}

add_filter('tiny_mce_before_init', 'format_TinyMCE');
add_filter('show_admin_bar', '__return_false');
add_filter('show_recent_comments_widget_style', '__return_false');

add_action('init', 'addParseSiteURLMCE');

function addParseSiteURLMCE() {
	if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
		add_filter('mce_external_plugins', 'addParseSiteURLMCEPlugin');
	}
}

function addParseSiteURLMCEPlugin($plugin_array) {
	$plugin_array['site_url'] = get_template_directory_uri() . '/js/siteurl.js';
	return $plugin_array;
}

// http://codex.wordpress.org/Function_Reference/wp_title
add_filter('wp_title', 'baw_hack_wp_title_for_home', 10, 2);
function baw_hack_wp_title_for_home($title, $sep) {
	$sep = (strlen($sep)>0) ? $sep : " | ";
	if (empty($title) && (is_home() || is_front_page())) {
		return __(get_bloginfo('sitename'), 'theme_domain') . $sep . get_bloginfo('description');
	} else {
		return __(get_bloginfo('sitename'), 'theme_domain') . $sep . $title;
	}
	return $title;
}

function theme_ensure_permalink() {
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure('/%postname%/');

	function blank_flush_rules() {
		global $wp_rewrite;
		//FIXME: this is an expensive operation, only run on initial setup of project
		flush_rewrite_rules(false);
	}

	add_action('admin_init', 'blank_flush_rules');
}

?>
