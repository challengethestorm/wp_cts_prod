<?php

if(!function_exists('get_plugin_data_folder')) {
	require_once WPMU_PLUGIN_DIR.'/xtdpluginbase/xtdpluginbase.php';
}


class XtdGoogleAnalytics{
	static $instanceKey = "analytics";
	static $actionParameter = 'analytics_action';
	static $instances =array();

	static function init(){
			add_action('wp_head',array(__CLASS__,'add_my_google_analytics') ,99);
	}
	
	static function add_my_google_analytics() {

		$instance = get_instances_as_array(self::$instanceKey);
		$verification = isset($instance['verification'])?$instance['verification']:"";
		$siteID = isset($instance['id'])?$instance['id']:"";
	
		if( strlen($verification)){?>
			<meta name="google-site-verification" content="<?php echo $verification ?>">
		 <?php }

		 if(strlen($siteID)){?>
			<script type="text/javascript">
			  var _gaq = _gaq || [];
			  _gaq.push(['_setAccount', '<?php echo $siteID ?>']);
			  _gaq.push(['_trackPageview']);
			  (function() {
			    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			    ga.src = ('https:' == document.location.protocol ? 'https://ssl' :
			'http://www') + '.google-analytics.com/ga.js';
			    var s = document.getElementsByTagName('script')[0];
			s.parentNode.insertBefore(ga, s);
			  })();
			</script>
		<?php }
	}







	static function put_instance(){
		$verification = isset($_REQUEST['verification'])?$_REQUEST['verification']:"";
		$_id = isset($_REQUEST['id'])?$_REQUEST['id']:"";

		self::put_instance_in_file(self::$instanceKey,'verification', $verification);
		self::put_instance_in_file(self::$instanceKey,'id', $_id);
		return;
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

XtdGoogleAnalytics::init();