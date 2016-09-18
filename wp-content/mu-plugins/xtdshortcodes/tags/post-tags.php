<?php
	function blank_shortcode_tag_post_title($atts) {
		$args = shortcode_atts( array(
			'link' => '0'
	    ), $atts );
		ob_start();
		if($args['link'] === '1') {
			echo '<a class="post-title" href="' . get_the_permalink() .'">';
		}
		the_title();
		if($args['link'] === '1') {
			echo '</a>';
		}
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_content($atts) {
		$args = shortcode_atts( array(
			'more_text' => '(more...)'
	    ), $atts );
		ob_start();
		the_content($args['more_text']);
		$content = ob_get_contents();
   		ob_end_clean();
   		
   		return $content;
	}
	function blank_shortcode_tag_post_time($atts) {
		$args = shortcode_atts( array(
			'format' => 'g:i a',
			'hour' => '12'
	    ), $atts );
		if($args['hour'] == '24') {
			$args['format'] = str_replace('g', 'H', $args['format']);
			$args['format'] = str_replace('a', '', $args['format']);
		}
		ob_start();
		the_time($args['format']);
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_id() {
		ob_start();
		the_ID();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_excerpt() {
		global $post;
		$content = apply_filters( 'get_the_excerpt', $post->post_excerpt );
   		return $content;
	}
	function blank_shortcode_tag_post_link_pages() {
		ob_start();
		wp_link_pages();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_attachment_link() {
		ob_start();
		the_attachment_link();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_previous_link($atts) {
		$args = shortcode_atts( array(
			'title' => '< %title',
			'format' => '%link'
	    ), $atts );
		ob_start();
		echo "<span>";
		previous_post_link($args['format'], $args['title']);
		echo "</span>";
		$content = ob_get_contents();
		$content = str_replace("<a", '<a class="post-prev-link"', $content);
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_next_link($atts) {
		$args = shortcode_atts( array(
			'title' => '%title >',
			'format' => '%link'
	    ), $atts );
		ob_start();
		echo "<span>";
		next_post_link($args['format'], $args['title']);
		echo "</span>";
		$content = ob_get_contents();
		$content = str_replace("<a", '<a class="post-next-link"', $content);
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_previous_image_link() {
		ob_start();
		previous_image_link();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_next_image_link() {
		ob_start();
		next_image_link();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_next_links() {
		ob_start();
		next_posts_link();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_tags($atts) {
		$args = shortcode_atts( array(
			'before' => 'Tags: ',
			'sep' => ', ',
			'after' => '',
			'notags' => 'No tags.'
	    ), $atts );
	    if(empty($args['notags'])) {
	    	$args['notags'] = ' ';
	    }
	    $posttype = get_post_type();
		ob_start();
		global $post;
		if($posttype !== 'post' && $posttype !== 'page') {
			the_terms($post->ID, $posttype . '_tag', $args['before'], $args['sep'], $args['after']);
		} else {
			the_tags($args['before'], $args['sep'], $args['after']);
		}
		$content = ob_get_contents();
   		ob_end_clean();
   		if(!empty($content)) {
   			$content = '<span class="post-tags">' . $content . '</span>';
   		} else {
   			$content = '<span class="post-tags">'. $args['notags'] .'</span>';
   		}
   		$content = str_replace("<a", '<a class="post-tag"', $content);
   		return $content;
	}
	function blank_shortcode_tag_post_comments_popup_link() {
		ob_start();
		comments_popup_link();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_page_title($atts) {
		$args = shortcode_atts( array(
			'link' => '0'
	    ), $atts );
		ob_start();
		if($args['link'] === '1') {
			echo '<a href="' . get_the_permalink() .'">';
		}
		wp_title('');
		if($args['link'] === '1') {
			echo '</a>';
		}
		$content = ob_get_contents();
		$content = str_replace("<a", '<a class="post-title"', $content);
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_categories($atts) {
		$args = shortcode_atts( array(
			'sep' => ' ',
			'parents' => ' '
	    ), $atts );
		$posttype = get_post_type();
		ob_start();
		global $post;
		if($posttype !== 'post' && $posttype !== 'page') {
			echo '<span>';
			the_terms($post->ID, $posttype . '_category', '', $args['sep']);
			echo '</span>';
		} else {
			echo '<span>';
			the_category($args['sep'], $args['parents']);
			echo '</span>';
		}
		$content = ob_get_contents();
		$content = str_replace("<a", '<a class="post-category"', $content);
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_attachment_image() {
		ob_start();
		$args = array( 'post_type' => 'attachment', 'posts_per_page' => -1, 'post_status' =>'any', 'post_parent' => the_ID() ); 
		$attachments = get_posts( $args );
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				echo apply_filters( 'the_title' , $attachment->post_title );
				the_attachment_link( $attachment->ID , false );
			}
		}
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_date($atts) {
		$args = shortcode_atts( array(
			'format' => 'F j, Y'
	    ), $atts );
		ob_start();
		//the_date(); // Does not work for multiple occurences, fails silently
		the_time( $args['format'] );
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_modified_author() {
		ob_start();
		the_modified_author();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_modified_date() {
		ob_start();
		the_modified_date();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_modified_time() {
		ob_start();
		the_modified_time();
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_shortlink($atts) {
		$args = shortcode_atts( array(
			'format' => 'This is the short link.'
	    ), $atts );
		ob_start();
		the_shortlink($args['format']);
		$content = ob_get_contents();
		$content = str_replace("<a", '<a class="post-shortlink"', $content);
   		ob_end_clean();
   		return $content;
	}
	function blank_shortcode_tag_post_thumbnail() {
		$args = array(
			'size' => 'full'
		);
		ob_start();
		echo '<a href="' . get_the_permalink() .'">';
		if (has_post_thumbnail()) {
			the_post_thumbnail($args['size']);
		} else {
			if(xtd_in_editor()) {
				echo '<img class="wp-post-image" src="'.  get_site_url() .'/wp-content/uploads/2014/09/placeholder_image.png"></img>';
			}
		}
		echo '</a>';
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}
	//post only shortcodes
	function blank_shortcode_tag_next_page_link($atts) {
		global $wp_query;
		$args = shortcode_atts( array(
			'link_next' => 'Next Page >'
	    ), $atts );
	    ob_start();
	    echo get_next_posts_link( $args['link_next'], $wp_query->max_num_pages );
	    $content = ob_get_contents();
	    $content = str_replace('<a', '<a class="next-page-link"', $content);
	    ob_end_clean();
	    return $content;
	}
	function blank_shortcode_tag_prev_page_link($atts) {
		global $wp_query;
		$args = shortcode_atts( array(
			'link_prev' => '< Previous Page'
	    ), $atts );
	    ob_start();
	    if (xtd_in_editor()) {
		    global $paged;
		    $oldpaged = $paged;
		    $paged = 2;	    	
	    }
	    echo get_previous_posts_link( $args['link_prev'], $wp_query->max_num_pages );
	    $content = ob_get_contents();
	    $content = str_replace('<a', '<a class="next-page-link"', $content);
	    ob_end_clean();
	    if (xtd_in_editor()) {
	    	$paged = $oldpaged;	    
	    }
	    
	    return $content;
	}
	function blank_shortcode_tag_paginate_link($atts) {
		$args = shortcode_atts( array(
			'link_prev' => '< Previous Page',
			'link_next' => 'Next Page >'
	    ), $atts );
	    global $wp_query;
	    $big = 99999999;
	    $args['base'] = str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
	    $args['format'] = '?paged=%#%';
	    $args['current'] = max( 1, get_query_var('paged') );
	    $args['total'] = $wp_query->max_num_pages;
	    $args['prev_text'] = $args['link_prev'];
	    $args['next_text'] = $args['link_next'];
	    if(empty($args['prev_text']) && empty($args['next_text'])) {
	    	$args['prev_next'] = false;
	    }
	    ob_start();
	    echo '<span>';
	    echo paginate_links($args);
	    echo '</span>';
	    $content = ob_get_contents();
	    ob_end_clean();
	    if($content !== '<span></span>') {
	    	return $content;	
	    }
	    return;
	}
	$blank_post_only_tags = array(
		'next_page_link',
		'prev_page_link',
		'paginate_link'
	);
	function blank_shortcode_tag_post_permalink($atts) {
		$atts = shortcode_atts(
			array(
				'title' => 'Link',
			),
			$atts
		);
		ob_start();
		the_permalink(); 
		$content = ob_get_contents();
   		ob_end_clean();
   		return '<a class="post-permalink" href="' . $content . '">'.$atts['title'].'</a>';
	}
	$blank_post_and_page_tags = array(
		'post_content', 
		'post_title', 
		'post_time', 
		'post_categories',
		'post_tags',
		'post_excerpt', 
		'post_previous_link', 
		'post_next_link', 
		'post_date',
		'post_shortlink',
		'post_thumbnail',
		'post_permalink'
	);
	global $blank_tags_shortcodes;
	// these tags can be used both with tag_post and tag_page //
	for ($i=0; $i < count($blank_post_and_page_tags); $i++) {
		$post_name = 'tag_'.$blank_post_and_page_tags[$i];
		$page_name = 'tag_'.str_replace('post_', 'page_', $blank_post_and_page_tags[$i]);
		$blank_tags_shortcodes['post'][] = $post_name;
		if($page_name !== 'tag_page_content' && $page_name !== 'tag_page_categories' && $page_name !== 'tag_page_tags' &&
		   $page_name !== 'tag_page_previous_link' && $page_name !== 'tag_page_next_link' && $page_name !== 'tag_page_excerpt') {
			$blank_tags_shortcodes['page'][] = $page_name;
		}
		add_shortcode( $post_name, 'blank_shortcode_tag_'.$blank_post_and_page_tags[$i]);
		add_shortcode( $page_name, 'blank_shortcode_tag_'.$blank_post_and_page_tags[$i]);
	}
	for ($i=0; $i < count($blank_post_only_tags); $i++) {
		//$blank_tags_shortcodes['general'][] = $blank_post_only_tags[$i];
		add_shortcode( 'tag_' . $blank_post_only_tags[$i], 'blank_shortcode_tag_'.$blank_post_only_tags[$i]);
	}
