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

class XtdSimpleSlider {
	static $id = "";
	static $config = "";
	static $styles = array();
	static $scripts = array();

	static $instancesFile ;
	static $instances;
	static $defaultInstanceSettings;

	static $instanceNameStart = "slider";
	static $actionParameter = 'xtd_simple_slider_action';
	static public $instanceKey = "xtd_simple_slider";

	static function set_defaults(){
		self:: $instancesFile  			 =   get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array();
	}

	static function init() {
		self::set_defaults();
		CPPluginBase::init(get_called_class());
		add_shortcode('xtd_simple_slider', array(__CLASS__, 'handle_shortcode'));

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

		self::register_assets();
		xtd_add_scripts(array('owl_carousel_min_js'));
		array_push(self::$styles, 'owl_carousel_theme_css');
		array_push(self::$styles, 'owl_carousel_css');
		array_push(self::$styles, 'owl_carousel_transition_css');
		xtd_add_styles(self::$styles);

		$styles = xtd_create_preview_styles(self::$styles);
		self::$id = $atts['id'];
		return '<script>
		 jQuery(document).ready( function(){
		 		var sliderInstance = jQuery("#' . self::$id . '");
		 		sliderInstance.children("p").remove();

		 		var sliderContentWrapper = jQuery(".content-wrapper", sliderInstance);
		 		sliderContentWrapper.removeClass("owl-theme owl-carousel");
		 		sliderInstance.find("[data-fake-pagination=\"true\"]").remove();

		 		var sliderContent = jQuery(".' . self::$id . '_content", sliderInstance);
		 		sliderContent.children("p").remove();
		 		if(sliderInstance.attr("data-autoheight") !== "false") {
		 			sliderContent.css("height","auto");

		 		}

		 		sliderContent.each(function() {
		 			var $self = jQuery(this);
		 			var autoHeight = sliderInstance.attr("data-autoheight") !== "false"?true:false
		 			$self.owlCarousel({
			 			navigation : false,
			 			slideSpeed : sliderInstance.attr("data-slider-speed"),
			 			paginationSpeed  : sliderInstance.attr("data-slider-speed"),
			 			singleItem:true,
			 			autoHeight: autoHeight,
			 			pagination: sliderInstance.attr("data-bullets") === "true"?true:false,
			 			mouseDrag: sliderInstance.attr("data-drag") === "true"?true:false,
			 			touchDrag: sliderInstance.attr("data-drag") === "true"?true:false,
			 			autoPlay: sliderInstance.attr("data-autoplay") === "true" ?sliderInstance.attr("data-delay") : false,
			 			transitionStyle: (sliderInstance.attr("data-transition") && sliderInstance.attr("data-transition") != "linear") ?sliderInstance.attr("data-transition") : false,
			 			navigationText: ["",""]
			 		});
					var prevButton =sliderInstance.find("[data-slider=\'' . self::$id . '-prev\']");
					var nextButton = sliderInstance.find("[data-slider=\'' . self::$id . '-next\']");

					
					$self.bind("owl.onplay", function() {
						
						    var event; // The custom event that will be created

						    if (document.createEvent) {
						        event = document.createEvent("HTMLEvents");
						        event.initEvent("scroll", true, true);
						    } else {
						        event = document.createEventObject();
						        event.eventType = "scroll";
						    }

						    event.eventName = "scroll";

						    if (document.createEvent) {
						        window.dispatchEvent(event);
						    } else {
						        window.fireEvent("on" + event.eventType, event);
						    }
					   

					})

					if(!autoHeight){
						jQuery("head").append("<style>#'. self::$id . ' .owl-wrapper,#'. self::$id . ' .owl-wrapper .owl-item{height:100%;}</style>")
					} else {
						jQuery("head").append("<style>#'. self::$id . ' .owl-wrapper{overflow:hidden;}</style>")
					}

					prevButton.click(function(e){ e.preventDefault(); e.stopPropagation(); $self.trigger("owl.prev") });
					nextButton.click(function(e){ e.preventDefault(); e.stopPropagation(); $self.trigger("owl.next") });
					sliderContent.find(".item").attr("style","");

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
		 <![endif]-->'."\n\n".
		 $styles;
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

XtdSimpleSlider::init();