<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: Layouts drop down menu
	Plugin URI: 
	Description: Layouts drop down menu
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [nav_menu]


if(!function_exists('get_plugin_data_folder')) {
	require_once WPMU_PLUGIN_DIR.'/xtdpluginbase/xtdpluginbase.php';
}

class XtdDropDownMenu {
	static $id ="";
	static $location = "";
	static $unique="";
	static $templateFile="";
	static $url=false;
	static $styles = array();
	static $scripts = array();
	
	static $instancesFile ;
	static $instances;
	static $defaultInstanceSettings;
	static $instanceNameStart = 'drop_menu';
	static $instanceKey = "xtd_drop_down_menu";

	static $actionParameter = 'xtd_dropdownmenu_action';
 	
	static function assets($instanceName){
		return array();
	}

	static function export($plugin_data){
		$atts = $plugin_data['shortcode']['attrs'];

		$instanceName = 'drop_' . $atts['id'];
		$assets = array(
			'%theme%/'.$instanceName.'.css',
			'%theme%/'.$instanceName.'-ie.css',
		);

		$files = array();
		$data = array();

		$files = array_merge($files, $assets);
		$instances = get_instances_as_array(self::$instanceKey);
		$data = $instances[$instanceName];

		$styles = array();
		array_push($styles, get_template_directory_uri().'/'.$instanceName.'.css');
		array_push($styles, get_template_directory_uri().'/'.$instanceName.'-ie.css');

		return array(
			"data" => array(
				"assets" => $assets,
				"instanceData" => $data
			),
			"files" => $files,
			"styles" => $styles
		);
	}

	static function import($plugin_data) {
		$menuid = $plugin_data["shortcode"]["attrs"]["id"];
		$assets = $plugin_data['assets'];

		$files = array();
		$instanceData = array();
		if (isset($plugin_data["instanceData"])) {
			$instanceData = $plugin_data["instanceData"];
		}

		$upload_obj = wp_upload_dir();
		$instanceName = str_replace("drop_", "", self::look_for_instance("", $instanceData) );
		$upload_dir = $upload_obj['basedir'];

		$data = array();
		$data['replace'] = array();
		$data['variables'][$menuid] = $instanceName;
		$data['variables']['%instance%'] = $instanceName;
		$data['variables']["%theme%"] = get_template_directory();
		$data['variables']["%uploads%"] = $upload_dir;

		return $data;
	}


	static function init(){
		self::set_defaults();
		CPPluginBase::init(get_called_class());

		add_shortcode( 'xtd_drop_down_menu', array(__CLASS__,'handle_shortcode') );
		add_action('init', array(__CLASS__, 'register_assets'));
		add_action('wp_footer', array(__CLASS__, 'display_assets'));
		
	}
	
