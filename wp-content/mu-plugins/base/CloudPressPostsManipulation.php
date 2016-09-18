<?php

class CloudPressPostsManipulation extends CloudPressPluginBase {

	public $templates_root = "";

	/**
	 * @var mixed
	 */
	protected $post_type;
	/**
	 * @var mixed
	 */
	protected $posts = null;

	/**
	 * @param $post_type
	 */
	function __construct($post_type) {
		$this->post_type = $post_type;
		parent::__construct(get_called_class());

	}

	/**
	 * @param array $atts
	 * @return mixed
	 */
	function get_posts($atts = array()) {
		$atts = array_merge(array(
			'id' => 'cp_' . time(),
			'type' => $this->post_type,
			'posts' => '6',
			'category' => array(),
			'tax_relation' => 'AND',
			'tag' => '',
			'page' => '1',
			'template' => false,
			'orderby' => "menu_order",
			'order' => "ASC",
			"include_children" => true,
		), $atts);

		if (!empty($atts['category'])) {
			$category = $atts['category'];
			$atts['category'] = array();
			$category = preg_replace("/\s*,\s*/", ",", $category);
			$categories = explode(',', $category);
			$atts['category'] = $categories;
		}

		if (!empty($atts['tag'])) {
			$tag = $atts['tag'];
			$atts['tag'] = array();
			$tag = preg_replace("/\s*,\s*/", ",", $tag);
			$tags = explode(',', $tag);
			$atts['tag'] = $tags;
		}

		$post_args = array(
			'post_type' => $this->post_type,
			'post_status' => 'publish',
			'posts_per_page' => $atts['posts'],
			'paged' => $atts['page'],
		);

		if (!empty($atts['category'])) {
			// var_dump($atts);

			$cat_taxonomy = 'category';

			if ($this->post_type != 'post' && $this->post_type != 'page') {
				$cat_taxonomy = $this->post_type . '_category';
			}
			$post_args['tax_query'] = array(
				'relation' => $atts['tax_relation'],
				array(
					'taxonomy' => $cat_taxonomy,
					'relation' => $cat_taxonomy,
					'field' => 'name',
					'terms' => $atts['category'],
					"include_children" => $atts["include_children"],
				),
			);
		}

		if (!empty($atts['tag'])) {

			if (!isset($post_args['tax_query'])) {
				$post_args['tax_query'] = array();
			}
			$tag_taxonomy = 'tag';

			if ($this->post_type != 'post' && $this->post_type != 'page') {
				$tag_taxonomy = $this->post_type . '_tag';
			}

			$post_args['tax_query'][] = array(
				'taxonomy' => $tag_taxonomy,
				'field' => 'name',
				'terms' => $atts['tag'],
				"include_children" => $atts["include_children"],
			);
		}

		$order_args = array();
		$general_orders_array = array("ID", "name", "author", "date", "modified", "rand");

		if ($atts['orderby'] !== "menu_order") {
			if (!in_array($atts['orderby'], $general_orders_array)) {
				$order_args['meta_key'] = $atts['orderby'];
				$order_args['orderby'] = "meta_value";
			} else {
				$order_args['orderby'] = $atts['orderby'];
			}
			$order_args['order'] = $atts['order'];
		} else {
			$order_args['orderby'] = 'menu_order';
			$order_args['order'] = 'ASC';
		}

		$post_args = array_merge($post_args, $order_args);

		if (isset($atts['featured']) && $atts['featured'] =='1') {
			$post_args['meta_query'] = array(
				array(
					'key' => '_is_featured',
				),
			);
		}
		$posts = new WP_Query($post_args);
		
		return $posts;
	}

