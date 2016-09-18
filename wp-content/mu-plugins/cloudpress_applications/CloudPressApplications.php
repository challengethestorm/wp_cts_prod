<?php
// [cp_app_slider]
// [cp_app_list id="" type=""]
// [cp_app_carousel]
class CloudPressApps {
	private static $apps;
	private static $apps_type = array('list', 'slider', 'carousel');
	static $actionParameter = 'cp_apps_action';
	static $apps_rel = "/apps";
	static $newAppToRegister = array();
	static $custom_apps = array();
	static $custom_apps_tax = array();
	static $customFieldsTypes = array(
		"text" => array(
			'label' => "Text",
			'handler' => 'handle_custom_field_text',
			'parser' => 'parse_custom_field_text',
			'default' => '%FIELD_NAME% default text',
		),
		"link" => array(
			'label' => "Link",
			'handler' => 'handle_custom_field_link',
			'parser' => 'parse_custom_field_link',
			'default' => 'http://cloud-press.net',
		),
		"email" => array(
			'label' => "Email",
			'handler' => 'handle_custom_field_email',
			'parser' => 'parse_custom_field_email',
			'default' => 'admin@domain.com',
		),
		"image" => array(
			'label' => "Image",
			'handler' => 'handle_custom_field_image',
			'parser' => 'parse_custom_field_image',
			'default' => '@@SITE_URL@@/wp-content/uploads/2014/09/placeholder_image-150x150.png',
		),
		"video" => array(
			'label' => "Video",
			'handler' => 'handle_custom_field_video',
			'parser' => 'parse_custom_field_video',
			'default' => 'https://www.youtube.com/watch?v=ZbrKVRlmgYo',
		),
	);
	// APPS MANAGEMENT
	public static function get_instances() {
		try {
			if (!self::$apps) {
				self::$apps = get_instances_as_array("CloudPressApps");
			}
		} catch (Exception $e) {
		}
		if (!self::$apps) {
			self::$apps = array();
		}
		return self::$apps;
	}
	public static function apps_dir() {
		$dir = wp_get_theme()->get_template_directory() . self::$apps_rel;
		if (!file_exists($dir)) {
			@mkdir($dir);
		}
		return $dir;
	}
	public static function get_app_by_slug($slug) {
		$toReturn = array();
		$instances = self::get_instances();
		foreach ($instances as $name => $props) {
			if ($props['slug'] == $slug) {
				$toReturn = $props;
				break;
			}
		}
		if (empty($toReturn)) {
			$toReturn = false;
		}
		return $toReturn;
	}
	public static function get_app_by_name($find_name) {
		$toReturn = array();
		$instances = self::get_instances();
		foreach ($instances as $name => $props) {
			if ($find_name == $name) {
				$toReturn = $props;
				break;
			}
		}
		if (empty($toReturn)) {
			$toReturn = false;
		}
		return $toReturn;
	}
	public static function get_app_instances($appName) {
		$app = self::get_app_by_name($appName);
		if (!$app) {
			$app = self::get_app_by_slug($appName);
		}
		return get_instances_as_array($app['slug'] . "_Instances");
	}
	public static function add_new() {
		$data = $_REQUEST;
		$slug = preg_replace("/[^[:alnum:][:space:]]/ui", '', $data['name']);
		$slug = str_replace(" ", "", $slug);
		$data['custom_fields'] = ($data['custom_fields']) ? $data['custom_fields'] : array();
		$data['custom_fields_type'] = ($data['custom_fields_type']) ? $data['custom_fields_type'] : array();
		$merge_custom_fields = array();
		for ($i = 0; $i < count($data['custom_fields']); $i++) {
			if (strlen($data['custom_fields'][$i]) > 0) {
				$merge_custom_fields[$data['custom_fields'][$i]] = $data['custom_fields_type'][$i];
			}
		}
		self::save_custom_post_type($slug, $data['name'], $data['singular'], '', $merge_custom_fields, '', '', isset($data['has-detail']));
		self::add_new_template($slug, $data['name'], $data['singular'], '', $merge_custom_fields, '', '', isset($data['has-detail']));
		// update_option('scporder_options', $input_options);
	}
	public static function update_app() {
		$data = $_REQUEST;
		$appName = $data['name'];
		$data['custom_fields'] = ($data['custom_fields']) ? $data['custom_fields'] : array();
		$data['custom_fields_type'] = ($data['custom_fields_type']) ? $data['custom_fields_type'] : array();
		$merge_custom_fields = array();
		for ($i = 0; $i < count($data['custom_fields']); $i++) {
			if (strlen($data['custom_fields'][$i]) > 0) {
				$merge_custom_fields[$data['custom_fields'][$i]] = $data['custom_fields_type'][$i];
			}
		}
		$icon = $data['icon'];
		$apps = self::get_instances();
		$app = $apps[$appName];
		$app['has-detail'] = @(isset($data['has-detail']));
		$app['fields'] = $merge_custom_fields;
		if (!empty($icon)) {
			$app['icon'] = str_replace(site_url(), "", $icon);
		}
		put_instance_in_file("CloudPressApps", $appName, $app);
	}
	static function save_custom_post_type($slug, $name, $singular, $archive, $fields, $category, $tag, $has_detail = false) {
		$data = $_REQUEST;
		$icon = ($_REQUEST['icon']) ? $_REQUEST['icon'] : "";
		$icon = str_replace(site_url(), "", $icon);
		$slug = strtolower($slug);
		// $parsedFields = json_decode(str_replace('\"', '"', $fields), true);
		$data = array(
			'name' => $name,
			'singular' => $singular,
			'archive' => 'true',
			'fields' => $fields,
			'slug' => $slug,
			'icon' => $icon,
			'enabled' => true,
			'has-detail' => $has_detail,
		);
		put_instance_in_file("CloudPressApps", $name, $data);
		$scporder_options = get_option('scporder_options') ? get_option('scporder_options') : array();
		if (!isset($scporder_options['objects'])) {
			$scporder_options['objects'] = array();
		}
		$scporder_options['objects'][] = $slug;
		update_option('scporder_options', $scporder_options);
	}
	static function new_instance() {
		self::set_defaults();
		$instanceName = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
		$instanceName = self::look_for_instance($instanceName);
		die($instanceName);
		return;
	}
	static function add_new_template($slug, $name, $singular, $archive, $fields, $category, $tag, $appDefaultsPostContent = false, $has_detail = false) {
		$slug = strtolower($slug);
		if (!file_exists(self::apps_dir())) {
			mkdir(self::apps_dir());
		}
		self::add_default_posts($slug, $name, $singular, $archive, $fields, $category, $tag, $appDefaultsPostContent, $has_detail);
		$templatePath = self::apps_dir() . '/' . $slug . '-post-list.php';
		$template = "" .
			"<h3 class='custom-post-title'><?php echo do_shortcode('[tag_post_title]') ?></h3>" .
			"<div class='custom-post-content'><?php echo do_shortcode('[tag_post_excerpt]') ?></div>" .
			"<hr/>";
		file_put_contents($templatePath, $template);
		//add single blog template from blank templates
		$blankPath = wp_get_theme()->get_template_directory() . '/blank-templates/content-custom.php';
		$templatePath = self::apps_dir() . '/content-' . $slug . '.php';
		$content = file_get_contents($blankPath);
		file_put_contents($templatePath, $content);
		$blankPath = wp_get_theme()->get_template_directory() . '/blank-templates/content-custom.php';
		$templatePath = self::apps_dir() . '/content-detail-' . $slug . '.php';
		$content = file_get_contents($blankPath);
		file_put_contents($templatePath, $content);
		$blankPath = wp_get_theme()->get_template_directory() . '/blank-templates/single-custom.php';
		$templatePath = self::apps_dir() . '/single-' . $slug . '.php';
		$content = file_get_contents($blankPath);
		file_put_contents($templatePath, $content);
		$blankPath = wp_get_theme()->get_template_directory() . '/blank-templates/archive-custom.php';
		$templatePath = self::apps_dir() . '/archive-' . $slug . '.php';
		$content = file_get_contents($blankPath);
		file_put_contents($templatePath, $content);
		//and style css
		$blankPath = wp_get_theme()->get_template_directory() . '/blank-templates/single-custom.css';
		$templatePath = self::apps_dir() . '/single-' . $slug . '.css';
		$content = file_get_contents($blankPath);
		file_put_contents($templatePath, $content);
		$blankPath = wp_get_theme()->get_template_directory() . '/blank-templates/archive-custom.css';
		$templatePath = self::apps_dir() . '/archive-' . $slug . '.css';
		$content = file_get_contents($blankPath);
		file_put_contents($templatePath, $content);
		$blankPath = wp_get_theme()->get_template_directory() . '/blank-templates/content-custom.css';
		$templatePath = self::apps_dir() . '/content-' . $slug . '.css';
		$content = file_get_contents($blankPath);
		file_put_contents($templatePath, $content);
		$blankPath = wp_get_theme()->get_template_directory() . '/blank-templates/content-custom.css';
		$templatePath = self::apps_dir() . '/content-detail-' . $slug . '.css';
		$content = file_get_contents($blankPath);
		file_put_contents($templatePath, $content);
	}
	static function add_default_posts($slug, $name, $singular, $archive, $fields, $category, $tag, $appDefaultsPostContent, $has_detail) {
		$slug = strtolower($slug);
		$has_posts = wp_count_posts($slug);
		$has_posts = $has_posts->publish > 0;
		if($has_posts){
			return;
		}
		$category = preg_replace("/\s*,\s*/", ",", $category);
		$tag = preg_replace("/\s*,\s*/", ",", $tag);
		$categories = explode(',', $category);
		for ($i = 0; $i < count($categories); $i++) {
			$cat = get_term_by('name', $categories[$i], $slug . '_category');
			$id = 0;
			if ($cat) {
				$id = $cat->term_id;
			}
			if ($id === 0) {
				$id = wp_insert_term($categories[$i], $slug . '_category');
			}
		}
		register_taxonomy(
			$slug . '_category', $slug, array(
				'labels' => array(
					'name' => 'Category',
					'add_new_item' => 'Add New Category',
					'new_item_menu' => 'New Category',
				),
				'show_ui' => true,
				'show_tagcloud' => false,
				'hierarchical' => true,
				'show_admin_column' => true,
			)
		);
		register_taxonomy(
			$slug . '_tag', $slug, array(
				'labels' => array(
					'name' => 'Tags',
					'add_new_item' => 'Add New Tag',
					'new_item_menu' => 'New Tag',
				),
				'show_ui' => true,
				'show_tagcloud' => true,
				'hierarchical' => false,
				'show_admin_column' => true,
			)
		);
		$tags = explode(',', $tag);
		for ($i = 0; $i < count($tags); $i++) {
			$t = get_term_by('name', $tags[$i], $slug . '_tag');
			$id = 0;
			if ($t) {
				$id = $t->term_id;
			}
			if ($id === 0) {
				$id = wp_insert_term($tags[$i], $slug . '_tag');
			}
			// $tags[$i] = $id;
		}
		$categories = array();
		if (isset($appDefaultsPostContent['posts_categories'])) {
			$categories = $appDefaultsPostContent['posts_categories'];
			foreach ($appDefaultsPostContent['posts_categories'] as $key => $value) {
				$t = get_term_by('name', $key, $slug . '_category');
				$id = 0;
				if ($t) {
					$id = $t->term_id;
				}
				if ($id === 0) {
					$id = wp_insert_term($tags[$i], $slug . '_category');
				}
			}
		}
		$total_defaults_posts = isset($appDefaultsPostContent['nr_of_items']) ? $appDefaultsPostContent['nr_of_items'] : 6;
		for ($i = 0; $i < $total_defaults_posts; $i++) {
			$post_content = "<p><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam tortor augue, imperdiet eget consequat vel, facilisis eu neque." .
				" Nulla imperdiet erat ante, non rutrum lectus sollicitudin ut. Vestibulum dignissim libero vitae dui tincidunt sagittis. " .
				"Suspendisse scelerisque magna ex, in malesuada mi vestibulum at. Pellentesque odio felis, gravida nec augue sit amet, eleifend consequat sapien. " .
				"Vivamus id placerat justo. Vivamus aliquam nisi sed ipsum aliquam tincidunt.</span></p>";
			if ($has_detail) {
				$post_content = $post_content . "<!--more--><p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo " .
					"inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos " .
					"qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et" .
					" dolore magnam aliquam quaerat voluptatem.</p>";
			}
			if ($appDefaultsPostContent && isset($appDefaultsPostContent['post_content'])) {
				$post_content = $appDefaultsPostContent['post_content'];
			}
			$post_title = "";
			$post_name = "";
			if (isset($appDefaultsPostContent['post_titles'])) {
				if (is_array($appDefaultsPostContent['post_titles']) && count($appDefaultsPostContent['post_titles']) > $i) {
					$post_title = $appDefaultsPostContent['post_titles'][$i];
				} else {
					$post_title = $appDefaultsPostContent['post_titles'] . " " . ($i + 1);
				}
			} else {
				$post_title = $singular . " " . ($i + 1);
			}
			$post_name = sanitize_title($post_title);
			$post = array(
				'post_type' => $slug,
				'post_status' => 'publish',
				'post_content' => $post_content,
				'post_title' => $post_title,
				'post_name' => $post_name,
			);
			$id = wp_insert_post($post, true);
			//wp_set_post_categories($id, $categories);
			// wp_set_object_terms($id, $categories, $slug . '_category');
			wp_set_object_terms($id, $tags, $slug . '_tag');
			$cats_to_post = array();
			if (count($categories)) {
				foreach ($categories as $category => $positions) {
					if ($positions['start'] <= $i && $positions['length'] > 0) {
						$categories[$category]['length'] = $categories[$category]['length'] - 1;
						$cats_to_post[] = $category;
					}
				}
			}
			if (count($cats_to_post)) {
				wp_set_object_terms($id, $cats_to_post, $slug . '_category');
			}
			if ($fields) {
				foreach ($fields as $field => $type) {
					$toAddValue = self::$customFieldsTypes[$type]['default'];
					$toAddValue = str_replace("%FIELD_NAME%", $field, $toAddValue);
					if ($appDefaultsPostContent && isset($appDefaultsPostContent[$field])) {
						$toAddValue = $appDefaultsPostContent[$field];
						$toAddValue = str_replace("@@INDEX@@", $i + 1, $toAddValue);
					}
					add_post_meta($id, $field, $toAddValue, true);
				}
			}
		}
	}
	// INSTANCES && SHORTCODES
	static function init() {
		add_action('admin_init', array(__CLASS__, 'admin_init'));
		foreach (self::$apps_type as $app) {
			add_shortcode('cp_app_' . $app, array(__CLASS__, 'handle_' . $app . '_shortcode'));
		}
		self::add_custom_actions();
		self::register_custom_actions();
		add_action('init', array(__CLASS__, 'register_types'));
		add_filter('single_template', array(__CLASS__, 'apps_single_template_filter'), 10, 1);
		add_filter('archive_template', array(__CLASS__, 'apps_archive_template_filter'), 10, 1);
		if (isset($_REQUEST['xtd_stripped'])) {
			add_action('admin_menu', array(__CLASS__, 'remove_menu_popup'));
			add_action('wp_before_admin_bar_render', array(__CLASS__, 'admin_bar_render_popup'), 0);
			add_filter('screen_options_show_screen', array(__CLASS__, 'remove_options_popup'));
			add_action('admin_head', array(__CLASS__, 'add_style_popup'));
			add_action('admin_footer', array(__CLASS__, 'add_scripts_popup'));
		}
		add_filter("wp_loaded", array(__CLASS__, 'post_type_labels'));
		add_filter('post_row_actions', array(__CLASS__, 'hide_links_for_no_details_app'), 10, 2);
		add_filter('default_title', array(__CLASS__, 'set_app_item_defaults'), 10, 2);
	}
	static function remove_all() {
		$content = get_instances_as_array();
		$apps = $content['CloudPressApps'];
		foreach ($apps as $app_name => $props) {
			$mycustomposts = get_posts(array('post_type' => $props['slug'], 'number' => 1000));
			//print_r($mycustomposts);
			foreach ($mycustomposts as $mypost) {
				if ($fields) {
					foreach ($fields as $field => $type) {
						echo "delete_post_meta:" . $mypost->ID . "##" . $field . "\r\n";
						//delete_post_meta($mypost->ID, $field);
					}
				}
				echo "wp_remove_object_terms:" . $mypost->ID . "##" . $props['slug'] . '_tag' . "\r\n";
				wp_remove_object_terms($mypost->ID, null, $props['slug'] . '_tag');
				wp_delete_post($mypost->ID, true);
				echo "wp_delete_post:" . $mypost->ID . "\r\n";
				$taxonomy = $props['slug'] . "_tag";
				$terms = get_terms($taxonomy);
				$count = count($terms);
				if ($count > 0) {
					foreach ($terms as $term) {
						echo "wp_delete_term:" . $mypost->term_id . "##" . $taxonomy . "\r\n";
						wp_delete_term($term->term_id, $taxonomy);
					}
				}
			}
		}
		unset($content['CloudPressApps']);
		$fp = get_plugin_data_folder() . '/instances.php';
		file_put_contents($fp, "<?php return '" . json_encode($content) . "';");
		//unlink(self::apps_dir());
	}
	static function admin_init() {
	}
	// filter text custom fields ( there aren't attributes shortcodes)
	static function attribute_shortcode_filter($response, $shortcode) {
		global $post;
		try {
			if (strpos($shortcode, 'cp_post_meta') !== FALSE) {
				if (strpos($shortcode, 'cp_post_permalink') !== FALSE) {
					return true;
				}
				$shortcode = html_entity_decode($shortcode);
				$app = CloudPressApps::get_app_by_slug($post->post_type);
				$fields = $app['fields'];
				$matches = array();
				preg_match('/name="(.*?)"/s', $shortcode, $matches);
				$field_name = array_pop($matches);
				if (!isset($fields[$field_name])) {
					return false;
				}
				switch ($fields[$field_name]) {
				case "text":
				case "video";
					$response = false;
					break;
				}
			}
		} catch (Exception $e) {
		}
		return $response;
	}
	static function custom_field_types_() {
		$toReturn = array();
		$instances = self::get_instances();
		foreach ($instances as $name => $props) {
			$toReturn[$name] = $props['fields'];
		}
		$toReturn = json_encode($toReturn, JSON_PRETTY_PRINT);
		
		return $toReturn;
	}