	static function handle_shortcode($atts){
		$instanceName = $atts['id'];
		
		$atts = shortcode_atts( array(
			'id' => "",
			'inherit' => "",
			'location' => "main-menu"
    ), $atts );

		self::$id = $atts['id'];
		self::$location = (!empty($atts['location'])) ?$atts['location'] : self::$id;
		self::$unique = 'drop_' . self::$id ;
		self::$url = get_permalink();

		self::look_for_instance(self::$unique);

		if(!isset($atts['inherit'])){
			$atts['inherit']= "";
		}
		
		wp_register_style(self::$unique, get_template_directory_uri().'/'.self::$unique.'.css');
		array_push(self::$styles, self::$unique);
		xtd_add_styles(self::$styles);

		$locations = get_nav_menu_locations();

		if (isset($locations[self::$location])) {
			$menu = wp_get_nav_menu_object($locations[self::$location]);
		}
		
		if (!$menu) {
			$menu = wp_get_nav_menu_object(self::$id);
		}

		if (!$menu) {
			$menu = wp_get_nav_menu_object('main-menu');
		}

		$menuTree = '';

		if(function_exists('xtd_in_editor') && xtd_in_editor()){
			$menuTree = self::xtd_get_menu_tree($menu);
			$menuTree = ' data-tree="'. htmlentities(json_encode($menuTree)) .'"';
		}

		if ($menu) {
			$id = $menu->term_id;
		}

		if(empty($id)) {
			$id = self::$id;
		}
		
		global $xtd_decorate_files;
		if ($xtd_decorate_files) {
			global $xtd_inside_api;
			$old_xtd_inside_api = $xtd_inside_api;
			$xtd_inside_api = true;
		}
		try {
		$styles = xtd_create_preview_styles(self::$styles);
		

		$items = wp_get_nav_menu_items( $id, array(  'order'  => 'ASC','orderby' => 'menu_item_parent', ) ); 
		if(!$items){
				// fake an item if menu doesn't exists
				$menu = '<div id="'.self::$unique.'" >' . 
				'<ul id="'.self::$unique.'"><li href="#"><a>This menu no longer exists</a></li>'.  
				'</ul>';
		} else {
				$items = self::simplifyMenu($items); 

				$menu = '<div id="'.self::$unique.'_container"  class="fm2_'.self::$unique.'_container" >'
						.'<ul id="'.self::$unique.'" '.$menuTree.'  class="drop_down_menu_ul fm2_'.self::$unique.'">{{menu_items}}'
						.'</ul>';
				$menuItems = "";

				foreach ($items as $item) {
					if($item['parent']==0){
						$menuItems .= self::makeMenuItem($items,$item);
					}
				}

				$menu = str_replace("{{menu_items}}", $menuItems, $menu);
		}
		$menuCss = get_stylesheet_directory() .'/' . self::$unique . '.css';
		

		if(!file_exists($menuCss)){
			self::individualizeCssFile(dirname(__FILE__) . '/assets/default/drop_menu_default.css', self::$unique, false);
		}
		$mobileLabel = "Menu";
		if(isset(self::$instances[self::$unique]["options"]["mobileLabel"])) {
			$mobileLabel = self::$instances[self::$unique]["options"]["mobileLabel"];
			}
		} catch (Exception $e) {
		}
		if ($xtd_decorate_files) {
			$xtd_inside_api = $old_xtd_inside_api;
		}

		$menu .= $styles .  '</div>';
		return "<div class='".self::$unique."_menu_wrapper'>". $menu .
					'<a class="fm2_mobile_button" style="display:none;" id="fm2_'.self::$unique.'_mobile_button"><span class="caption">' . $mobileLabel . '</span></a>'.
				"</div>";
	}


	static  function xtd_get_menu_tree($menu) {
	  $ret = array();
	  $menu_items = wp_get_nav_menu_items($menu->term_id);
	  foreach ($menu_items as $item) {
	    if ($item->ID == $item->object_id) {
	      $ret[$item->ID] = array(
	        'link' => $item->url,
	        'page' => $item->ID,
	        'order' => $item->menu_order,
	        'title' => $item->title,
	        'parent' => $item->menu_item_parent,
	        'url' => $item->url
	      );
	    } else {
	      $item_id = get_post_meta($item->ID, '_menu_item_object_id', true);
	      $page = get_page($item_id);
	      $link = get_page_link($item_id);

	      $ret[$item->ID] = array(
	        'link' => $link,
	        'page' => $item_id,
	        'order' => $item->menu_order,
	        'title' => $page->post_title,
	        'parent' => $item->menu_item_parent,
	        'url' => $item->url
	      );
	      if ($item->title) {
	        $ret[$item->ID]['title'] = $item->title;
	      }
	    }
	  }
	  
	  foreach ($ret as $key => $item) {
	    $parentID = $item['parent'];
	    if ($parentID!=0) {
	      $found = false;
	      foreach ($ret as $key2 => $item2) {
	        if ($ret[$key2]['page']==$parentID)
	          $found=true;
	      }
	      if (!$found) {
	        unset($ret[$key]);
	      }
	    }
	  }

	  return $ret;
	}

