<?php
	
	$blank_general_widgets = array(
		'custom_post_list',
		'tag_custom_post_key',
		'wp_head',
		'wp_footer',
		'header',
		'sidebar',
		'footer',
		'post', 
		'post_list', 
		'search_results',
		'page_content',
		'blog_content',
		'custom_post_content',
		'featured_posts', 
		'post_comments'
	);

	function blank_shortcode_tag_custom_post_key( $atts ){
		$atts = shortcode_atts( array(
			'key' => 'key'
	    ), $atts );
		ob_start();
		$arr = get_post_custom();
		if(!empty($arr) && array_key_exists($atts['key'], $arr)) {
			 echo $arr[$atts['key']][0];
		}
		$content = ob_get_contents();
		ob_end_clean();
		if(!empty($content)) {
			$class = 'custom-field-' . str_replace(' ', '-', $atts['key']);
			$content = '<span class="'. $class .'">'.$content.'</span>';
		}
		return $content;
	}

	function blank_shortcode_custom_post_list( $atts ){
		ob_start();

		$atts = shortcode_atts( array(
			'id' => 'cp_' . time(),
	        'type' => 'xtd_testimonial',
	        'posts' => '5',
	        'category' => array(),
	        'tag' => '',
	        'page' => '1',
	        'template'=>false
	    ), $atts );

		if(!empty($atts['category'])) {
			$category = $atts['category'];
			$atts['category'] = array();
			$category = preg_replace("/\s*,\s*/", ",", $category);
			$categories = explode(',', $category);
			$atts['category'] = $categories;
		}

		if(!empty($atts['tag'])) {
			$tag = $atts['tag'];
			$atts['tag'] = array();
			$tag = preg_replace("/\s*,\s*/", ",", $tag);
			$tags = explode(',', $tag);
			$atts['tag'] = $tags;
		}

		$post_args = array(
			'post_type' => $atts['type'],
			'post_status' => 'publish',
			'posts_per_page' => $atts['posts'],
			'paged' => $atts['page']
		);
		
		if(!empty($atts['category'])) {
			 $post_args['tax_query'] = array(
					array(
						'taxonomy' => $atts['type'] . '_category',
						'field'    => 'name',
						'terms'    => $atts['category'] ,
					),
				);
		} 
		

		if(!empty($atts['tag'])) {

			if(!isset( $post_args['tax_query'])){
				 $post_args['tax_query'] = array();
			}

			 $post_args['tax_query'][] = 
					array(
						'taxonomy' => $atts['type'] . '_tag',
						'field'    => 'name',
						'terms'    => $atts['tag'] ,
					);

		}
		
		$post = new WP_Query( $post_args );
		$index = 1;
		echo "<div id='" . $atts['id'] . "-row'>";
			if ($post->have_posts()) {
				while ( $post->have_posts() ) : $post->the_post();
 					
 					$clear_str = cp_get_clear_classes($index, $atts);
					
					$extra = ' post-id="'.get_the_ID().'"';

					echo '<div class="'. $atts['id'] .'-column'.$clear_str.'" >';

						echo '<div class="'. $atts['id'] .'-post-content" '.$extra.' >';
						$_template = isset($atts['template'])?$atts['template']:$atts['type'] .'-post-list';

					
						
						get_template_part( CloudPressApps::$apps_rel . "/". $_template );
						
						echo '</div>';
					echo '</div>';
					$index++;
				endwhile;
			} else {
				//echo "<li>";
				echo 'Sorry, no posts matched your criteria.'; 
				//echo "</li>";
			}
		echo "</div>";
		$page = ob_get_contents();
   		ob_end_clean();
		wp_reset_postdata();
		return $page;
	}

	function blank_shortcode_wp_footer( $atts ){
		ob_start();
		wp_footer();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_wp_head( $atts ){
		ob_start();
		wp_head();
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_header( $atts ){
		$a = shortcode_atts( array(
	        'name' => '',
	        'class' => 'header',
	        'tag' => 'div'
	    ), $atts );

		ob_start();

		echo '<'.$a['tag'].' class="'.$a['class'].'">';
		get_header($a['name']);
		echo '</'.$a['tag'].'>';
		
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_sidebar( $atts ){
		$a = shortcode_atts( array(
	        'name' => '',
	        'class' => 'sidebar',
	        'tag' => 'div'
	    ), $atts );


		if($a['name']){
			$a['class'] =str_replace(" ", "-", strtolower($a['name'])) . "-sidebar";
		}

		ob_start();

		echo '<'.$a['tag'].' class="'.$a['class'].'">';
		get_sidebar($a['name']);
		echo '</'.$a['tag'].'>';
		
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_footer( $atts ){
		$a = shortcode_atts( array(
	        'name' => '',
	        'class' => 'footer',
	        'tag' => 'div'
	    ), $atts );
		ob_start();
		echo '<'.$a['tag'].' class="'.$a['class'].'">';
		get_footer($a['name']);
		echo '</'.$a['tag'].'>';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function blank_shortcode_featured_posts($atts) {
		ob_start();

		$atts = shortcode_atts( array(
			'id' => 'fp_' . time(),
	        'tag' => 'featured',
	        'posts' => '5',
	    ), $atts );


		$featured_args = array(
			'tag' => $atts['tag'],
			'post_status' => 'publish',
			'posts_per_page' => $atts['posts'],
		);

		$featured = new WP_Query( $featured_args );
		echo "<ul id='" . $atts['id'] . "'>";
		if ($featured->have_posts()) {
			while ( $featured->have_posts() ) : $featured->the_post();
				echo "<li>";
				get_template_part( 'partial-templates/featured-post-list');
				echo "</li>";
			endwhile;
		
		} else {
			echo "<li>";
			echo 'Sorry, no posts matched your criteria.'; 
			echo "</li>";
		}
		echo "</ul>";
		$page = ob_get_contents();
   		ob_end_clean();
		wp_reset_postdata();
		return $page;
	}


	function blank_shortcode_post_list() {
		if ( have_posts() ) :
			echo '<div class="post-list">';
			while ( have_posts() ) : the_post();

				echo '<div class="blog-post">';
				// add a template attribute//
				if (is_single()) {
					get_template_part('content', 'single');
				} else {
					get_template_part('content', get_post_type() );
				}
				echo '</div>';
			endwhile;
			echo '</div>';
		endif;
	}

	function blank_shortcode_search_results() {
		ob_start();
		if ( have_posts() ) :
			echo '<div class="search-results">';
			while ( have_posts() ) : the_post();
				echo '<div class="search-results-item">';
				get_template_part( 'partial-templates/search-results-item');
				echo '</div>';
			endwhile;
			echo '</div>';
		else :
			echo '<h1 class="search-results-none">No result</h1>';
		endif;
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}

	function cp_get_clear_classes($index, $attr) {
			if (!isset($atts['tablet_cols']) && isset($atts['desktop_cols'])) {
	        $atts['tablet_cols'] = $atts['desktop_cols'];
	    }

	    if (!isset($atts['desktop_cols']) && isset($atts['tablet_cols'])) {
	        $atts['desktop_cols'] = $atts['tablet_cols'];
	    }

	    if (!isset($atts['mobile_cols'])) {
	    	$atts['mobile_cols'] = 1;
	    }


			$m_c = intval($atts['mobile_cols']);
      $t_c = intval($atts['tablet_cols']);
      $d_c = intval($atts['desktop_cols']);
     

      $clear = array();
      if ($index > 1) {
          if ($m_c > 1) {
              if (($index % $m_c) == 1) {
                  array_push($clear, 'clear-mobile'); 
              } 
          }
          if ($t_c > 1) {
              if (($index % $t_c) == 1) {
                  array_push($clear, 'clear-tablet'); 
              } 
          }
          if ($d_c > 1) {
              if (($index % $d_c) == 1) {
                  array_push($clear, 'clear-desktop'); 
              } 
          }                    
      }

      $clear_str = '';
      if (count($clear)) {
          $clear_str = ' '.implode(' ', $clear);
      }
      return $clear_str;
	}

	function blank_shortcode_custom_post_content($atts) {

		$default_posts_per_page = get_option( 'posts_per_page' );


		$atts = shortcode_atts( array(
			'pagination' => 'false',
			'link_prev' => '< Previous Page',
			'link_next' => 'Next Page >',
			'orderby'=> "ID",
      'order'=> "ASC",
      'posts_per_page' => $default_posts_per_page
	    ), $atts );
		global $xtd_is_template;
		global $wp_query;


		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$order_args = array('posts_per_page' => $atts['posts_per_page'], 'paged' => $paged);
        $general_orders_array = array("ID","name","author","date","modified","rand");
        
        if($atts['orderby'] !== "menu_order"){
                if(!in_array($atts['orderby'],  $general_orders_array)){
                    $order_args['meta_key'] = $atts['orderby'];
                    $order_args['orderby'] ="meta_value";
                } else {
                      $order_args['orderby'] = $atts['orderby'];
                }
                $order_args['order'] =  $atts['order'];
        } else {
            $order_args['orderby'] = 'menu_order';
            $order_args['order'] = 'ASC';
        }


		$query_args = array_merge( $wp_query->query_vars,$order_args );
		query_posts( $query_args );

		$inEditorTag = '';
		if(function_exists('xtd_in_editor') && xtd_in_editor()){
			}
		

		if ( have_posts() ) :
			if (is_single()) {
				$extras = '';
				while ( have_posts() ) : the_post();
						$extras =' post-id="'.get_the_ID().'"';

					echo '<div class="custom-post-item" '.$extras.'>';
						if (!$xtd_is_template) {
							get_template_part(  CloudPressApps::$apps_rel . '/content-detail-'.get_post_type() );
						}
					echo '</div>';
				endwhile;
				


			} else {
				echo '<div class="custom-post-list">';

        $index = 1;
				if (!$xtd_is_template) {
					$posttype = get_post_type();
					while ( have_posts() ) : the_post();
						$extras =' post-id="'.get_the_ID().'"';
           	$clear_str = cp_get_clear_classes($index, $atts);
						echo '<div class="custom-post'.$clear_str.'" '.$extras.'>';
						get_template_part(CloudPressApps::$apps_rel . '/content-'.get_post_type() );
						echo '</div>';
						$index++;
					endwhile;
				}

				global $wp_query;

				if($atts['pagination'] === 'true' && (xtd_in_editor() || $wp_query->max_num_pages > 1)) {
					echo '<div class="navigation">';
						echo '<div class="prev-navigation">';
							echo do_shortcode('[tag_prev_page_link link_prev="'.$atts['link_prev'].'"]');
						echo '</div>';
						echo '<div class="numbers-navigation">';
							echo do_shortcode('[tag_paginate_link link_prev="" link_next=""]');
						echo '</div>';
						echo '<div class="next-navigation">';
							echo do_shortcode('[tag_next_page_link link_next="'.$atts['link_next'].'"]');
						echo '</div>';
					echo '</div>';
				}
				echo '</div>';
			}
		endif;
	}

function blank_shortcode_blog_content($atts) {
		$atts = shortcode_atts( array(
			'pagination' => 'false',
			'link_prev' => '< Previous Page',
			'link_next' => 'Next Page >',
            'name'=>''
	    ), $atts );
                
        $posts_slug = "content";
        if($atts['name'] != ""){
            $posts_slug="partial-templates/content-" . $atts['name'];
        }
        $post_slug = "content";
        if($atts['name'] != ""){
            $post_slug="partial-templates/content-" . $atts['name'];
        }

        
        if(xtd_in_editor()){
        	$is_single = false || is_single();
        	if(isset($_REQUEST['page'])){
        		$is_single = $_REQUEST['page'] == "single.php";
        	}

        	if($is_single){
        		query_posts( 'posts_per_page=1');
        		global $wp_query;
				$wp_query->is_single = true; // force is_single for single plage in editor
        	} else {
        		query_posts( 'posts_per_page='. get_option('posts_per_page') );
        	}
         }


		global $xtd_is_template;
		if ( have_posts() ) :
			if (is_single()) {

				echo '<div class="post-item">';
				while ( have_posts() ) : the_post();
				if (!$xtd_is_template) {
					get_template_part($post_slug, 'single');
				}
				endwhile;
				echo '</div>';


			} else {
				echo '<div class="post-list"><div class="post-list-row">';
				if (!$xtd_is_template) {
					$posttype = get_post_type();
					while ( have_posts() ) : the_post();
						echo '<div class="blog-post">';
						get_template_part($posts_slug, $posttype);
						echo '</div>';
					endwhile;
				}

				global $wp_query;

				if($atts['pagination'] === 'true' && (xtd_in_editor() || $wp_query->max_num_pages > 1)) {
					echo '<div class="navigation">';
						echo '<div class="prev-navigation">';
							echo do_shortcode('[tag_prev_page_link link_prev="'. htmlentities($atts['link_prev']).'"]');
						echo '</div>';
						echo '<div class="numbers-navigation">';
							echo do_shortcode('[tag_paginate_link link_prev="" link_next=""]');
						echo '</div>';
						echo '<div class="next-navigation">';
							echo do_shortcode('[tag_next_page_link link_next="'.htmlentities($atts['link_next']).'"]');
						echo '</div>';
					echo '</div>';
				}
				echo '</div></div>';
			}
		endif;


		if(xtd_in_editor()){
			wp_reset_query();
		}
	}

	function blank_shortcode_page_content() {
		ob_start();
		global $xtd_is_template;
		if ( have_posts() ) :
			echo '<div class="page-content">';
			if (!$xtd_is_template) {
				while ( have_posts() ) : the_post();
					echo blank_generate_loop_tags("content-page.php");
					echo do_shortcode('[tag_page_content]');
				endwhile;
			}
			echo '</div>';
		else :
		endif;
		$page = ob_get_contents();
   		ob_end_clean();
   		return $page;
	}

	function blank_shortcode_nav_menu($args) {
		$a = shortcode_atts( array(
	        'location' => '',
	        'id' => ''
	    ), $args );

		ob_start();
		wp_nav_menu(array('location' => $a['location'], 'menu' => $a['id']));
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}

	function blank_shortcode_post_comments() {
		ob_start();
		echo '<div class="post-comments">';
		comments_template();
		echo '</div>';
		$content = ob_get_contents();
   		ob_end_clean();
   		return $content;
	}

	for ($i=0; $i < count($blank_general_widgets); $i++) { 
		add_shortcode( $blank_general_widgets[$i], 'blank_shortcode_'.$blank_general_widgets[$i]);
	}
?>