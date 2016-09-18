<?php

function wrapWidgetCode($content, $id) {
	return '<div id="' . $id . '">' . $content . '</div>';
}

function shortcode_calendar($args) {
	$a = shortcode_atts(array(
		'title' => 'Calendar',
		'id' => 'wp_widget',
	), $args);
	ob_start();
	//get_calendar();
	the_widget('WP_Widget_Calendar', $a);
	$content = ob_get_contents();
	ob_end_clean();
	return wrapWidgetCode($content, $a['id']);
}

function shortcode_recent_comments($args) {
	$a = shortcode_atts(array(
		'title' => 'Recent Comments',
		'number' => 5,
		'id' => 'wp_widget',
		'ordered' => 'false',
	), $args);
	ob_start();
	//$comments = get_comments($args);
	the_widget('WP_Widget_Recent_Comments', $a);
	$content = ob_get_contents();
	if ($a['ordered'] === 'true') {
		$content = str_replace('<ul', '<ol', $content);
		$content = str_replace('</ul', '</ol', $content);
	}
	ob_end_clean();
	return wrapWidgetCode($content, $a['id']);
}

function shortcode_list_pages($args) {
	$a = shortcode_atts(array(
		'title' => 'Pages',
		'sortby' => 'menu_order',
		'exclude' => null,
		'id' => 'wp_widget',
	), $args);
	ob_start();
	the_widget('WP_Widget_Pages', $a);
	$content = ob_get_contents();
	ob_end_clean();
	return wrapWidgetCode($content, $a['id']);
}

function shortcode_xtd_tag_cloud($args) {
	$a = shortcode_atts(array(
		'title' => 'Tags',
		'taxonomy' => '',
		'smallest' => 1,
		'largest' => 2,
		'unit' => 'em',
		'id' => 'wp_widget',
	), $args);
	ob_start();
	wp_tag_cloud($a);
	// the_widget('WP_Widget_Tag_Cloud', $a);

	global $wp_query;
	if ($wp_query->query_vars["taxonomy"] && !$a['taxonomy']) {
		$a['taxonomy'] = $wp_query->query_vars["taxonomy"];
	}

	if (!$a['taxonomy']) {
		$a['taxonomy'] = 'post_tag';
	}

	$content = ob_get_contents();
	$content .= (!$content) ? "<p>No tags defined</p>" : "";
	ob_end_clean();
	// wrapper added manually
	return wrapWidgetCode('<div class="widget widget_tag_cloud"><h2 class="widgettitle">' . $a['title'] . '</h2><div class="tagcloud">' . $content . '</div></div>', $a['id']);
}

function shortcode_recent_posts($args) {
	$a = shortcode_atts(array(
		'title' => 'Recent Posts',
		'number' => 10,
		'id' => 'wp_widget',
		'ordered' => 'false',
	), $args);
	ob_start();
	the_widget('WP_Widget_Recent_Posts', $a);
	$content = ob_get_contents();
	if ($a['ordered'] === 'true') {
		$content = str_replace('<ul', '<ol', $content);
		$content = str_replace('</ul', '</ol', $content);
	}
	ob_end_clean();
	return wrapWidgetCode($content, $a['id']);
}
/* //http://alignsoft.com/links-manager-deprecated-as-of-wordpress-3-5/
function shortcode_links($args) {
$a = shortcode_atts( array(
'title' => __('Links'),
'category' => 'false',
'description' => 'false',
'rating' => 'false',
'images' => 'true',
'name' => 'false'
), $args );
ob_start();
the_widget('WP_Widget_Links', $a);
$content = ob_get_contents();
ob_end_clean();
return wrapWidgetCode($content);
}*/
//add_shortcode( 'links', 'shortcode_links' );