	function decorate_template($path) {
		$path = get_template_directory() . "/" . $path . ".php";
		$path = str_replace("//", "/", $path);
		$relPath = str_replace(get_template_directory(), '', $path);
		$uid = nextSectionID();
		$filecontent = file_get_contents($path);
		$old = $filecontent;
		$filecontent = clean_internal_comments($filecontent);
		$cleaned = $filecontent;
		$filecontent = add_section_comments($filecontent);
		$sectioned = $filecontent;
		$filecontent = '<!-- TemplateBegin id="' . $uid . '" template="' . $relPath . '"-->' . '<?php echo blank_generate_loop_tags("' . $relPath . '") ?>' . $filecontent . '<!-- TemplateEnd id="' . $uid . '" -->';
		//write in temp and rename : should fix the concurency issue with reading while writing//
		xtd_write_file($path . ".temp", $filecontent, "\r\n--------old------\r\n" . $old . "\r\n--------cleaned------\r\n" . $cleaned . "\r\n--------sectioned------\r\n" . $sectioned);
		@rename($path . ".temp", $path);
	}

	function undecorate_template($path) {
		$path = get_template_directory() . "/" . $path . ".php";
		$path = str_replace("//", "/", $path);
		$filecontent = file_get_contents($path);
		$filecontent2 = clean_internal_comments($filecontent);
		if ($filecontent2 != $filecontent) {
			file_put_contents($path . ".temp", $filecontent2);
			@rename($path . ".temp", $path);
		}
	}

	/**
	 * @param $template
	 * @param array $atts
	 * @param $extra_class
	 * @param $extra_attrs
	 * @return mixed
	 */
	function get_posts_generated_html($template, $atts = array(), $extra_class = "", $extra_attrs = "") {

		if (isset($this->templates_root)) {
			$template = $this->templates_root . "/" . $template;
		}

		$posts = $this->get_posts($atts);
		$result = array();
		$dummy = false;
		if ($this->is_in_editor()) {
			if (!$posts->post_count) {
				unset($atts['category']);
				unset($atts['tag']);
				unset($atts['featured']);
				$posts = $this->get_posts($atts);
				$dummy = true;
			}

			$this->decorate_template($template);

		}
		global $post;
		$old_post = $post;
		for ($i = 0; $i < $posts->post_count; $i++) {

			$post = $posts->posts[$i];
			$posts->setup_postdata($post);
			if (in_array($post->ID, $atts['to_skip'])) {
				continue;
			}

			ob_start();

			if ($this->is_in_editor()) {
				$extra_attrs = " data-post-type=\"{$post->post_type}\" data-post-id=\"{$post->ID}\" ";
			}

			echo '<div class="' . $this->instanceNameStart . '-content ' . $extra_class . '" ' . $extra_attrs . ' >';

				if ($dummy) {
					echo "<!-- TAKE SOME POSTS TO SHOW A DEMO -->";
				}
			
				get_template_part($template);
			echo '</div>';
			$result[] = ob_get_contents();
			ob_end_clean();
			$posts->wp_reset_postdata();
		}

		wp_reset_postdata();
		setup_postdata($old_post);
		
		if ($this->is_in_editor()) {
			$this->undecorate_template($template);
		}

		return $result;
	}

	/**
	 * @param $atts
	 * @return mixed
	 */
	function get_posts_html_by_atts($atts, $to_skip = array()) {
		$template = isset($atts['template']) ? $atts['template'] : $atts['id'];

		$atts['to_skip'] = $to_skip;
		$htmls = $this->get_posts_generated_html($template, $atts, $atts['id'] . '-item');
		return $htmls;
	}

