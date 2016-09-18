<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */

/*
Plugin Name: SimpleSlider
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

class XtdSimpleCarousel {
	static $id = "";
	static $config = "";
	static $styles = array();
	static $scripts = array();

	static $instancesFile ;
	static $instances;
	static $defaultInstanceSettings;
	static $instanceNameStart = "carousel";
	static $actionParameter = 'xtd_simple_carousel_action';
	static public $instanceKey = "xtd_simple_carousel";

	static function set_defaults(){
		self:: $instancesFile  			 =   get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array();
	}

	static function init() {
		self::set_defaults();
		CPPluginBase::init(get_called_class());
		add_shortcode('xtd_simple_carousel', array(__CLASS__, 'handle_shortcode'));


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
	static function handle_shortcode($atts) {

		$instanceName = $atts['id'];
		self::look_for_instance($instanceName);
		
		self::$id = $atts['id'];

		self::register_assets();
		$mediaObj = get_media_queries_object();
		

		xtd_add_scripts(array('owl_carousel_min_js'));
		array_push(self::$styles, 'owl_carousel_theme_css');
		array_push(self::$styles, 'owl_carousel_css');
		array_push(self::$styles, 'owl_carousel_transition_css');
		xtd_add_styles(self::$styles);
		$styles = xtd_create_preview_styles(self::$styles);

		return $styles . '<script data-carousel="true">
		 jQuery(document).ready( function(){
		 		var sliderInstance = jQuery("#' . self::$id . '");
		 		var sliderContent = jQuery(".' . self::$id . '_content");
		 		sliderInstance.children("p").remove();
		 		sliderContent.children("p").remove();
		 		sliderContent.children("[data-fake-pagination=\"true\"]").remove();
		 		sliderContent.each(function() {
		 			var $self = jQuery(this);
		 			$self.find(".item_wrapper").show();
		 			$self.find(".item_wrapper").css("width","100%");
		 			$self.owlCarousel({
			 			navigation : false,
			 			slideSpeed : $self.parent().attr("data-slider-speed"),
			 			paginationSpeed  : $self.parent().attr("data-slider-speed"),
			 			pagination: $self.parent().attr("data-bullets") === "true"?true:false,
			 			autoPlay: $self.parent().attr("data-autoplay") === "true" ? $self.parent().attr("data-delay") : false,
			 			mouseDrag: $self.parent().attr("data-drag") === "true"?true:false,
			 			touchDrag: $self.parent().attr("data-drag") === "true"?true:false,
			 			transitionStyle: $self.parent().attr("data-transition") ?$self.parent().attr("data-transition") : "",
			 			navigationText: ["",""],
			 			items : $self.parent().attr("data-slider-items-desktop"),
			 			itemsTablet: [' . $mediaObj['desktop']['minWidth'] . ',$self.parent().attr("data-slider-items-tablet")],
			 			itemsMobile: [' . $mediaObj['tablet']['minWidth'] . ', $self.parent().attr("data-slider-items-mobile")]
			 		});
					var prevButton = $self.parent().find("[data-slider=\'' . self::$id . '-prev\']");
					var nextButton = $self.parent().find("[data-slider=\'' . self::$id . '-next\']");

					prevButton.click(function(e){ e.preventDefault(); e.stopPropagation(); $self.trigger("owl.prev") });

					sliderContent.find(".owl-wrapper, .owl-item").css("height","100%");
					nextButton.click(function(e){ e.preventDefault(); e.stopPropagation(); $self.trigger("owl.next") });
					/*setTimeout(function() {
						var prevWrap = jQuery("<div class=\'owl-prev-wrap\'></div>");
						prevButton.wrap(prevWrap);
						var nextWrap = jQuery("<div class=\'owl-next-wrap\'></div>");
						nextButton.wrap(nextWrap);
					}, 1000);*/
		 		})
		 })
		 </script>
		 '."\n\n".'
		  <!--[if lte IE 9]>
		  	<style>
		 		#'. self::$id . ' .owl-wrapper{ position:absolute};
	 		</style>
		 	<script>
		 		jQuery(document).ready( function(){
		 			jQuery("#'. self::$id . ' .owl-prev,#'. self::$id . ' .owl-next").css("position","absolute");
		 		})
	 		</script>
		 <![endif]-->'."\n\n";
	}

	static function custom_scripts() {

	}

	static function register_assets() {
		$mu_plugins = realpath(dirname(__FILE__));
		wp_register_style('owl_carousel_css', plugins_url('owl-carousel/owl.carousel.css', $mu_plugins), false);
		wp_register_style('owl_carousel_theme_css', plugins_url('owl-carousel/owl.theme.css', $mu_plugins), false);
		wp_register_style('owl_carousel_transition_css', plugins_url('owl-carousel/owl.transitions.css', $mu_plugins), false);
		wp_register_script('jquery-visible', plugins_url('owl-carousel/jquery.visible.js', $mu_plugins), array('jquery'));
		wp_register_script('owl_carousel_min_js', plugins_url('owl-carousel/owl.carousel.js', $mu_plugins), array('jquery','jquery-visible'));
	}

	static function new_instance(){
		$instanceName = isset($_REQUEST['id'])?$_REQUEST['id']:null;
		$instanceName = self::look_for_instance($instanceName);

		die($instanceName);
		return;
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

XtdSimpleCarousel::init();