	static function individualizeCssFile($source='', $ID='', $relative=false, $skin='default') {

		if(!$source && isset($_REQUEST['source'])) {
			$source = $_REQUEST['source'];
		}
		if(!$ID && isset($_REQUEST['ID'])) {
			$ID = $_REQUEST['ID'];
		}
		if(isset($_REQUEST['relative'])) {
			$relative = $_REQUEST['relative'];
		}
		if(isset($_REQUEST['skin'])) {
			$skin = $_REQUEST['skin'];
		}


		$override = true;

		if(isset($_GET['no_override'])){
			$override = false;
		}

		$menuCss = get_stylesheet_directory() .'/' . $ID . '.css';
		
		
		if($source ==  "xtd_untouched_". $ID . ".css"){
			$source = dirname(__FILE__). "/" . $source;
		}


		$content = file_get_contents($source);

		// delete file get from external assets folder
		if(strpos($source, "xtd_untouched_". $ID ) !== false){
			unlink($source);
		}
		
		$content = str_replace('#menuID_mobile_button', "#fm2_".$ID."_mobile_button", $content);
		$content = str_replace('#menuID_mobile-overlay', ".fm2_".$ID."_mobile-overlay", $content);
		$content = str_replace('#menuID_jq_menu_back', "#fm2_".$ID."_jq_menu_back", $content);

		if($relative === false) {
			$content = str_replace('url("', 'url("../../mu-plugins/xtddropdownmenu/assets/' . $skin . '/', $content);
			$content = str_replace("url('", "url('../../mu-plugins/xtddropdownmenu/assets/" . $skin . '/', $content);
		} else {
			$content = str_replace('url("', 'url("'.$relative, $content);
			$content = str_replace("url('", "url('".$relative, $content);
		}

		$content = str_replace('menuID', $ID, $content);

		if($override){
			file_put_contents($menuCss, $content);
		} else {
			echo $content;
			die();
		}

	}

	
	static function makeMenuItem($items,$item){

		$item_url = self::parseURL($item['url']);

		$itemDetails = "";
		if(function_exists("xtd_in_editor") && xtd_in_editor()){
			$itemDetails = 'data-item-details="'. htmlentities(json_encode($item)) .'"';
		}

		$result = '<li data-item-id="' . $item['id'] . '" ' .$itemDetails.'><a {{class_type}} href="'.$item_url.'" target="{{target}}"><{{tag}} class="{{has_submenu}}">'.$item['title'].'</{{tag}}></a>{{submenus}}</li>';

		if(self::$url === false){
			self::$url = $item['url'];
		}

		$submenus = array();
		foreach ($items as $submenu) {
			if($submenu['parent'] == $item['id']){
				$submenus[] = $submenu;
			}
		}
		if(count($submenus)){
			$submenuItems = "<ul>";
			foreach ($submenus as $submenu) {
				$submenuItems .=self::makeMenuItem($items,$submenu);
			}
			$submenuItems .= "</ul>";
			$result = str_replace('{{submenus}}', $submenuItems, $result);
			$result = str_replace('{{class_type}}', '', $result);
			$result = str_replace('{{has_submenu}}', 'branch', $result);
			$result = str_replace('{{tag}}', 'span', $result);
			$target= get_post_meta($item['id'],'_menu_item_target',true);
			$result = str_replace('{{target}}', $target, $result);
		} else {
			if($item['url'] == self::$url){
				$result = str_replace('{{class_type}}', 'class="sel"', $result);
			} else {
				$result = str_replace('{{class_type}}', '', $result);
			}
			$result = str_replace('{{has_submenu}}', 'leaf', $result);
			$result = str_replace('{{submenus}}', '', $result);
			$result = str_replace('{{tag}}', 'font', $result);
			$target= get_post_meta($item['id'],'_menu_item_target',true);
			$result = str_replace('{{target}}', $target, $result);
		}
		return $result;
	}
	
	static function parseURL($url){
		if(strpos($url, "tag_link_site_url") !== false){
			$url = str_replace("[tag_link_site_url]", "tag_link_site_url", $url);
			$exploded = explode("tag_link_site_url", $url);
			$root = do_shortcode("[tag_link_site_url]");
			return $root . $exploded[1];
		}

		return $url;
	}
	
	static function simplifyMenu($items){
		$result = array();

		if($items)
			foreach ($items as $item) {
				$result[] = array(
						"id" => $item->ID,
						"ID" => $item->ID,
						"title" => $item->title,
						"order" => $item->menu_order,
						"url" => $item->url,
						"parent" => $item->menu_item_parent,
						"pageID"=>  get_post_meta($item->ID, '_menu_item_object_id', true),
						"target"=>  get_post_meta($item->ID, '_menu_item_target', true),
					);
			}

		return $result;
	}
	
