<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: Layouts nav menu
	Plugin URI: 
	Description: Layouts nav menu
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [nav_menu]

if(!function_exists('get_plugin_data_folder')) {
	require_once WPMU_PLUGIN_DIR.'/xtdpluginbase/xtdpluginbase.php';
}

class XtdOneLevelMenu{
	static $id ="";
	static $location = "";
	static $unique="";
	static $templateFile="";
	static $url=false;
	static $styles = array();
	static $scripts = array();

	static $instancesFile;
	static $instances;
	static $defaultInstanceSettings;
	static $instanceNameStart = 'menu_menu';
	static $instanceKey = "xtd_one_level_nav_menu";

	static $actionParameter = 'xtd_one_level_menu_action';
 
	static function init(){
		self::set_defaults();
		
		CPPluginBase::init(get_called_class());
		add_shortcode( 'xtd_one_level_nav_menu', array(__CLASS__,'handle_shortcode') );

	}



	


	static function export($plugin_data){
		$atts = $plugin_data['shortcode']['attrs'];

		$instanceName = 'menu_' . $atts['id'];
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

		$instanceName = str_replace("menu_", "", self::look_for_instance("", $instanceData) );
		$upload_obj = wp_upload_dir();
		$upload_dir = $upload_obj['basedir'];

		$data = array();
		$data['replace'] = array();
		$data['variables'][$menuid] = $instanceName;
		$data['variables']['%instance%'] = $instanceName;
		$data['variables']["%theme%"] = get_template_directory();
		$data['variables']["%uploads%"] = $upload_dir;

		return $data;
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

	static function handle_shortcode($atts){
		self::$id =$atts['id'];


		$atts = shortcode_atts( array(
			'id' => "",
			'skin' => "",
			'inherit' => "",
			'location' => "secondary-menu",
			'type'=>'vertical',
    		), $atts );

		self::$location = $atts['location'];
		
		self::$unique = 'menu_' . self::$id ;


		$instanceName = 'menu_' . $atts['id'];
		self::look_for_instance($instanceName);
		
		self::$url = get_permalink();


		if(!isset($atts['inherit']) || !$atts['inherit']){
			$atts['inherit'] = false;
		} else {
			self::$unique = 'menu_' . $atts['inherit'];
		}
		
		wp_register_style(self::$unique, get_template_directory_uri().'/'.self::$unique.'.css');
		array_push(self::$styles, self::$unique);
		xtd_add_styles(self::$styles);

		$styles = xtd_create_preview_styles(self::$styles);

		$menu = wp_get_nav_menu_object(self::$location);
		if (!$menu) {
			$menu = wp_get_nav_menu_object(self::$id);
		}
		
		if (!$menu) {
			$menu = wp_get_nav_menu_object('secondary-menu');
		}

		if ($menu) {
			$id = $menu->term_id;
		}


		if(empty($id)) {
			$id = self::$id;
		}

		$menuTree = '';

		if(function_exists('xtd_in_editor') && xtd_in_editor()){
			$menuTree = self::xtd_get_menu_tree($menu);
			$menuTree = ' data-tree="'. htmlentities(json_encode($menuTree)) .'"';
		}


		$items = wp_get_nav_menu_items( $id, array(  'order'  => 'ASC','orderby' => 'menu_item_parent', ) ); 
		if(!$items){
				// fake an item if menu doesn't exists
				$menu = '<div id="'.self::$unique.'" '.$menuTree.' >' . 
				'<ul id="'.self::$unique.'"><li href="#"><a>This menu no longer exists</a></li>'.  
				'</ul>';
		} else {
				$items = self::simplifyMenu($items); 

				$menu = '<div id="'.self::$unique.'" >'
						.'<ul id="'.self::$unique.'_list">{{menu_items}}'
						.'</ul>';
				$menuItems = "";

				foreach ($items as $item) {
					if($item['parent']==0){
						$menuItems .= self::makeMenuItem($items,$item);
					}
				}

				$menu = str_replace("{{menu_items}}", $menuItems, $menu);
		}

		global $xtd_decorate_files;
		if ($xtd_decorate_files) {
			global $xtd_inside_api;
			$old_xtd_inside_api = $xtd_inside_api;
			$xtd_inside_api = true;
		}
		try {
		$menuCss = get_stylesheet_directory() .'/' . self::$unique . '.css';
		} catch (Exception $e) {
		}
		if ($xtd_decorate_files) {
			$xtd_inside_api = $old_xtd_inside_api;
		}

		if(!file_exists($menuCss)){
			if($atts['type'] == 'vertical'){
				$content = file_get_contents(dirname(__FILE__) . '/assets/menu_vertical_default.css');
			} else {
				$content = file_get_contents(dirname(__FILE__) . '/assets/menu_default.css');
			}
			
			$content = str_replace('menuID', self::$unique, $content);
			file_put_contents($menuCss, $content);
		}

		$menu .= '<select>
				      <option value="" selected="">MENU</option>
				   </select>';
		$menu .= $styles;
		$menu .= '<script type="text/javascript" src="'.plugins_url('', __FILE__) .  '/assets/menu_selection.js'.'"></script>';
		return $menu .  '</div>' ;
	}

	
	static function makeMenuItem($items,$item){

		$item_url = self::parseURL($item['url']);

		if(function_exists("xtd_in_editor") && xtd_in_editor()){
			$itemDetails = 'data-item-details="'. htmlentities(json_encode($item)). '"';
		}

		$result = '<li data-item-id="' . $item['id'] . '" ' .$itemDetails.' ><a {{class_type}} href="'.$item_url.'" target="{{target}}">'.$item['title'].'</a>{{submenus}}</li>';
		$target= get_post_meta($item['id'],'_menu_item_target',true);
		$result = str_replace('{{target}}', $target, $result);

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
			
		} else {
			if($item['url'] == self::$url){
				$result = str_replace('{{class_type}}', 'class="selected"', $result);
			} else {
				$result = str_replace('{{class_type}}', '', $result);
			}
			$result = str_replace('{{submenus}}', '', $result);
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
				if($item->menu_item_parent == 0){
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
			}

		return $result;
	}


	static function set_defaults(){
		self:: $instancesFile  			 =    get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =   array();
	}

	static function new_instance(){
		

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

XtdOneLevelMenu::init();