<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: XTD WEBFONTS
	Plugin URI: 
	Description: Web Font Handler
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [xtd_simple_slider]

if(!function_exists('get_plugin_data_folder')) {
	require_once WPMU_PLUGIN_DIR.'/xtdpluginbase/xtdpluginbase.php';
}

class XtdWebFonts{
	static $instancesFile ;
	static $instances;
	static $defaultInstanceSettings;
	static $instanceNameStart = "WebFont";
	static $instanceKey = "xtd_web_fonts";

	static $actionParameter = 'xtd_webfonts_action';

	// ****************************************** START & DEFAULTS *********************************

	static function set_defaults(){
		self:: $instancesFile  			 =   get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array();
	}

	static function init(){
			add_action('wp_head', array(__CLASS__, 'display_assets'));

			
	

	}
	
	// ****************************************** SHORTCODE HANDLE *********************************

	static function handle_shortcode($atts){

	}


	// ****************************************** CUSTOM ACTIONS *********************************

	static function new_instance(){
		self::set_defaults();

		$instanceName = isset($_REQUEST['id'])?$_REQUEST['id']:null;
		if (isset($_REQUEST['family'])) {
			$instanceName = $_REQUEST['family'];
		}
		$instanceData = array();
		if (isset($_REQUEST['weights'])) {
			$instanceData = $_REQUEST['weights'];
		}
		$instanceName = self::look_for_instance($instanceName, $instanceData);

		die($instanceName);
		return;
	}
	
	static function delete_instance(){
		self::set_defaults();

		$instanceName = isset($_REQUEST['family'])?$_REQUEST['family']:null;
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

	// ****************************************** ASSETS MANAGEMENT *********************************

	static function display_assets() {
		self::set_defaults();
		self::get_instances_as_array(self::$instanceKey);

		if( count(self::$instances) ) {
			$instancesNames = array_keys(self::$instances);
			foreach($instancesNames as $instanceName) {
				echo "<link href=\"//fonts.googleapis.com/css?family=". $instanceName.":".self::$instances[$instanceName]."\" rel=\"stylesheet\" type=\"text/css\" />\n";
			}
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
		} else {
			self::update_instance_in_file(self::$instanceKey, $instanceName, $instanceData);
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
	
	static function update_instance_in_file($pluginName, $instanceName, $instanceData){
		self::$instances[$instanceName] = $instanceData;
		put_instance_in_file($pluginName, $instanceName, self::$instances[$instanceName]);
	}
}

XtdWebFonts::init();