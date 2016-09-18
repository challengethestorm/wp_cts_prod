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

class XtdHoverFX{
	static $instancesFile;
	static $instances;
	static $defaultInstanceSettings;
	static $instanceNameStart = "ContentSwap";
	static $instanceKey = "xtd_hover_fx";

	static $actionParameter = 'xtd_hoverfx_action';

	// ****************************************** START & DEFAULTS *********************************

	static function set_defaults(){
		self:: $instancesFile  			 =    get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array(
			'effectType'=>'',
			'contentType'=>'overlay',
			'overflowEnabled'=>'false',
			'effectDelay'=>'800',
			'effectEasing'=>'Ease',
			'overlayColor'=>'490A3D',
			'innerColor'=>'ffffff',
			'openPage'=>'same',
			'name'=>'',
			'overlayColor'=>'490A3D',
			'captionType'=>'490A3D',
			'operationType'=>'edit',
			'hasls'=>'true',
			'additionalWrapperClasses'=>'',
			'direction'=>'bottom',
			'useSameTemplate'=>'true',
			);
	}

	static function init(){

			CPPluginBase::init(get_called_class());
			
			add_action('wp_head', array(__CLASS__, 'display_assets'));


	}

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
		wp_register_script('extendJQuery', get_template_directory_uri() . '/js/extendjQuery.js', array('jquery'));
		wp_register_script('HoverFX', plugins_url('', __FILE__) .  '/assets/HoverFX.js', array('extendJQuery'));
		wp_register_style( 'HoverFX', plugins_url('', __FILE__) . '/assets/HoverFX.css',false);

	}

	static function hoverfx_footer() {
		?>
		<script type="text/javascript">

					(function(jQuery) {

					jQuery(window).load(function(){
						var hoverEffects = <?php echo json_encode(self::$instances); ?>;
					    var timeout = setTimeout(function() {
					    	initHoverFX(hoverEffects);
					    }, 10);
					    jQuery(window).resize(function(e) {
							clearTimeout(timeout);
							timeout = setTimeout(function() {
								initHoverFX(hoverEffects,null, e);
							}, 150);

					    })
					});

					}(menus_jQuery));
				</script>
		<?php
	}

	static function display_assets() {
		self::set_defaults();
		self::get_instances_as_array(self::$instanceKey);

		if( count(self::$instances) || xtd_in_editor()) {

				self::register_assets();
				xtd_add_scripts(array('extendJQuery'));
				xtd_add_scripts(array('HoverFX'));
				xtd_add_styles(array('HoverFX'));

				add_action( 'wp_footer', array(__CLASS__, 'hoverfx_footer') );
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

XtdHoverFX::init();
