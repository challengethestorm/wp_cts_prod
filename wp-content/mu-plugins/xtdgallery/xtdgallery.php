<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: SimpleSlider
	Plugin URI: 
	Description: Layouts Gallery
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [xtd_simple_slider]

if(!function_exists('get_plugin_data_folder')) {
	require_once WPMU_PLUGIN_DIR.'/xtdpluginbase/xtdpluginbase.php';
}

class XtdGallery{
 	
 	static public $instanceKey = "xtd_gallery";
 	static $instancesFile;
 	static $actionParameter = 'xtd_gallery_action';
 	static $instances;
 	static $instanceNameStart = 'gallery';
 	static $defaultInstanceSettings;
 	
	static function init(){
			CPPluginBase::init(get_called_class());
			add_shortcode( 'xtd_gallery', array(__CLASS__,'handle_shortcode') );

	}

	static function set_defaults(){
		self:: $instancesFile  			 =    get_plugin_data_folder() . '/instances.php';
		self:: $instances  	   			 = 	  array();
		self:: $defaultInstanceSettings  =    array();
	}

	static function export($plugin_data){
		$atts = $plugin_data['shortcode']['attrs'];
		$instanceFileName =  $atts['id']. "_" . $atts['skin'];
		$instanceFolder = get_plugin_data_folder('xtd_gallery/instances');
		$assets = array(
			'%theme%/plugins-data/xtd_gallery/instances/'.$instanceFileName.'.js',
			'%theme%/plugins-data/xtd_gallery/instances/'.$instanceFileName.'.css'
		);

		$files = array();
		

		$data = array();
		$atachments = array();

		if (isset($atts['ids'])) {
			$ids = explode(",", $atts['ids']);
			foreach ($ids as $id) {
				$meta = wp_get_attachment_metadata($id, true);
				$file = "%uploads%/".$meta['file'];
				array_push($atachments, array('file' => $file, 'id' => $id));
				array_push($assets, $file);
			};
		}

		$files = array_merge($files, $assets);
		$styles = array();
		array_push($styles, get_template_directory_uri().'/plugins-data/xtd_gallery/instances/'.$instanceFileName.'.css');

		return array(
			"data" => array(
				"assets" => $assets,
				"instanceData" => $data,
				"atachments" => $atachments
			),
			"files" => $files,
			"styles" => $styles
		);
	}

