<?php

/**
 * @package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: XTD WIDGETS AREA
	Plugin URI: 
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

if(!function_exists('get_plugin_data_folder')) {
	require_once WPMU_PLUGIN_DIR.'/xtdpluginbase/xtdpluginbase.php';
}

class XtdWidgetsArea{
	static $instances;
	static $defaultInstanceSettings;
	static $instanceNameStart = "widgetarea";
	static $instanceKey = "xtd_widgets_area";
	static $actionParameter = 'xtd_widgets_area_action';

	// ****************************************** START & DEFAULTS *********************************

	static function set_defaults(){
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array(
			'name'=>'Widgets Area',
			);
	}

	static function init(){
		add_action('widgets_init',  array(__CLASS__, 'add_widgets_areas'));
		add_shortcode('xtd_widgets_area', array(__CLASS__,'handle_shortcode') );
		
	}


	static function add_widgets_areas(){
		self::get_instances_as_array(self::$instanceKey);

		if(count(self::$instances)){
			foreach (self::$instances as $key => $value) {
				self::register_widget_area($value['name'],$key);
			}
		}
	}

	static function register_widget_area($name,$id){
		register_sidebar( array(
			'name' => $name,
			'id' =>  $id,
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4>',
			'after_title'   => '</h4>',
		));
	}




	
	// ****************************************** SHORTCODE HANDLE *********************************

	static function handle_shortcode($atts){
		$atts = shortcode_atts(
			array(
				'id' => '',
				'name'=>''
			),
			$atts
		);

		self::get_instances_as_array(self::$instanceKey);
		

		if(isset(self::$instances[$atts['id']])){
			$instance = self::$instances[$atts['id']];
		} else {
			$instance = array('name'=>$atts['name']?$atts['name']:"Widget Area");
			put_instance_in_file(self::$instanceKey,$atts['id'],$instance);
			self::register_widget_area($atts['name']?$atts['name']:"Widget Area",$atts['id']);

		}

		ob_start();
		echo '<div id="'.$atts['id'].'" data-area-name="'.$instance["name"].'">';
		 if (function_exists('dynamic_sidebar') && dynamic_sidebar($atts['id'])){
		 	
		 } else {
		 	if(xtd_in_editor()){
			 	echo ''.
			 		'<div class="pre-widget">'.
					'	<p>No widgets to display</p>'.
					'</div>';
			}
		 }
		echo '</div>';

		 $content =  ob_get_contents() ;
		 ob_end_clean();

		 return $content;
	}


	// ****************************************** CUSTOM ACTIONS *********************************

	static function new_instance(){
        self::set_defaults();

		$instanceName = isset($_REQUEST['id'])?$_REQUEST['id']:null;

		if(isset($_REQUEST['name'])){
			self::$defaultInstanceSettings['name'] = $_REQUEST['name'];
		}

		$instanceName = self::look_for_instance($instanceName);
		die($instanceName);
		return;
	}

	static function get_instance(){
		self::set_defaults();
		self::get_instances_as_array(self::$instanceKey);
		die(json_encode(self::$instances));
	}

	// ****************************************** ASSETS MANAGEMENT *********************************

	static function register_assets(){
	
	}

	static function display_assets() {
	
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

XtdWidgetsArea::init();