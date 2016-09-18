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

class XtdRevealFX{
	static $instancesFile ;
	static $instances;
	static $defaultInstanceSettings;
	static $instanceNameStart = "RevealFX";
	static $instanceKey = "xtd_reveal_fx";

	static $actionParameter = 'xtd_revealfx_action';

	// ****************************************** START & DEFAULTS *********************************

	static function set_defaults(){
		self:: $instancesFile  			 =   get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array(
			'effect'=>'slide',
			'parameters'=>array(
				'from'=>'left',
				'distance'=>'400px',
				'opacity'=>'0',
				'start'=>'2',
				),
			'over'=>'1500ms',
			'defaultDelay'=>'5000ms',
			'easing'=>'ease',
			'viewportFactor'=>'0',
			'preset'=>'custom',
			'init'=>false,
			);
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
		} else {
			$instanceData = self::$defaultInstanceSettings;
		}

		$instanceName = self::look_for_instance("", $instanceData);
	
		$data = array();
		$data['replace'] = array();
		$data['variables'][$plugin_data['source']['instanceID']] = $instanceName;
		$data['variables']['%instance%'] = $instanceName;

		return $data;
	}

	static function init(){
			CPPluginBase::init(get_called_class());
			
			add_action('wp_footer', array(__CLASS__, 'display_assets'));
			add_action('wp_head', array(__CLASS__, 'add_specific_styles'));

			
	}
	

	static function add_specific_styles(){
		self::set_defaults();
		self::get_instances_as_array(self::$instanceKey);

		if( count(self::$instances) ) {

			if(!xtd_in_editor()){
				?>
				<style type="text/css">
					[reveal-fx]{
						display:none;
					}
				</style>
				<?php
			}
		}
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
		wp_register_script('reveal_fx', plugins_url('', __FILE__) .  '/assets/RevealFX.js', array('extendJQuery'));
	}

	static function display_assets() {
		self::set_defaults();
		self::get_instances_as_array(self::$instanceKey);

		if( count(self::$instances) ) {
				self::register_assets();
				xtd_add_scripts(array('extendJQuery'));
				xtd_add_scripts(array('reveal_fx'));
				?>
				<script type="text/javascript">
			            window.scrollEffects = <?php echo json_encode(self::$instances) ?> ;
			            (function(jQuery) {
			                jQuery(document).ready(function() {
			                    jQuery('[reveal-fx]').each(function() {
			                        var element = jQuery(this);

			                        if (!window.scrollEffects.hasOwnProperty(element.attr("reveal-fx"))) {
			                            element.show();
			                            return;
			                        }

			                        for (var prop in scrollEffects) {
			                            if (element.attr("reveal-fx") == prop) {
			                                element.attr("data-scrollReveal", prop);
			                                element.hide();
			                            }
			                        }


			                    });

			                    window.extendScrollReveal.init();

			                });
			            }(jQuery));
				</script>
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

XtdRevealFX::init();