	static function import($data){
		$map = array();

		$id = $data['shortcode']['attrs']['id'];

		$upload_obj = wp_upload_dir();
		$upload_dir = $upload_obj['basedir'];
		
		$returnObj = array();
		$returnObj['replace'] = array();
		$returnObj['variables'] = array();

		$instanceData = array();
		if (isset($data["instanceData"])) {
			$instanceData = $data["instanceData"];
		}
		$instanceName = self::look_for_instance("", $instanceData);
		
		
		$returnObj['variables'][$id] = $instanceName;
		$returnObj['variables']['%instance%'] = $instanceName;
		$returnObj['variables']["%theme%"] = get_template_directory();
		$returnObj['variables']["%uploads%"] = $upload_dir;
		

		if (isset($data['atachments'])) {
			for ($i=0; $i < count($data['atachments']); $i++) {
					$filename = $data['atachments'][$i]['file'];
					
				
					foreach ($returnObj['variables'] as $name => $value) {
						$filename = str_replace($name, $value, $filename);
					}

					$filetype = wp_check_filetype( basename( $filename ), null );

					$wp_upload_dir = wp_upload_dir();

					// Prepare an array of post data for the attachment.
					$attachment = array(
						'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
						'post_mime_type' => $filetype['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
						'post_content'   => '',
						'post_status'    => 'inherit'
					);

					//echo $filename;
					// Insert the attachment.
					$attach_id = wp_insert_attachment($attachment, $filename);

					if (!isset($map['atachments'])) {
						$map['atachments'] = array();
					}
					$map['atachments'][$data['atachments'][$i]['id']] = $attach_id;

					// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
					require_once( ABSPATH . 'wp-admin/includes/image.php' );

					// Generate the metadata for the attachment, and update the database record.
					$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
					wp_update_attachment_metadata( $attach_id, $attach_data );
			}
		}

		if (isset($map['atachments']) && isset($data['shortcode']['attrs']['ids'])) {
				$ids = explode(",", $data['shortcode']['attrs']['ids']);
				for ($i=0; $i < count($ids); $i++) { 
					if (isset($map['atachments'][$ids[$i]])) {
						$ids[$i] = $map['atachments'][$ids[$i]];
					}
				}
				$ids = implode(",", $ids);
		}

		if (isset($ids)) {
			$returnObj['variables'][$data['shortcode']['attrs']['ids']] = $ids;
		}

		return $returnObj;
	}

	static function handle_shortcode($atts){
		self::register_assets();


		$atts = shortcode_atts(
			array(
				'id' => 'xtd_gal_'. time(),
				'columns' 	=> '3',
				'ids' => '',
				'lb' => '1',
				'orderby'=> '',
				'skin'=>'skin01'
			),
			$atts
		);

		ob_start();

		$size="";
		if($atts['columns']>12){
			$size = "thumbnail";
		} elseif ($atts['columns']>6) {
			$size = "medium";
		} elseif ($atts['columns']>2) {
			$size = "large";
		}  else {
			$size = "full";
		}

		$gallery = do_shortcode('[gallery link="file" size="'.$size.'" columns="'.$atts['columns'].'" ids="'.$atts['ids'].'" orderby="'.$atts['orderby'].'" ]');

		if($atts['lb']=='1'){


			xtd_add_styles(array('xtdLightBox'));
			xtd_add_scripts(array('extendLightbox'));

			// images ids
			// 
			$galleryParts = explode("<img", $gallery);
			$idsArray = explode(",", $atts['ids']);
			

			if(xtd_in_editor()){
				$newContent = "";
				foreach ($idsArray as $key => $value) {
					$newContent .= $galleryParts[$key]. '<img data-id="'.$value.'"';
				}

				$newContent .= $galleryParts[ count($galleryParts)-1 ];
				$gallery = $newContent;
			}

			
			$content =  '<div id="'.$atts['id'].'"> '. 
					str_replace('<a', '<a rel="'.$atts['id'].'_lightbox" data-fancybox-group="gallery"', $gallery );

					// remove gallery style = 
					$content = preg_replace("/<style(.*)style>/is", "", $content);
					$content = preg_replace("/<br(.*?)>/is", "", $content);
					$content = $content. '<style>#'.$atts['id'].' .wp-caption-text.gallery-caption{display:none;}</style>';

			$instanceFileName =  $atts['id']. "_" . $atts['skin'];
			$skinInstance = get_plugin_data_folder('xtd_gallery/instances') .'/'. $instanceFileName . '.css';
			if(!file_exists($skinInstance)){
				$cssContent = file_get_contents(dirname(__FILE__) . '/assets/skins/'.$atts['skin'].'/style.css');
				$cssContent = str_replace('@instanceName', $atts['id'] . '_lightbox', $cssContent);
				file_put_contents($skinInstance, $cssContent);
			}

			$skinInstance = get_plugin_data_folder('xtd_gallery/instances') .'/'. $instanceFileName . '.js';
			if(!file_exists($skinInstance)){
				$skinJs  = file_get_contents(dirname(__FILE__) . '/assets/skins/'.$atts['skin'].'/xtdLightbox.js');
				$skinJs  = str_replace('@instanceName',  $atts['id'] . '_lightbox', $skinJs);
				file_put_contents($skinInstance, $skinJs);
			}

			if(!file_exists(get_plugin_data_url('xtd_gallery/instances')  .'/'.$atts['skin'].'_assets' )){
				self::copydir(dirname(__FILE__) . '/assets/skins/'.$atts['skin']. '/'.$atts['skin']. '_assets/', get_plugin_data_folder('xtd_gallery/instances')  .'/'.$atts['skin'].'_assets' );
			}

			wp_register_style( $atts['id']. '_instance', get_plugin_data_url('xtd_gallery/instances')  .'/'. $instanceFileName . '.css',false);
			wp_print_styles($atts['id']. '_instance');
		

			wp_register_script('lightbox_' . $atts['id'], get_plugin_data_url('xtd_gallery/instances') .'/'.  $instanceFileName . '.js', false);
			wp_print_scripts('lightbox_' . $atts['id']);

			
			$content .= ob_get_contents();
			$content .= "</div>";
  		 	ob_end_clean();
			return $content;
		} else {
			return '<div id="'.$atts['id'].'"> '. $gallery . '</div>';
		}
	}

	static function custom_scripts(){
		
	}


	static function copydir($source,$destination)
	{

		if(!file_exists($destination)){
			wp_mkdir_p($destination);
		}

		if(!is_dir($destination)){
			$oldumask = umask(0); 
			mkdir($destination, 01777); // so you get the sticky bit set 
			umask($oldumask);
		}
		

		$dir_handle = @opendir($source) or die("Unable to open");
		
		while ($file = readdir($dir_handle)) 
		{
			if($file!="." && $file!=".." && !is_dir("$source/$file"))
			copy("$source/$file","$destination/$file");
		}
		closedir($dir_handle);
	}

	static function register_assets(){
		wp_register_script('extendJQuery', get_template_directory_uri() . '/js/extendjQuery.js', array('jquery'));
		wp_register_script('extendLightbox', plugins_url('', __FILE__) .  '/xtdLightBox/extendLightbox.js', array('extendJQuery'));
		wp_register_style('xtdLightBoxCss', plugins_url('', __FILE__) .  '/xtdLightBox/xtdLightbox.css',false);
		wp_register_style('xtdLightBoxCarousel', plugins_url('', __FILE__) .  '/xtdLightBox/carousel-style.css',false);
		wp_register_style('xtdLightBoxEffects', plugins_url('', __FILE__) .  '/xtdLightBox/effects.css',false);
		
		wp_register_style('xtdLightBox', '', array('xtdLightBoxCss', 'xtdLightBoxCarousel', 'xtdLightBoxEffects'));
		
	}

	static function get_images(){
		
		$shortcode = isset($_REQUEST['shortcode']) ?  $_REQUEST['shortcode'] : "";
		$idsString = array();
		preg_match_all('/ids="(.*?)"/', $shortcode, $idsString);
		$idsArray = array();

		if( count($idsString) == 2){
			$idsArray =  explode(",", $idsString[1][0]);
		}

		if(!$idsArray){
			return "{}";
		}

		// var_dump($query_images->posts);
		$response = array();

		foreach ($idsArray as $key => $imageID) {
			$image_sizes = array("thumbnail", "medium", "large", "full");
			$imageURLS = array();
			foreach ($image_sizes as $key => $size) {
				$props = wp_get_attachment_image_src($imageID,$size);
				$imageURLS[$size] = $props[0];
			}
			
			$response[$imageID]=$imageURLS;
		}

		die(json_encode($response));
		
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

XtdGallery::init();