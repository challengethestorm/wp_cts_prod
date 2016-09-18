<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: XTD LINK LIGHTBOX
	Plugin URI: 
	Description: Layouts simple slider
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [xtd_simple_slider]

if(!function_exists('get_plugin_data_folder')) {
	require_once WPMU_PLUGIN_DIR.'/xtdpluginbase/xtdpluginbase.php';
}

class XtdLinkLightbox{
	static $instancesFile ;
	static $instances;
	static $defaultInstanceSettings;
	static $instanceNameStart = "LinkLightbox";
	static $instanceKey = "xtd_link_lightbox";
	static $actionParameter = 'xtd_link_lightbox_action';



	static function export($plugin_data){
		$instanceName = $plugin_data['instanceID'];
		$data = array();
		$instances = get_instances_as_array(self::$instanceKey);
		$data = $instances[$instanceName];

		$styles = array();
		$files = array();
		$assets = array();
		
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
		$instanceData = array();
		if (isset($plugin_data["instanceData"])) {
			$instanceData = $plugin_data["instanceData"];
		}

		$instanceName = self::look_for_instance("", $instanceData);
	
		$data = array();
		$data['replace'] = array();
		$data['variables'][$plugin_data['source']['instanceID']] = $instanceName;
		$data['variables']['%instance%'] = $instanceName;

		return $data;
	}

	
	// ****************************************** START & DEFAULTS *********************************

	static function set_defaults(){
		self:: $instancesFile  			 =   get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array(
			'exists'=>'true'
		);
	}

	static function init(){
			CPPluginBase::init(get_called_class());
			// add_shortcode( 'xtd_reveal_fx', array(__CLASS__,'handle_shortcode') );
			
			add_action('init', array(__CLASS__, 'register_assets'));
			add_action('wp_head', array(__CLASS__, 'display_assets'));

			
	}
	

	
	// ****************************************** SHORTCODE HANDLE *********************************

	static function handle_shortcode($atts){

	}


	// ****************************************** CUSTOM ACTIONS *********************************

	static function new_instance(){
		self::set_defaults();

		$instanceName = isset($_REQUEST['id'])?$_REQUEST['id']:null;
		$instanceName = self::look_for_instance($instanceName);

		die($instanceName);
		return;
	}

	// ****************************************** ASSETS MANAGEMENT *********************************

	static function register_assets(){
		wp_register_script('extendJQuery', get_template_directory_uri() .  '/js/extendjQuery.js', array('jquery'));
		wp_register_script('extendLightboxLink', plugins_url('', __FILE__) .  '/assets/extendLightbox.js', array('extendJQuery'));
		wp_register_script('linklightboxJS', plugins_url('', __FILE__) .  '/assets/linklightbox.js', array('extendJQuery'));

		wp_register_style('linklightboxCSS', plugins_url('', __FILE__) .  '/assets/linklightbox.css',false);
		wp_register_style('xtdLightBoxCarouselLink', plugins_url('', __FILE__) .  '/assets/carousel-style.css',false);
	}

	static function display_assets() {
		self::set_defaults();
		self::get_instances_as_array(self::$instanceKey);
		
		if( count(self::$instances) ) {
			wp_print_scripts('extendLightboxLink');
			wp_print_scripts('linklightboxJS');

			wp_print_styles('linklightboxCSS');
			wp_print_styles('xtdLightBoxCarouselLink');
		}
	}

	
	/*********************************** MANAGE INSTANCE *******************************************/ 

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

XtdLinkLightbox::init();