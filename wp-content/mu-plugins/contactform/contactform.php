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

if (!function_exists('get_plugin_data_folder')) {
	require_once WPMU_PLUGIN_DIR . '/xtdpluginbase/xtdpluginbase.php';
}

class XtdContactForms {
	static $id = "";
	static $config = "";
	static $styles = array();
	static $scripts = array();

	static $instancesFile;
	static $instances;
	static $instanceNameStart = "ContactForm";
	static $instanceKey = "xtd_contact_form";
	static $shortcodeKey = "xtd_contact";
	static $defaultInstanceSettings;

	static $actionParameter = 'xtd_contact_form_action';

	static function export($plugin_data) {
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
				"instanceData" => $data,
			),
			"files" => $files,
			"styles" => $styles,
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

	static function set_defaults() {
		self::$instancesFile = get_plugin_data_folder() . '/instances.php';
		self::$instances = array();
		self::$defaultInstanceSettings = array(
			'email' => 'admin@domain.com',
			'send_option' => array(
				'emailSent' => 'firstOption',
				'relPath' => '',
				'emailSentMessage' => 'Your%20message%20was%20sent%20successfully!',
			),
			'messages' => 'undefined',

		);
	}

	static function init() {
		CPPluginBase::init(get_called_class());

		add_shortcode('xtd_contact', array(__CLASS__, 'handle_shortcode'));

	}

	static function new_instance() {
		self::set_defaults();

		$instanceName = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
		$instanceName = self::look_for_instance($instanceName);

		die($instanceName);
		return;
	}

	static function handle_shortcode($atts) {
		self::register_assets();

		xtd_add_scripts(array('contact_forms_js'));
		array_push(self::$styles, 'contact_forms_style');
		xtd_add_styles(self::$styles);
		$styles = xtd_create_preview_styles(self::$styles);

		$atts = shortcode_atts(
			array(
				'id' => 'CF_' . time(),
			),
			$atts
		);

		self::set_defaults();
		self::get_instances_as_array(self::$instanceKey);

		$instance = self::$defaultInstanceSettings;
		if (isset(self::$instances[$atts['id']])) {
			$instance = self::$instances[$atts['id']];
		}

		$send_option = $instance['send_option']['emailSent'];
		$messages = $instance['messages'];
		$relPath = $instance['send_option']['relPath'];
		$send_message = $instance['send_option']['emailSentMessage'];

		add_action('wp_head', array(__CLASS__, 'display_assets'));

		$form = $styles . '
				<script>
					 jQuery(document).ready(function() {
					 		var ' . $atts['id'] . '_form = jQuery("#' . $atts['id'] . '").find("form");
					        ' . $atts['id'] . '_form.attr("data-location", "' . plugins_url('', __FILE__) . '/sendmail.php")
					        ' . $atts['id'] . '_form.xtdContactForm({
					            "emailSent": "' . $send_option . '",
					             "id": "' . $atts['id'] . '",
					            "messages" : ' . json_encode($messages) . ',' . '
					            "relPath": "' . $relPath . '",
					            "emailSentMessage": "' . $send_message . '"
					        });
					    });
				</script>';

		return $form;
	}

	static function custom_scripts() {

	}

	static function register_assets() {
		wp_register_style('contact_forms_style', plugins_url('', __FILE__) . '/assets/xtdContactForms.css', false);
		wp_register_script('extendJQuery', get_template_directory_uri() . '/js/extendjQuery.js', array('jquery'));
		wp_register_script('contact_forms_js', plugins_url('', __FILE__) . '/assets/xtdContactForms.js', array('extendJQuery'));
	}

	/*********************************** MANAGE INSTANCE *******************************************/

	static function look_for_instance($instanceName, $instanceData = array()) {

		//try to get all instances
		self::get_instances_as_array(self::$instanceKey);

		if (!$instanceName) {

			$instanceName = self::$instanceNameStart . get_plugin_next_instance(self::$instances, self::$instanceNameStart);
		}

		// try to get current instance settings or set defaults
		if (!isset(self::$instances[$instanceName])) {
			self::put_instance_in_file(self::$instanceKey, $instanceName, $instanceData);
		}

		return $instanceName;
	}

	static function get_instances_as_array($pluginName = "") {
		self::$instances = get_instances_as_array($pluginName);
	}

	static function put_instance_in_file($pluginName, $instanceName, $instanceData) {
		if (!empty($instanceData)) {
			self::$instances[$instanceName] = $instanceData;
		} else {
			self::$instances[$instanceName] = self::$defaultInstanceSettings;
		}
		put_instance_in_file($pluginName, $instanceName, self::$instances[$instanceName]);
	}
}

XtdContactForms::init();

if (function_exists('cp_contact_form_send_mail')) {
	add_action('wp_loaded', 'cp_contact_form_send_mail');
}