	static function set_defaults(){
		self:: $instancesFile  			 =    get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array(
			'effectSub'=>array(
				"name"=>"slide",
				"direction"=>"up",
				"duration"=>250,
				"easing"=>"swing",
				"useFade"=>false,
			),
			'effectRest'=>array(
				"name"=>"slide",
				"direction"=>"left",
				"duration"=>250,
				"easing"=>"swing",
				"useFade"=>false,
			),
			'effectSubTwo'=>array(
				"name"=>"none",
				"direction"=>"left",
				"duration"=>250,
				"easing"=>"swing",
				"useFade"=>false,
			),
			'options'=>array(
				"preset"=>"fixed",
				"enableTablet"=>true,
				"enableMobile"=>true,
				"mobileMaxWidth"=>640,
				"tabletMaxWidth"=>1023,
				"tabletCloseBtnLabel"=>"Close",
				"tabletCloseBtnEnable"=>true,
			),
			'skin' => 'default'
		);
	}
	

	
	static function new_instance(){
		self::set_defaults();

		$instanceName = isset($_REQUEST['id'])?$_REQUEST['id']:null;
		$instanceName = self::look_for_instance($instanceName);

		die($instanceName);
		return;
	}

	static function delete_instance(){
		self::set_defaults();

		$instanceName = isset($_REQUEST['id'])?$_REQUEST['id']:null;
		self::remove_instance($instanceName);

		die($instanceName);
		return;
	}
	
	

	static function custom_scripts(){
		
	}

	static function register_assets(){
		wp_register_script('extendJQuery',  get_template_directory_uri() .  '/js/extendjQuery.js', array('jquery'));
		wp_register_script('DropDownMenu', plugins_url('', __FILE__) .  '/assets/drop_menu_selection.js', array('extendJQuery'));
	}

	static function display_assets() {
		
		self::get_instances_as_array(self::$instanceKey);
		
		if( count(self::$instances) ) {
			wp_print_scripts('extendJQuery');
			wp_print_scripts('DropDownMenu');
			?>
				<script type="text/javascript">

					(function(jQuery) {

					jQuery(document).ready(function(){
						var menuSettings = <?php echo json_encode(self::$instances); ?>;
						var instanceName;
						for (var key in menuSettings) {
							if(menuSettings.hasOwnProperty(key) && key!="xtd_drop_down_menu") {
								instanceSettings = menuSettings[key];
								instanceName = key;
								registerFlexiCSSMenu(instanceName, instanceSettings);
							}
						}
					});

					}(menus_jQuery));
				</script>
			<?php
		}
	}

	static function remove_instance($instanceName){
		//try to get all instances
		self::get_instances_as_array(self::$instanceKey);

		if(isset(self::$instances[$instanceName])) {
			remove_instance_from_file(self::$instanceKey, $instanceName);
		}
	}
	
	static function look_for_instance($instanceName, $instanceData = array()){ 

		//try to get all instances
		self::get_instances_as_array(self::$instanceKey);


		if(!$instanceName){

			$instanceName = self::$instanceNameStart . get_plugin_next_instance(self::$instances, self::$instanceNameStart);
		}

		// try to get current instance settings or set defaults
		if(!isset(self::$instances[$instanceName])){
			self::put_instance_in_file(self::$instanceKey, $instanceName, $instanceData);
		}

		return $instanceName;
	}


	static function get_instances_as_array($pluginName=""){
		self::$instances = get_instances_as_array($pluginName);
		if( count(self::$instances) ) {
			$instancesNames = array_keys(self::$instances);
			$mediaObj = get_media_queries_object();
			foreach($instancesNames as $instanceName) {
				self::$instances[$instanceName]["options"]["mobileMaxWidth"] = $mediaObj["tablet"]["minWidth"];
				self::$instances[$instanceName]["options"]["tabletMaxWidth"] = $mediaObj["desktop"]["minWidth"];
			}
		}
	}

	static function put_instance_in_file($pluginName, $instanceName, $instanceData){
		if (!empty($instanceData)) {
			self::$instances[$instanceName] = $instanceData;
		} else {
			self::$instances[$instanceName] = self::$defaultInstanceSettings;
		}
		put_instance_in_file($pluginName, $instanceName, self::$instances[$instanceName]);
	}
}

XtdDropDownMenu::init();