function shortcode_categories($args) {
	$a = shortcode_atts(array(
		'title' => 'Categories',
		'count' => '0',
		'hierarchical' => '0',
		'dropdown' => '0',
		'id' => 'wp_widget',
		'taxonomy' => '',
		'hide_empty' => '0',
		'value_field' => 'slug',
	), $args);

	$a['title'] = trim($a['title']);

	global $wp_query;
	if ($wp_query->query_vars["taxonomy"] && !$a['taxonomy']) {
		$a['taxonomy'] = $wp_query->query_vars["taxonomy"];
	}

	if (!$a['taxonomy']) {
		$a['taxonomy'] = 'category';
	}

	add_filter('widget_categories_args', function ($cat_args) use ($a) {
		$cat_args['taxonomy'] = strtolower($a['taxonomy']);
		$cat_args['hide_empty'] = strtolower($a['hide_empty']);
		return $cat_args;
	}, 10, 1);

	add_filter('widget_categories_dropdown_args', function ($cat_args) use ($a) {
		$cat_args['taxonomy'] = strtolower($a['taxonomy']);
		$cat_args['hide_empty'] = strtolower($a['hide_empty']);
		$cat_args['value_field'] = strtolower($a['value_field']);
		return $cat_args;
	}, 10, 1);

	ob_start();
	the_widget('WP_Widget_Categories', $a);
	$content = ob_get_contents();
	ob_end_clean();

	// remove extra classes
	if (xtd_in_editor()) {
		$matches = array();
		preg_match('/cat-item-([0-9]+)\s?/', $content, $matches);
		if ($matches) {
			$toMatch = $matches[0];
			$content = str_replace($toMatch, 'current-cat', $content);
		}
	}
	$content = preg_replace("/cat-item-([0-9]+)\s?/", "", $content);

	// remove h2 if title is empty
	if (!$a['title']) {
		$content = preg_replace('/<h2 class="widgettitle">(.*)<\/h2>/', "<!-- no title -->", $content);
	}

	$content = preg_replace('/<label(.*)class="screen-reader-text"(.*)>(.*)<\/label>/', "<!-- no label -->", $content);

	if ($a['dropdown'] !== '0') {

		$newJS = "" .
		"	function onCatChange(){\n" .
		"		if ( dropdown.options[ dropdown.selectedIndex ].value != -1 ) {\n" .
		"			location.href = '" . home_url() . "/" . sanitize_title($a['taxonomy']) . "/' + dropdown.options[ dropdown.selectedIndex ].value;\n" .
		"		}\n" .
		"	}\n";
		$content = preg_replace("/function\s?onCatChange\(\)\s?\{[.*\s\S\}]+?\}[\S\s]+?\}/", $newJS, $content);
	}

	return wrapWidgetCode($content, $a['id']);
}

function shortcode_archives($args) {
	$a = shortcode_atts(array(
		'title' => 'Archives',
		'count' => '0',
		'dropdown' => '0',
		'id' => 'wp_widget',
	), $args);
	ob_start();
	the_widget('WP_Widget_Archives', $a);
	$content = ob_get_contents();
	ob_end_clean();
	return wrapWidgetCode($content, $a['id']);
}

function shortcode_rss($args) {
	$a = shortcode_atts(array(
		'title' => '',
		'url' => '',
		'items' => '5',
		'show_summary' => 'false',
		'show_author' => 'true',
		'show_date' => 'true',
		'id' => 'wp_widget',
	), $args);
	ob_start();

	$a['show_summary'] = ($a['show_summary'] == 'true');
	$a['show_author'] = ($a['show_author'] == 'true');
	$a['show_date'] = ($a['show_date'] == 'true');

	the_widget('WP_Widget_RSS', $a, $a);
	$content = ob_get_contents();
	ob_end_clean();
	return wrapWidgetCode($content, $a['id']);
}

function shortcode_post_comments_section($args) {
	$a = shortcode_atts(array(
		'style' => '',
	), $args);

	global $withcomments;
	ob_start();
	echo '<div class="post-comments">';
	$withcomments = true;
	$comments = get_comments(array(
		'post_id' => get_the_ID()));
	comments_template();
	echo '</div>';
	$content = ob_get_contents();
	ob_end_clean();

	$style_file = array('comments');

	if ($a['style']) {
		$style_file[] = $a['style'];
	}
	$style_file = implode('-', $style_file);
	wp_enqueue_style($style_file, get_template_directory_uri() . "/" . $style_file . ".css");
	return $content;

}

// function shortcode_search($args){
// 	$args =  shortcode_atts( array(
//         'title' => ''
//         ), $args);
// 	ob_start();
// 	the_widget( 'WP_Widget_Search', $args);
// 	$content = ob_get_contents();
// 	ob_end_clean();
// 	return  $content;
// }

add_shortcode('calendar', 'shortcode_calendar');
add_shortcode('recent_comments', 'shortcode_recent_comments');
add_shortcode('list_pages', 'shortcode_list_pages');
add_shortcode('xtd_tag_cloud', 'shortcode_xtd_tag_cloud');
add_shortcode('recent_posts', 'shortcode_recent_posts');
add_shortcode('categories', 'shortcode_categories');
add_shortcode('archives', 'shortcode_archives');
add_shortcode('rss', 'shortcode_rss');
add_shortcode('post_comments_section', 'shortcode_post_comments_section');
// add_shortcode( 'search', 'shortcode_search' );

?>