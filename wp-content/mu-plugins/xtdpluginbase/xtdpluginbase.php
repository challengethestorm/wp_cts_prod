<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */

/*
Plugin Name: XTD Plugin Base
Plugin URI:
Description: Layouts simple slider
Author: Extend Studio
Version: 1
Author URI: extendstudio.com
 */

if (!class_exists('CPPluginBase')) {

	class CPPluginBase {
		static public $plugins = array();
		static public $common_files = array();

		static function init($class) {
			self::$plugins[$class::$instanceKey] = $class;
			if (isset($class::$shortcodeKey)) {
				self::$plugins[$class::$shortcodeKey] = $class;
			}
		}

		static function add($shorcode, $class) {
			self::$plugins[$shorcode] = $class;
		}

		static function getInstanceClass($pluginID) {
			if (isset(self::$plugins[$pluginID])) {
				return self::$plugins[$pluginID];
			}
			return null;
		}

		static function check_common_files() {
			$template_directory = get_template_directory();
			if ($template_directory) {
				$extend_jquery = $template_directory . "/js/extendjQuery.js";
				if (!file_exists($extend_jquery)) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
					WP_Filesystem();
					copy_dir(dirname(__FILE__) . "/assets/", $template_directory);
				}
			}
		}
	}
}

// [xtd_simple_slider]
if (!function_exists('get_plugin_data_folder')) {
	function get_plugin_data_folder($pluginName = "") {
		$themefolder = wp_get_theme()->get_template();

		$folder = wp_get_theme()->get_template_directory() . '/plugins-data';

		if ($themefolder) {
			if (!file_exists($folder)) {
				mkdir($folder, 0771, true);
			}
		}

		if ($pluginName) {
			$folder .= '/' . $pluginName;
		}

		if ($themefolder) {
			if (!file_exists($folder)) {
				mkdir($folder, 0771, true);
			}
		}

		return $folder;
	}
}

if (!function_exists("xtd_write_file")) {
	function xtd_write_file($path, $content, $data = "") {
		$result = file_put_contents($path, $content);
		// file_put_contents(xtd_get_save_history_folder()."/saves.txt", $path."##".$result."##".date("Y-m-d H:i:s")."\r\ndata=".$data."\r\n-------------\r\n".$content."\r\n++++++++++++++++++++\r\n", FILE_APPEND);
		return $result;
	}
}

if (!function_exists("xtd_exclusive_write")) {
	function xtd_exclusive_write($file, $filecontent) {
		xtd_write_file($file . ".temp", $filecontent, "");
		@rename($file . ".temp", $file);
	}
}

function get_plugin_next_instance($instances, $instanceNameStart) {
	$max = 0;
	foreach ($instances as $name => $value) {
		$matches = array();
		$instanceMatch = preg_match('/' . $instanceNameStart . '(\d+)/i', $name, $matches);
		if (count($matches)) {
			$val = intval($matches[1]);
			if ($val >= $max) {
				$max = $val;
			}
		}
	}
	$max++;
	return $max;
}

function export_plugin_instance($pluginName, $instanceName) {
	$instances = get_instances_as_array($pluginName);
	$data = array(
		"instanceName" => $instanceName,
		"assets" => array(),
		"data" => array(),
	);

	if (isset($instances[$instanceName])) {
		$data["data"] = $instances[$instanceName];
	}
	return $data;
}

function get_instances_as_array($pluginName = "") {
	$instancesFile = get_plugin_data_folder() . '/instances.php';

	if (!file_exists($instancesFile)) {
		touch($instancesFile);
		file_put_contents($instancesFile, '<?php return "{}";');
	}

	$contents = file_get_contents($instancesFile);

	$pattern = "/<\?php return '(.*)';/";
	$replacement = '${1}';
	$contents = preg_replace($pattern, $replacement, $contents);
	$content = json_decode($contents, true);

	if (strlen($pluginName) == 0 || !$pluginName) {
		return $content;
	}

	if ($content != null && array_key_exists($pluginName, $content)) {
		return $content[$pluginName];
	}

	return array();
}

function put_instance_in_file($pluginName, $instanceName, $instance) {
	$instancesFile = get_plugin_data_folder() . '/instances.php';

	$fp = fopen($instancesFile, "r+");
	$content = get_instances_as_array();

	if (flock($fp, LOCK_EX)) {
		try {
			if (!is_array($content)) {
				$content = array();
			}

			if (!array_key_exists($pluginName, $content)) {
				$content[$pluginName] = array();
			}
			if (!array_key_exists($instanceName, $content[$pluginName])) {
				$content[$pluginName][$instanceName] = array();
			}
			$content[$pluginName][$instanceName] = $instance;
			ftruncate($fp, 0);
			fwrite($fp, "<?php return '" . json_encode($content) . "';");
			fflush($fp);
		} catch (Exception $e) {

		}
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}

function remove_instance_from_file($pluginName, $instanceName) {
	$instancesFile = get_plugin_data_folder() . '/instances.php';
	$fp = fopen($instancesFile, "r+");
	$content = get_instances_as_array();

	if (flock($fp, LOCK_EX)) {
		try {
			if (!is_array($content)) {
				$content = array();
			}

			if (isset($content[$pluginName]) && $content[$pluginName] && array_key_exists($pluginName, $content) && array_key_exists($instanceName, $content[$pluginName])) {
				unset($content[$pluginName][$instanceName]);
			}

			ftruncate($fp, 0);
			fwrite($fp, "<?php return '" . json_encode($content) . "';");
			fflush($fp);
		} catch (Exception $e) {

		}
		flock($fp, LOCK_UN);
		fclose($fp);
	}
}

function get_media_queries_object() {
	$themefolder = wp_get_theme()->get_template();
	$folder = wp_get_theme()->get_template_directory();
	$mediasFile = $folder . '/mediaqueries.js';
	$content = '{"mobile":{"mediaString":"default","defaultWidth":"400px","deviceWidth":"767px","mediaWidth":"767px","minWidth":0},"tablet":{"mediaString":"only screen and (min-width : 768px)","deviceWidth":991,"mediaWidth":"768px","defaultWidth":"800px","minWidth":767},"desktop":{"mediaString":"only screen and (min-width : 1024px)","deviceWidth":"auto","mediaWidth":"1024px","defaultWidth":"auto","minWidth":1024,"userWidth":"auto"}}';
	if (file_exists($mediasFile)) {
		$content = file_get_contents($mediasFile);
	}
	return json_decode($content, true);
}