	function get_posts_list_html($atts, $to_skip = array()) {
		$posts_html = $this->get_posts_html_by_atts($atts, $to_skip);
		$atts = array_merge(array(
			'masonry' => '0',
			'delay' => '3000',
		), $atts);

		ob_start();

		$use_masonry = $atts['masonry'] == '1';
		// var_dump($atts);
		if ($use_masonry) {
			$this->use_script('cp_masonry', '/assets/masonry.js', array('jquery'));
		}

		?>
			<div id="<?php echo $atts["id"] ?>-row">
				<?php echo implode('', $posts_html) ?>
				<?php if ($use_masonry): ?>
					<script type="text/javascript">
		                jQuery(document).ready(function ($) {
		                    var $row = $("#<?php echo $atts['id'] ?>-row");
		                    if ($row.closest('[reveal-fx]').length > 0) {
		                        var masonryStarted = false;
		                        $row.closest('[reveal-fx]').on('cp-revealfx-initialized', function () {
		                            if (masonryStarted) {
		                                return;
		                            }

		                            masonryStarted = true;
		                            $row.masonry({
		                                itemSelector: '.<?php echo $atts["id"] ?>-item',
		                                isAnimated: true,
		                                gutter: 0,
		                                percentPosition: true,
		                                animationOptions: {
		                                    duration: 1200
		                                }
		                            })
		                        })
		                    } else {
		                        $row.masonry({
		                            itemSelector: '.<?php echo $atts["id"] ?>-item',
		                            isAnimated: true,
		                            gutter: 0,
		                            percentPosition: true,
		                            animationOptions: {
		                                duration: 1200
		                            }
		                        })
		                    }
		                });
		            </script>
				<?php endif;?>
			</div>
		<?php
		$list_content = ob_get_contents();
		ob_end_clean();
		return $list_content;

	}

	function get_posts_owl_slider_html($atts, $to_skip = array()) {
		$posts_html = $this->get_posts_html_by_atts($atts, $to_skip);
		$atts = array_merge(array(
			'speed' => '800',
			'delay' => '3000',
			'autoplay' => "true",
			'drag' => "true",
			"autoheight" => "true",
			"bullets" => "false",
			"transition" => ""), $atts);
		ob_start();
		?>
			<div id="<?php echo $atts['id'] ?>" data-bullets="<?php echo $atts['bullets'] ?>" data-slider-speed="<?php echo $atts['speed'] ?>" data-delay="<?php echo $atts['delay'] ?>" data-autoplay="<?php echo $atts['autoplay'] ?>" data-drag="<?php echo $atts['drag'] ?>" data-autoheight="<?php echo $atts['autoheight'] ?>" data-transition="<?php echo $atts['transition'] ?>">
      			<?php echo do_shortcode('[xtd_simple_slider id="' . $atts['id'] . '"]'); ?>
      			<style type="text/css">
      				div#<?php echo $atts['id'] ?>{
      					position: relative;
      				}
      			</style>
  				<div class="content-wrapper owl-carousel owl-theme">
                	<div class="<?php echo $atts['id'] ?>_content">
      					<?php foreach ($posts_html as $post_html): ?>
      						<div class="item <?php echo $this->post_type; ?>-post" >
      							<?php echo $post_html; ?>
      						</div>
      					<?php endforeach;?>
      				</div>


  				    <div class="owl-controls clickable" data-fake-pagination="true">
                    	<div class="owl-pagination">
                    		<?php for ($i = 0; $i < count($posts_html); $i++): ?>
                    			<?php if ($i == 0): ?>
                                	<div class="owl-page active"><span class=""></span></div>
	                        <?php else: ?>
	                                <div class="owl-page"><span class=""></span></div>
				            <?php endif;?>
                    	    <?php endfor;?>
                    	</div>
                    </div>
      			</div>

	            <div class="owl-prev" data-slider="<?php echo $atts['id'] ?>-prev">
	                <div class=""></div>
	            </div>