	static function custom_field_types() {
		die(self::custom_field_types_());
	}
	
	static function apps_single_template_filter($single) {
		global $wp_query, $post;
		$apps = self::get_instances();
		$post_type = $post->post_type;
		if (self::get_app_by_slug($post_type)) {
			wp_register_style('app_style_' . $post_type, get_template_directory_uri() . "/apps/single-$post_type.css", false);
			$single = self::apps_dir() . "/single-$post_type.php";
		}
		return $single;
	}
	static function apps_archive_template_filter($templateContent) {
		global $wp_query, $post;
		$apps = self::get_instances();
		$post_type = $post->post_type;
		if (self::get_app_by_slug($post_type)) {
			$templateContent = self::apps_dir() . "/archive-$post_type.php";
		}
		return $templateContent;
	}
	static function add_post_blank_tags($slug) {
		global $blank_tags_shortcodes;
		if (!is_array($blank_tags_shortcodes) || empty($blank_tags_shortcodes)) {
			$blank_tags_shortcodes = array();
		}
		$blank_tags_shortcodes[$slug] = array();
		$app_fields = CloudPressApps::get_app_by_slug($slug);
		$app_fields = $app_fields['fields'];
		foreach ($app_fields as $field => $type) {
			$blank_tags_shortcodes[$slug][] = 'cp_post_meta name="' . trim($field) . '"';
		}
		$blank_tags_shortcodes[$slug][] = 'cp_post_meta name="cp_post_permalink"';
		$blank_tags_shortcodes[$slug] = array_merge($blank_tags_shortcodes[$slug], get_all_cp_post_meta_shortcodes());
		if (isset($blank_tags_shortcodes['post'])) {
			foreach ($blank_tags_shortcodes['post'] as $sc) {
				if (!in_array($sc, $blank_tags_shortcodes[$slug])) {
					$blank_tags_shortcodes[$slug][] = $sc;
				}
			}
		}
	}
	static function render_dummy_ul() {
		global $xtd_decorate_files;
		if (!xtd_in_editor()) {
			return;
		}
		CloudPressApps::add_post_blank_tags(get_post_type());
		$xtd_decorate_files_bkp = $xtd_decorate_files;
		$xtd_decorate_files = true;
		echo blank_generate_loop_tags(get_post_type());
		$xtd_decorate_files = $xtd_decorate_files_bkp;
	}
	static function register_types() {
		$obj = CloudPressApps::get_instances();
		if (!isset($scporder_options['objects'])) {
			$scporder_options['objects'] = array();
		}
		CloudPressApps::$custom_apps = array();
		foreach ($obj as /* $slug => */$props) {
			$slug = $props['slug'];
			if (!isset($props['enabled']) || !$props['enabled']) {
				continue;
			}
			register_taxonomy(
				$slug . '_category', $slug, array(
					'labels' => array(
						'name' => $props['name'] . ' Categories',
						'menu_name' => ' Categories',
						'add_new_item' => 'Add New Category',
						'new_item_menu' => 'New Category',
					),
					'show_ui' => true,
					'show_tagcloud' => false,
					'hierarchical' => true,
					'show_admin_column' => true,
				)
			);
			register_taxonomy(
				$slug . '_tag', $slug, array(
					'labels' => array(
						'name' => $props['name'] . ' Tags',
						'menu_name' => 'Tags',
						'add_new_item' => 'Add New Tag',
						'new_item_menu' => 'New Tag',
					),
					'show_ui' => true,
					'show_tagcloud' => true,
					'hierarchical' => false,
					'show_admin_column' => true,
				)
			);
			$post_type_args = array(
				'labels' => array(
					'name' => $props['name'],
					'singular_name' => $props['singular'],
				),
				'public' => true,
				'menu_icon' => '',
				'taxonomies' => array($slug . '_category', $slug . '_tag'),
				'has_archive' => $props['archive'] === 'true',
				'publicly_queryable' => true,
				'rewrite' => array('slug' => preg_replace("/[^a-zA-Z0-9\p{P}]/", "-", $slug), 'with_front' => false),
				'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'comments'),
				'register_meta_box_cb' => array(__CLASS__, 'handle_custom_post_interface'),
			);
			if (@$props['icon']) {
				$post_type_args['menu_icon'] = site_url() . "/" . $props['icon'];
			}
			self::add_post_blank_tags($slug);
			register_post_type($slug, $post_type_args);
			register_taxonomy_for_object_type($slug . '_category', $slug);
			register_taxonomy_for_object_type($slug . '_tag', $slug);
			CloudPressApps::$custom_apps[$slug] = true;
			array_push(CloudPressApps::$custom_apps_tax, $slug . '_category');
			array_push(CloudPressApps::$custom_apps_tax, $slug . '_tag');
		}
		if (!is_admin()) {
			add_filter('pre_get_posts', array('CloudPressApps', 'fix_archive_pagination'));
		}
	}
	static function fix_archive_pagination($query) {
		if ($query->is_main_query()) {
			$post_type = $query->get('post_type');
			$is_taxonomy = is_tax(CloudPressApps::$custom_apps_tax);
			/*
				              foreach (CloudPressApps::$custom_apps as $tax => $value) {
				              if (is_tax(CloudPressApps::$custom_apps_tax) || is_tax($tax."_category")) {
				              $is_taxonomy = true;
				              break;
				              }
			*/
			if (isset(CloudPressApps::$custom_apps[$post_type]) || $is_taxonomy) {
				$query->set('posts_per_page', 1);
			}
		}
		return $query;
	}
	static function handle_custom_post_interface() {
		add_action('do_meta_boxes', array(__CLASS__, 'handle_metaboxes'), 0, 3);
	}
	static function post_type_labels() {
		$apps = self::get_instances();
		global $wp_post_types;
		foreach ($apps as $key => $app) {
			@$wp_post_types[$app['slug']]->labels->name = ucfirst($app['name']);
			@$wp_post_types[$app['slug']]->labels->all_items = ucfirst($app['name']);
			@$wp_post_types[$app['slug']]->labels->menu_name = ucfirst($app['name']);
			@$wp_post_types[$app['slug']]->labels->singular_name = ucfirst($app['singular']);
			@$wp_post_types[$app['slug']]->labels->edit_item = "Edit " . ucfirst($app['singular']);
			@$wp_post_types[$app['slug']]->labels->add_new_item = "Add New " . ucfirst($app['singular']);
			@$wp_post_types[$app['slug']]->labels->new_item = "New " . ucfirst($app['singular']);
			@$wp_post_types[$app['slug']]->labels->view_item = "View " . ucfirst($app['singular']);
			@$wp_post_types[$app['slug']]->labels->name_admin_bar = ucfirst($app['singular']);
			@$wp_post_types[$app['slug']]->labels->search_items = "Search " . ucfirst($app['singular']);
			@$wp_post_types[$app['slug']]->labels->not_found = "No " . ucfirst($app['singular']) . " found.";
			@$wp_post_types[$app['slug']]->labels->not_found_in_trash = "No " . ucfirst($app['singular']) . " found in Trash.";
		}
	}
	static function hide_links_for_no_details_app($actions) {
		global $post;
		$type = $post->post_type;
		$app = self::get_app_by_slug($type);
		if (!$app) {
			return $actions;
		}
		if ($app['has-detail'] === false) {
			unset($actions['view']);
		}
		return $actions;
	}
	static function set_app_item_defaults($title, $post) {
		$slug = $post->post_type;
		$app = self::get_app_by_slug($slug);
		if (!$app) {
			return $title;
		}
		$post->post_content = "<p><span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam tortor augue, imperdiet eget consequat vel, facilisis eu neque." .
			" Nulla imperdiet erat ante, non rutrum lectus sollicitudin ut. Vestibulum dignissim libero vitae dui tincidunt sagittis. " .
			"Suspendisse scelerisque magna ex, in malesuada mi vestibulum at. Pellentesque odio felis, gravida nec augue sit amet, eleifend consequat sapien. " .
			"Vivamus id placerat justo. Vivamus aliquam nisi sed ipsum aliquam tincidunt.</span></p>";
		$fields = $app['fields'];
		if ($fields) {
			foreach ($fields as $field => $type) {
				$toAddValue = self::$customFieldsTypes[$type]['default'];
				$toAddValue = str_replace("%FIELD_NAME%", $field, $toAddValue);
				add_post_meta($post->ID, $field, $toAddValue, true);
			}
		}
		$title = "New " . $app['singular'];
		return $title;
	}
	static function handle_metaboxes() {
		global $post;
		global $wp_meta_boxes;
		$custom_fields_metabox = @$wp_meta_boxes[$post->post_type]['normal']['core']['postcustom'];
		if (!$custom_fields_metabox) {
			return;
		}
		$custom_fields_metabox['title'] = "CloudPress Apps Custom Fields";
		$custom_fields_metabox['callback'] = array(__CLASS__, 'handle_custom_post_metabox');
		$wp_meta_boxes[$post->post_type]['normal']['core']['postcustom'] = $custom_fields_metabox;
	}
	static function handle_custom_post_metabox() {
		global $post;
		global $wp_meta_boxes;
		$app = self::get_app_by_slug($post->post_type);
		$app_fields = $app['fields'];
		$post_metas = get_post_meta($post->ID);
		wp_register_style('cloudpress_app_post_css', plugins_url('/assets/post-edit.css', __FILE__), false);
		wp_enqueue_style('cloudpress_app_post_css');
		wp_register_script('cloudpress_app_post_js', plugins_url('/assets/post-edit.js', __FILE__), array('jquery'));
		wp_enqueue_script('cloudpress_app_post_js');
		$url = wp_nonce_url(site_url() . "/?" . self::$actionParameter . '=change_field_value&', 'change_field_value');
		?>
        <div id="postcustomstuff" data-update-url="<?php echo $url?>">
            <table id="list-table">
                <thead>
                    <tr>
                        <th class="left">Name</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody id="the-list" data-wp-lists="list:meta">
        <?php foreach ($app_fields as $field => $type): ?>
                        <tr id="<?php echo strtolower(str_replace(" ", "_", $field)) ?>" class="custom-post-field">
                            <td class="left">
                                <p class="custom-field-name"><?php echo $field?></p>
                            </td>
                            <td data-type="<?php echo $type?>" data-field="<?php echo $field?>" data-id="<?php echo $post->ID?>">
                                <div class="field-updated"><span>Field updated</span></div>
                                <?php $extra_btn = ""?>
                                <?php if ($type == "text"): ?>
                                    <textarea name="<?php echo $field?>" rows="2" cols="30"><?php echo $post_metas[$field][0]?></textarea>
                                <?php elseif ($type == "image"): ?>
                                    <?php echo self::handle_custom_field_image($post_metas[$field][0], "img", "", 'name="' . $field . '"')?>
                                    <?php
$extra_btn .= '' .
			'<input type="submit" data-meta-key="' . $field . '"  data-image="true" class="button updatemeta button-small change-meta-image" value="Change">';
		?>
                                <?php elseif ($type == "video"): ?>
                                    <div>
                                        <?php
ob_start();
		echo do_shortcode('[xtd_video id="preview-video" src="' . $post_metas[$field][0] . '"]');
		$video_content = ob_get_contents();
		ob_end_clean();
		?>
                                        <?php echo $video_content?>
                                    </div>
                                    <textarea name="<?php echo $field?>" rows="2" cols="30"><?php echo $post_metas[$field][0]?></textarea>
                                <?php elseif ($type == "link" || $type == "email"): ?>
                                    <textarea name="<?php echo $field?>" rows="2" cols="30"><?php echo $post_metas[$field][0]?></textarea>
                                <?php endif;?>
                                <div class="submit">
                                    <input type="submit" name="update" data-meta-key="<?php echo $field?>" class="button updatemeta button-small updatebutton" value="Update">
                                    <?php echo $extra_btn?>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
        <?php
}
	static function register_custom_actions() {
		if (isset($_GET[self::$actionParameter]) || isset($_POST[self::$actionParameter])) {
			$action = $_REQUEST[self::$actionParameter];
			do_action(self::$actionParameter . '_' . $action);
		}
	}
	static function add_custom_actions() {
		add_action(self::$actionParameter . '_' . 'new', array(__CLASS__, 'new_app_instance'));
		add_action(self::$actionParameter . '_' . 'custom_field_types', array(__CLASS__, 'custom_field_types'));
		add_action(self::$actionParameter . '_' . 'change_field_value', array(__CLASS__, 'change_field_value'));
	}
	static function new_app_instance() {
		$data = $_REQUEST;
		$appName = isset($data['type']) ? $data['type'] : null;
		$appDetails = self::get_app_by_name($appName);
		if (!$appDetails) {
			$appDetails = self::get_app_by_slug($appName);
		}
		$slug = $appDetails['slug'];
		$instanceType = isset($data['instanceType']) ? $data['instanceType'] : "list";
		$apps_instances = self::get_app_instances($slug);
		$instanceName = $slug . get_plugin_next_instance($apps_instances, $slug);
		put_instance_in_file($slug . "_Instances", $instanceName, true);
		$template_name = strtolower($instanceName) . '-post-' . $instanceType;
		$template_root = wp_get_theme()->get_template_directory() . self::$apps_rel;
		if (!file_exists($template_root . "/" . $template_name . ".php")) {
			if (file_exists($template_root . "/" . strtolower($data['type']) . '-post-' . $instanceType . '.php')) {
				$contents = file_get_contents($template_root . "/" . strtolower($data['type']) . '-post-' . $instanceType . '.php');
			} else {
				$contents = file_get_contents($template_root . "/" . strtolower($data['type']) . '-post-list.php');
			}
			// $contents = file_get_contents($template_root . "/" . strtolower($data['type']) . '-post-list.php');
			file_put_contents($template_root . "/" . $template_name . ".php", $contents);
		}
		if (!file_exists($template_root . "/" . $template_name . ".css")) {
			if (file_exists($template_root . "/" . strtolower($data['type']) . '-post-' . $instanceType . '.css')) {
				$contents = @file_get_contents($template_root . "/" . strtolower($data['type']) . '-post-' . $instanceType . '.css');
			} else {
				$contents = @file_get_contents($template_root . "/" . strtolower($data['type']) . '-post-list.css');
			}
			if ($contents) {
				$contents = str_replace("@@INSTANCE_ID@@", strtolower($instanceName), $contents);
			}
			file_put_contents($template_root . "/" . $template_name . ".css", $contents);
		}
		die(strtolower($instanceName));
		return;
	}
	static function add_styles($type, $instance) {
		$apps = self::get_instances();
		$app = @$apps[$type];
		if (!$app) {
			$app = self::get_app_by_slug($type);
		}
		$slug = $app['slug'];
		$instance_file = strtolower(preg_replace("/[^[:alnum:][:space:]]/ui", '', $instance));
	}
	static function handle_list_shortcode($atts) {
		$atts = shortcode_atts(array(
			'id' => 'cp_' . time(),
			'type' => 'xtd_testimonial',
			'posts' => '6',
			'category' => array(),
			'tag' => '',
			'page' => '1',
			'orderby' => "ID",
			'order' => "DESC",
		), $atts);
		if (!self::is_enabled($atts['type'])) {
			return "";
		}
		self::add_styles($atts['type'], $atts['id']);
		$template_name = strtolower($atts['id']) . '-post-list';
		$shortcode = "[custom_post_list ";
		foreach ($atts as $key => $value) {
			if (is_array($value)) {
				$value = implode(",", $value);
			}
			$shortcode .= $key . '="' . $value . '" ';
		}
		ob_start();
		$post = self::get_posts($atts);
		echo "<div id='" . $atts['id'] . "-row'>";
		$index = 1;
		if ($post->have_posts()) {
			while ($post->have_posts()): $post->the_post();
				$clear_str = cp_get_clear_classes($index, $atts);
				$extra = ' post-id="' . get_the_ID() . '"';
				echo '<div class="' . $atts['id'] . '-column' . $clear_str . '">';
				echo '<div class="' . $atts['id'] . '-post-content" ' . $extra . ' >';
				get_template_part(CloudPressApps::$apps_rel . "/" . $template_name);
				echo '</div>';
				echo '</div>';
				$index++;
			endwhile;
		} else {
			echo 'Sorry, no posts matched your criteria.';
		}
		
		self::enqueue_styles_in_preview($template_name);
		echo "</div>";
		$page = ob_get_contents();
		ob_end_clean();
		wp_reset_postdata();
		return $page;
	}
	static function load_template_part($template_name, $part_name = null) {
		ob_start();
		get_template_part($template_name, $part_name);
		$var = ob_get_contents();
		ob_end_clean();
		return $var;
	}
	static function handle_slider_shortcode($atts) {
		$atts = shortcode_atts(array(
			'id' => 'cp_' . time(),
			'type' => 'xtd_testimonial',
			'posts' => '6',
			'category' => array(),
			'tag' => '',
			'page' => '1',
			// simple slider params
			'speed' => '800',
			'delay' => '3000',
			'autoplay' => "true",
			'drag' => "true",
			"autoheight" => "true",
			"bullets" => "false",
			"transition" => "",
			'orderby' => "ID",
			'order' => "DESC",
		), $atts);
		if (!self::is_enabled($atts['type'])) {
			return "";
		}
		self::add_styles($atts['type'], $atts['id']);
		ob_start();
		$template_name = strtolower($atts['id']) . '-post-slider';
		$bullets_number = 0;
		self::enqueue_styles_in_preview($template_name);
		$post = self::get_posts($atts);
		?>
        <div id="<?php echo $atts['id']?>" data-bullets="<?php echo $atts['bullets']?>" data-slider-speed="<?php echo $atts['speed']?>" data-delay="<?php echo $atts['delay']?>" data-autoplay="<?php echo $atts['autoplay']?>" data-drag="<?php echo $atts['drag']?>" data-autoheight="<?php echo $atts['autoheight']?>" data-transition="<?php echo $atts['transition']?>">
            <?php echo do_shortcode('[xtd_simple_slider id="' . $atts['id'] . '"]');?>
            <div class="content-wrapper owl-carousel owl-theme">
                <div class="<?php echo $atts['id']?>_content">
                    <?php if ($post->have_posts()): ?>
                        <?php while ($post->have_posts()): $post->the_post()?>
			                            <?php
	$extra = ' post-id="' . get_the_ID() . '"';
			?>
			                            <div class="item <?php echo $atts['type']?>-post" <?php echo $extra?>>

			                                <?php get_template_part(self::$apps_rel . "/" . $template_name);?>

			                            </div>
			                            <?php $bullets_number += 1;?>
			                        <?php endwhile;?>
                    <?php else: ?>
                        <div class="item <?php echo $atts['type']?>-post" <?php echo $extra?>>
                            <p>Sorry, no posts matched your criteria.</p>
                        </div>
                    <?php endif;?>
                </div>
                <div class="owl-controls clickable" data-fake-pagination="true">
                    <div class="owl-pagination">
                        <?php for ($i = 0; $i < $bullets_number; $i++): ?>
                            <?php if ($i == 0): ?>
                                <div class="owl-page active"><span class=""></span></div>
                            <?php else: ?>
                                <div class="owl-page"><span class=""></span></div>
                            <?php endif;?>

                        <?php endfor;?>
                    </div>
                </div>

            </div>
            <div class="owl-prev" data-slider="<?php echo $atts['id']?>-prev">
                <div class=""></div>
            </div>
            <div class="owl-next" data-slider="<?php echo $atts['id']?>-next">
                <div class=""></div>
            </div>
        </div>

        <?php
$page = ob_get_contents();
		ob_end_clean();
		wp_reset_postdata();
		return $page;
	}
	static function handle_carousel_shortcode($atts) {
		$atts = shortcode_atts(array(
			'id' => 'cp_' . time(),
			'type' => 'xtd_testimonial',
			'posts' => '6',
			'category' => array(),
			'tag' => '',
			'page' => '1',
			// carousel-params
			'speed' => '800',
			'delay' => '3000',
			'drag' => "true",
			"transition" => "",
			'desktop' => "4",
			'tablet' => "2",
			'mobile' => "1",
			'orderby' => "ID",
			'order' => "DESC",
		), $atts);
		if (!self::is_enabled($atts['type'])) {
			return "";
		}
		self::add_styles($atts['type'], $atts['id']);
		$template_name = strtolower($atts['id']) . '-post-carousel';
		$bullets_number = 0;
		// xtd_add_styles(array(CloudPressApps::$apps_rel . "/" . $template_name. ".css");
		ob_start();
		$post = self::get_posts($atts);
		self::enqueue_styles_in_preview($template_name);
		?>
        <div id="<?php echo $atts['id']?>" data-slider-speed="<?php echo $atts['speed']?>" data-slider-items-desktop="<?php echo $atts['desktop']?>" data-slider-items-tablet="<?php echo $atts['tablet']?>" data-slider-items-mobile="<?php echo $atts['mobile']?>" data-delay="<?php echo $atts['delay']?>" data-transition="<?php echo $atts['transition']?>" data-drag="<?php echo $atts['drag']?>">
            <?php echo do_shortcode('[xtd_simple_carousel id="' . $atts['id'] . '"]');?>
            <!-- <div class="content-wrapper owl-carousel owl-theme"> -->
            <div class="<?php echo $atts['id']?>_content">
                <?php if ($post->have_posts()): ?>
                    <?php while ($post->have_posts()): $post->the_post()?>
			                        <?php
	$extra = ' post-id="' . get_the_ID() . '"';
			?>
			                        <div class="item_wrapper">
			                            <div class="item <?php echo $atts['type']?>-post" <?php echo $extra?>>

			                                <?php get_template_part(self::$apps_rel . "/" . $template_name);?>

			                            </div>
			                        </div>
			                    <?php endwhile;?>
                <?php else: ?>
                    <div class="item <?php echo $atts['type']?>-post" <?php echo $extra?>>
                        <p>Sorry, no posts matched your criteria.</p>
                    </div>
                <?php endif;?>
            </div>
            <!-- </div> -->
            <div class="owl-prev" data-slider="<?php echo $atts['id']?>-prev">
                <div class=""></div>
            </div>
            <div class="owl-next" data-slider="<?php echo $atts['id']?>-next">
                <div class=""></div>
            </div>
        </div>

        <?php
$page = ob_get_contents();
		ob_end_clean();
		wp_reset_postdata();
		return $page;
	}
	static function render_post_custom_fields($meta, $slug, $field_name) {
		$name_parts = array();
		$name = $field_name;
		$app = self::get_app_by_slug($slug);
		$fields = $app['fields'];
		$type = $fields[$field_name];
		preg_match_all("/[A-Za-z0-9]+/", $name, $name_parts);
		$name_parts = implode("_", $name_parts[0]);
		$class_name = "meta_tag";
		if (count($name_parts) > 0) {
			$class_name .= "_" . $name_parts;
			$class_name = $slug . "_" . $class_name;
		}
		if (count($meta) == 0) {
			if (xtd_in_editor()) {
				// return "Meta key '$name' does not exists in post";
				$toShowValue = self::$customFieldsTypes[$type]['default'];
				$toShowValue = str_replace("%FIELD_NAME%", $field_name, $toShowValue);
				return self::handle_custom_field_type($toShowValue, $type, "p", $class_name);
			}
		} elseif (count($meta) == 1) {
			return self::handle_custom_field_type($meta[0], $type, "p", $class_name);
		}
	}
	static function get_posts($atts) {
		$atts = shortcode_atts(array(
			'id' => 'cp_' . time(),
			'type' => 'xtd_testimonial',
			'posts' => '6',
			'category' => array(),
			'tag' => '',
			'page' => '1',
			'template' => false,
			'orderby' => "ID",
			'order' => "DESC",
			"include_children" => true,
		), $atts);
		$app = self::get_app_by_name($atts['type']);
		if (!$app) {
			$app = self::get_app_by_slug($atts['type']);
		}
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
			'post_type' => $app['slug'],
			'post_status' => 'publish',
			'posts_per_page' => $atts['posts'],
			'paged' => $atts['page'],
		);
		if (!empty($atts['category'])) {
			$post_args['tax_query'] = array(
				array(
					'taxonomy' => $app['slug'] . '_category',
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
			$post_args['tax_query'][] = array(
				'taxonomy' => $app['slug'] . '_tag',
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
		// var_dump($order_args); die();
		$post_args = array_merge($post_args, $order_args);
		$post = new WP_Query($post_args);
		return $post;
	}
	static function toggle_enable($name, $enabled) {
		$instances = self::get_instances();
		$instances[$name]['enabled'] = $enabled;
		put_instance_in_file("CloudPressApps", $name, $instances[$name]);
	}
	static function is_enabled($name) {
		$instances = self::get_instances();
		if (isset($instances[$name]['enabled']) && $instances[$name]['enabled']) {
			return true;
		};
		$appDetails = self::get_app_by_slug($name);
		if ($appDetails && $appDetails['enabled']) {
			return true;
		}
		return false;
	}
	static function handle_custom_field_type($content, $type, $wrapper, $class) {
		if (is_callable(array(self, self::$customFieldsTypes[$type]['handler']))) {
			return call_user_func(array(self, self::$customFieldsTypes[$type]['handler']), $content, $wrapper, $class);
		} else {
			return "Custom field not found.";
		}
	}
	static function handle_custom_field_text($content, $wrapper = "", $class = "", $atts = "") {
		// return "<$wrapper $atts class='$class'>".  $content ."</$wrapper>";
		return $content;
	}
	static function handle_custom_field_link($content, $wrapper = "", $class = "", $atts = "") {
		$content = str_replace("@@SITE_URL@@", site_url(), $content);
		$url = $content;
		if (strpos($url, "http") !== 0) {
			$url = "http://" . $url;
		}
		return $content;
		// if($wrapper == "li"){
		//  return "<$wrapper $atts class='$class'><a target='_blank' href='$url'/>" . $content . "</a></$wrapper>";
		// } else{
		//  return '<a '.$atts.' class="'.$class.'" target="_blank" href="'.$url.'"/>' . $content . "</a>";
		// }
	}
	static function handle_custom_field_email($content, $wrapper = "", $class = "", $atts = "") {
		$content = "mailto:" . $content;
		return $content;
	}
	static function handle_custom_field_image($content, $wrapper = "", $class = "", $atts = "") {
		$content = str_replace("@@SITE_URL@@", site_url(), $content);
		if (strpos($content, "http") !== 0) {
			$content = "http://" . $content;
		}
		if ($wrapper == "img") {
			return '<img style="max-width: 480px;" class="' . $class . '"  ' . $atts . ' src="' . $content . '"/>';
		}
		return $content;
		// if($wrapper == "li"){
		//  return "<$wrapper class='$class'><a target='_blank' href='$url'/>" . '<img '.$atts.' src="'. $content.'"/>' . "</a></$wrapper>";
		// } else{
		//  return '<img class="'.$class.'"  '.$atts.' src="'. $content.'"/>';
		// }
	}
	static function handle_custom_field_bgimg($content, $wrapper = "", $class = "") {
		$content = str_replace("@@SITE_URL@@", site_url(), $content);
		if (strpos($content, "http") !== 0) {
			$content = "http://" . $content;
		}
		if ($wrapper == "li") {
			return "<$wrapper class='$class'><a target='_blank' href='$url'/>" . '<div ' . $atts . ' style="background-image:url(' . $content . ')"/><div>' . "</a></$wrapper>";
		} else {
			return '<div class="' . $class . '" ' . $atts . ' style="background-image:url(' . $content . ')"/><div>';
		}
	}
	static function handle_custom_field_video($video, $wrapper = "", $class = "") {
		ob_start();
		echo do_shortcode('[xtd_video id="' . $class . '" src="' . $video . '"]');
		$content = ob_get_contents();
		ob_end_clean();
		if ($wrapper == "li") {
			return "<$wrapper class='$class'>" . $content . "</$wrapper>";
		} else {
			return '<div class="' . $class . '">' . $content . '</div>';
		}
		return $content;
	}
	static function change_field_value() {
		add_action('init', array(__CLASS__, "change_field_value_action"));
	}
	static function change_field_value_action() {
		if (!wp_verify_nonce($_GET['_wpnonce'], 'change_field_value')) {
			die("NO DIRECT ACCESS ALLOWED");
		}
		$data = $_REQUEST;
		$toReturn = "";
		$value = call_user_func(array(self, self::$customFieldsTypes[$data['type']]['parser']), $data['value']);
		update_post_meta($data['id'], $data['field'], $value);
		switch ($data['type']) {
		case 'video':
			$toReturn = do_shortcode('[xtd_video id="preview-video" src="' . $value . '"]');
			break;
		default:
			$toReturn = $value;
			break;
		}
		die($toReturn);
	}
	static function parse_custom_field_text($value) {
		return $value;
	}
	static function parse_custom_field_link($value) {
		$value = str_replace(site_url(), "@@SITE_URL@@", $value);
		return $value;
	}
	static function parse_custom_field_email($value) {
		$value = trim($value);
		return $value;
	}
	static function parse_custom_field_image($value) {
		$value = str_replace(site_url(), "@@SITE_URL@@", $value);
		return $value;
	}
	static function parse_custom_field_bgimg($value) {
		$value = str_replace(site_url(), "@@SITE_URL@@", $value);
		return $value;
	}
	static function parse_custom_field_video($value) {
		return $value;
	}
	// STRIPPED POPUP FUNCTIONS
	public static function remove_menu_popup() {
		global $menu;
		$menu = array();
	}
	public static function remove_options_popup() {
		remove_meta_box('commentstatusdiv', $post->post_type, 'normal');
		remove_meta_box('commentsdiv', $post->post_type, 'normal');
		return false;
	}
	public static function admin_bar_render_popup() {
		global $wp_admin_bar;
		if ($wp_admin_bar->get_nodes()) {
			foreach ($wp_admin_bar->get_nodes() as $key => $value) {
				$wp_admin_bar->remove_menu($key);
			}
		}
		echo ' <div class="popup-loading"> <div class="cp-loading-spinner"></div> </div>';
	}
	public static function add_style_popup() {
		?>
       <style type="text/css">

                div#adminmenuback, div#adminmenuwrap, div#wpadminbar, #wpfooter,
                div#minor-publishing, div#delete-action, #edit-slug-box {
                    display: none!important;
                }
                div#wpbody {
                    margin-top: -38px;
                }
                div#wpcontent {
                     margin-left: 0px!important;
                }
                a.add-new-h2,
                #submitdiv,
                .tablenav,
                .search-box,
                .subsubsub,
                .row-actions {
                    display: none!important;
                }
                .check-column{
                    visibility:hidden;
                    width:0px!important;
                }
                .popup-loading {
                    position: fixed;
                    height: 100%;
                    width: 100%;
                    z-index: 10000;
                    background-color: rgba(255,255,255,0.8);
                    top: 0px;
                    left: 0px;
                }
                .cp-loading-spinner {
                    background: url('<?php echo site_url()?>/wp-admin/images/wpspin_light.gif') no-repeat;
                    background-size: 16px 16px;
                    opacity: 1;
                    width: 16px;
                    height: 16px;
                    margin: -8px 0px 0px -8px;
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    display: block;
                    visibility: visible;
                }
            </style>
      <?php
}
	public static function add_scripts_popup() {
		?>
            <script type="text/javascript">
                <?php echo file_get_contents(dirname(__FILE__) . "/assets/editor-popup-scripts.js");?>
            </script>
        <?php
	}
	static function enqueue_styles_in_preview($template_name) {
		// if(function_exists("xtd_in_editor") && xtd_in_editor()){
		//     wp_register_style($template_name . '_css', get_bloginfo('template_directory') . "/apps/$template_name.css", false);
		//     xtd_add_styles(array($template_name . '_css'));
		//     $styles = xtd_create_preview_styles(array($template_name . '_css'));
		//     echo  $styles;
		// }
	}
	static function export($plugin) {
		$toReturn = array(
			"source" => $plugin,
			"shortcode" => $plugin['shortcode'],
			"templates" => array($plugin['html']),
		);
		return $toReturn;
	}
	static function import($plugin_data) {
		$templateContent = $plugin_data['templates'][0];
		$templateContent = stripcslashes($templateContent);
		$templateContent = str_replace("\\\"", "\"", $templateContent);
		$templateContent = str_replace("\\'", "'", $templateContent);
		$templateContent = html_entity_decode($templateContent);
		$templateContent = preg_replace('/(\[[\w\W]+?\])/i', "<?php echo do_shortcode('$0'); ?>", $templateContent);
		$instanceID = $plugin_data["shortcode"]["attrs"]["id"];
		$instanceType = str_replace("cp_app_", "", $plugin_data["shortcode"]["name"]);
		$appName = strtolower($plugin_data["shortcode"]["attrs"]["type"]);
		$appDetails = self::get_app_by_name($appName);
		if (!$appDetails) {
			$appDetails = self::get_app_by_slug($appName);
		}
		$slug = $appDetails['slug'];
		$apps_instances = self::get_app_instances($slug);
		$instanceName = $slug . get_plugin_next_instance($apps_instances, $slug);
		put_instance_in_file($slug . "_Instances", $instanceName, true);
		//file_put_contents(self::apps_dir() . "/" . $instanceName . "-post-".$instanceType.".php", $templateContent);
		//touch(self::apps_dir() . "/" . $instanceName . "-post-list.css");
		$upload_obj = wp_upload_dir();
		$upload_dir = $upload_obj['basedir'];
		$data = array();
		$data['replace'] = array();
		$data['variables'][$instanceID] = $instanceName;
		$data['variables']['%instance%'] = $instanceName;
		$data['variables']["%theme%"] = get_template_directory();
		$data['variables']["%uploads%"] = $upload_dir;
		$data['templates'] = array(array("content" => $templateContent, "file" => self::apps_dir() . "/" . $instanceName . "-post-" . $instanceType . ".php"));
		return $data;
	}
}
CloudPressApps::init();
