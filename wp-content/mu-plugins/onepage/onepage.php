<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: XTD CONTACT
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

class XtdOnePage{
	static $instancesFile ;
	static $instances;
	static $defaultInstanceSettings;
	static $instanceNameStart = "instance";
	static $instanceKey = "xtd_one_page";

	static $actionParameter = 'xtd_onepage';

	// ****************************************** START & DEFAULTS *********************************

	static function set_defaults(){
		self:: $instancesFile  			 =    get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array(
			'duration'=>'1000',
			'swing'=>'linear',
			'enabled'=>false
			);

		self::look_for_instance(self::$instanceNameStart);
	}


	static function init(){

			// add_shortcode( 'xtd_reveal_fx', array(__CLASS__,'handle_shortcode') );
			add_action('wp_head', array(__CLASS__, 'display_assets'));

	}



	// ****************************************** SHORTCODE HANDLE *********************************

	static function handle_shortcode($atts){

	}


	// ****************************************** CUSTOM ACTIONS *********************************

	static function new_instance(){
		self::set_defaults();
		self::get_instances_as_array(self::$instanceKey);

		$instanceName = isset($_REQUEST['id'])?$_REQUEST['id']:null;
		
		return;
	}




	// ****************************************** ASSETS MANAGEMENT *********************************

	static function register_assets(){
		wp_register_script('extendjQueryUI', plugins_url('', __FILE__) .  '/assets/extendjQuery-ui.js', array('jquery'));
	}

	static function display_assets() {
		
		self::set_defaults();
		self::get_instances_as_array(self::$instanceKey);
		self::register_assets();

		if( count(self::$instances) && self::$instances['instance']['enabled'] == "true") {
			wp_print_scripts('extendjQueryUI');
			?>
				
				<script type="text/javascript" onepage="true" src="<?php echo plugins_url('', __FILE__) .  '/assets/xtdOnePageSite.js' ?>" duration="<?php echo self::$instances['instance']['duration'] ?>" swing="<?php echo self::$instances['instance']['swing'] ?>"></script>
			<?php
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

XtdOnePage::init();