            	<div class="owl-next" data-slider="<?php echo $atts['id'] ?>-next">
                	<div class=""></div>
            	</div>
      		</div>
		<?php
		$slider_content = ob_get_contents();
		ob_end_clean();
		return $slider_content;
	}

	function get_posts_owl_carousel_html($atts, $to_skip = array()) {
		$posts_html = $this->get_posts_html_by_atts($atts, $to_skip);
		$atts = array_merge(array(
			'speed' => '800',
			'delay' => '3000',
			'drag' => "true",
			"transition" => "",
			'desktop' => "4",
			'tablet' => "2",
			'mobile' => "1"), $atts);
		ob_start();
		?>
			<div id="<?php echo $atts['id'] ?>" data-slider-speed="<?php echo $atts['speed'] ?>" data-slider-items-desktop="<?php echo $atts['desktop'] ?>" data-slider-items-tablet="<?php echo $atts['tablet'] ?>" data-slider-items-mobile="<?php echo $atts['mobile'] ?>" data-delay="<?php echo $atts['delay'] ?>" data-transition="<?php echo $atts['transition'] ?>" data-drag="<?php echo $atts['drag'] ?>">
      			<?php echo do_shortcode('[xtd_simple_carousel id="' . $atts['id'] . '"]'); ?>
      			<style type="text/css">
      				div#<?php echo $atts['id'] ?>{
      					position: relative;
      				}
      			</style>
  				<div class="content-wrapper owl-carousel owl-theme">
                	<div class="<?php echo $atts['id'] ?>_content">
      					<?php foreach ($posts_html as $post_html): ?>
      						<div class="item_wrapper">
	      						<div class="item <?php echo $this->post_type; ?>-post" >
	      							<?php echo $post_html; ?>
	      						</div>
      						</div>
      					<?php endforeach;?>
      				</div>


  				    <div class="owl-controls clickable" data-fake-pagination="true">
                    	<div class="owl-pagination">
                    		<?php for ($i = 0; $i < count($posts_html); $i++): ?>
                    			<?php if ($i == 0): ?>
                                	<div class="owl-page active"><span class=""></span></div>
	                        <?php else: ?>
	                                <div class="owl-page"><span class=""></span></div>
				            <?php endif;?>
                    	    <?php endfor;?>
                    	</div>
                    </div>
      			</div>

	            <div class="owl-prev" data-slider="<?php echo $atts['id'] ?>-prev">
	                <div class=""></div>
	            </div>

            	<div class="owl-next" data-slider="<?php echo $atts['id'] ?>-next">
                	<div class=""></div>
            	</div>
      		</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function get_default_template_content() {
		return "" .
			'<h1 class="heading1">' . "\n" .
			'	<a href="<?php echo do_shortcode(\'[cp_post_meta name="cp_post_permalink"]\'); ?>" >' . "\n" .
			'		<?php echo do_shortcode(\'[cp_post_meta name="cp_title"]\'); ?>' . "\n" .
			'	</a>' . "\n" .
			'</h1>' . "\n" .
			'' . "\n" .
			'<div class="">' . "\n" .
			'		<span><?php echo do_shortcode(\'[tag_post_excerpt]\'); ?></span>' . "\n" .
			'</div>';
	}

	/**
	 * @param $source file path or html string
	 * @param $target file path
	 * @param $source_is_file default true
	 * @return $new_template_path new template path
	 */
	function add_new_template($source, $target, $source_is_file = true) {
		$base_path = wp_get_theme()->get_template_directory() . "/";

		if ($this->templates_root) {
			if (!file_exists($base_path . $this->templates_root)) {
				mkdir($base_path . $this->templates_root, 0755, true);
			}

		}

		$target = preg_replace("/(\.php$)/", "", $target) . ".php";
		if ($source_is_file) {
			$source = preg_replace("/(\.php$)/", "", $source);
			if (file_exists($base_path . $source . ".php")) {
				@copy($base_path . $source, $base_path . $this->templates_root . "/" . $target);
			}
		} else {
			@file_put_contents($base_path . $this->templates_root . "/" . $target, $source);
		}

		return $base_path . $target;
	}

	function get_main_loop_post() {
		global $post;
		return $post;
	}

	function get_post_categories($post, $atts = array()) {

		$cat_taxonomy = 'category';

		if ($post->post_type != 'post' && $post->post_type != 'page') {
			$cat_taxonomy = $post->post_type . '_category';
		}

		return wp_get_object_terms($post->ID, $cat_taxonomy, $atts);
	}

	function get_post_tags($post, $atts = array()) {

		$tag_taxonomy = 'tag';

		if ($post->post_type != 'post' && $post->post_type != 'page') {
			$tag_taxonomy = $post->post_type . '_tag';
		}

		return wp_get_object_terms($post->ID, $tag_taxonomy, $atts);
	}

}