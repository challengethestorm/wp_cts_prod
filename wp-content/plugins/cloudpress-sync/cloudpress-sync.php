<?php

/**
 * Plugin Name: CloudPress Sync
 * Plugin URI: http://cloud-press.net
 * Description: A plugin that helps you transfer your CloudPress site to your hosting server. It also helps keep your CloudPress project and your live site in sync.
 * Version: 1.2.2
 * Author: CloudPress
 * Author URI: http://www.cloud-press.net
 * License: GPL2
 */

/*
error_reporting(E_ERROR);

ini_set('display_errors', 1);*/

require 'plugin-updates/plugin-update-checker.php';



class CloudPressSync
{
    static $page_title = "CloudPress Sync";
    static $menu_slug = "cloudpress-sync-page";
    static $capability = "manage_options";
    static $sync_file = "sync.json";
    static $pair_option_name = "cloudpress-sync-json";
    static $sync_data = null;
    static $sync_file_path = "";
    static $cpSync;

    static function activate_redirect($plugin) {
        if( $plugin == plugin_basename( __FILE__ ) ) {
          exit(wp_redirect(admin_url("tools.php?page=cloudpress-sync-page")));
        }
    }

    static function theme_init() {
      remove_all_filters('pre_site_transient_update_plugins');
    }

    static function not_on_cloudpress(){
       ?>
      <div class="notice notice-error">
        <p><?php echo 'CloudPress Sync Plugin should be installed only on your own site, not on cloudpress project! The plugin was disabled and you should remote it.'; ?></p>
       </div>
    <?php
    }

    static function update_notice() {
        ?>
      <div class="notice notice-error">
        <p><?php echo 'There is a new version of CloudPress Sync available. Please update the plugin from <a href="'. admin_url( 'plugins.php?plugin_status=upgrade').'">here</a>.'; ?></p>
       </div>
    <?php
    }

    function disabled_notice() {
        ?>
      <div class="notice notice-error">
        <p><?php echo 'CloudPress Sync Plugin requires php version  5.3 or higher! The plugin was disabled.'; ?></p>
       </div>
    <?php
    }

    
    static function check_requirements() {
      return !version_compare(phpversion(), '5.3.0', '<') && !file_exists(WPMU_PLUGIN_DIR.'/cloudpress-sync/cloudpress-sync.php');
    }

    static function deactivate_check(){

      if (!self::check_requirements()) {
        if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            add_action( 'admin_notices', array( __CLASS__, file_exists(WPMU_PLUGIN_DIR.'/cloudpress-sync/cloudpress-sync.php') ? 'not_on_cloudpress' : 'disabled_notice' ) );
            if ( isset( $_GET['activate'] ) ) {
              unset( $_GET['activate'] );
            }
        }
      }
    }

    static function init() {

        add_action( 'admin_init', array( __CLASS__, 'deactivate_check' ) );
        if (!self::check_requirements()) {
          return;
        }

        add_action( 'after_setup_theme', array(__CLASS__,'theme_init'), 9999);
       
        register_activation_hook( __FILE__, array(__CLASS__,'activate') );

        self::$sync_file_path = dirname(__FILE__)."/".self::$sync_file;
        if (file_exists(self::$sync_file_path)) {
          $content = file_get_contents(self::$sync_file_path);
          update_option('cloudpress-sync-json', $content);
          self::$sync_data = json_decode($content, true);
        } else {
          self::$sync_data = json_decode(get_option('cloudpress-sync-json', '{}'), true);
          file_put_contents(self::$sync_file_path, json_encode(self::$sync_data));
        }

        $server = self::$sync_data['server'];
        $app_url = strpos($server, "localhost") === FALSE ? "http://app.".$server : "//".$server;

        if ($server !== "cloud-press.net") {
          add_filter( 'https_ssl_verify', '__return_false' );
        }

        global $syncUpdateCheck;
        $syncUpdateCheck = PucFactory::buildUpdateChecker(
            $app_url.'/updates/?action=get_metadata&slug=cloudpress-sync',
            __FILE__
        );


        //$update = $syncUpdateCheck->checkForUpdates();
        //if ($update) {
        //  self::update_notice();
        //  return;
        //}

        add_action( 'activated_plugin', array(__CLASS__, 'activate_redirect'));
        add_action('admin_menu', array(__CLASS__, 'cloudpress_page_action'));

        //add_action('save_post', array(__CLASS__, "cloudpress_set_changed_flag"));

        add_action( 'wp_ajax_cloudpress_pull', array(__CLASS__, "cloudpress_pull") );
        add_action( 'wp_ajax_cloudpress_push', array(__CLASS__, "cloudpress_push") );
        add_action( 'wp_ajax_cloudpress_rollback', array(__CLASS__, "cloudpress_rollback") );
        add_action( 'wp_ajax_cloudpress_rollback_first', array(__CLASS__, "cloudpress_rollback_first") );

    }

    static function cloudpress_reset_changed_flag() {
      file_put_contents(dirname(__FILE__)."/changed.js", "top.cp_sync_changed = false;");
    }

    static function cloudpress_set_changed_flag($post_id) {
      $post_type = get_post_type( $post_id );
      $post_status = get_post_status( $post_id );
      file_put_contents(dirname(__FILE__)."/test.txt", $post_id."##".$post_type."\r\n", FILE_APPEND);
      if (($post_type == "post" || $post_type == "page") && $post_status != "auto-draft"){
        file_put_contents(dirname(__FILE__)."/changed.js", "top.cp_sync_changed = true;");
      }
    }

    static function uploadFile($file, $post_fields = array()) {
      $boundary = wp_generate_password(24);
      $headers = array(
        'content-type' => 'multipart/form-data; boundary=' . $boundary
      );

      $payload = '';

      // First, add the standard POST fields:
      foreach ( $post_fields as $name => $value ) {
              $payload .= '--' . $boundary;
              $payload .= "\r\n";
              $payload .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
              $payload .= $value;
              $payload .= "\r\n";
      }

      $fileh = @fopen( $file, 'rb' );
      $file_size = filesize( $file );
      $file_data = fread( $fileh, $file_size );

      // Upload the file
      if ( $file ) {
              $payload .= '--' . $boundary;
              $payload .= "\r\n";
              $payload .= 'Content-Disposition: form-data; name="zip"; filename="' . basename( $file ) . '"' . "\r\n";
              $payload .= "\r\n";
              $payload .= $file_data;
              $payload .= "\r\n";
      }

      $payload .= '--' . $boundary . '--';

     
      return array(
        'headers' => $headers,
        'body' => $payload,
        'timeout' => 45
      );
   }

     static function response_ok($data) {
       return json_encode($data);
     }

     static function response_fail($error, $data = array()) {
        return json_encode(array("error" => $error, "data" => $data));
     }

     static function format_size($bytes){
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    static $remote_fs = array(
      "pull" => array("presetup"=> true, "replace" => true, "requirements" => true),
      "push" => array()
    );

    static function cloudpress_pull() {

        if (!current_user_can('manage_options')) {
            wp_die('Sorry, but you do not have permissions to sync.');
        }

        if (is_multisite()) {
            wp_die('Sorry, sync plugin does\'t work with multisites.');
        }
        
        /* Make sure post was from this page */
        if ( ! empty( $_POST ) && check_admin_referer( self::$menu_slug, 'cp_sync_nounce') ) {
          
          if (!isset($_POST['oneTimeToken']) || !$_POST['oneTimeToken']) {
            wp_die('no token provided');
          }


          @ini_set('max_execution_time', 300);
          @ini_set('memory_limit', '256M');
          @ini_set('opcache.enable', 0);
          @set_time_limit(0);


          require_once "inc/utils.php";

          $step = $_REQUEST['step'];
          self::$cpSync = new CPFS(!isset(self::$remote_fs['pull'][$step]));

           
          self::$cpSync->load_pull_id(($step == "presetup") ? true : false);

          if (method_exists(__CLASS__, "cloudpress_pull_step_".$step)){
            self::$cpSync->sync_log('begin '."cloudpress_pull_step_".$step."##");
            ob_clean();
            echo call_user_func(array(__CLASS__, "cloudpress_pull_step_".$step));
            exit();
          } else {
            die('invalid step '."cloudpress_pull_step_".$step);
          }
        } else {
          die('invalid request');
        }
    }
    
    static function cloudpress_pull_step_presetup() {
      if (!self::$cpSync->sync_folder_exists()) {
        return self::response_ok(array("error" => "Unable to create sync folder. Please manually create a folder named \".cloudpress-sync\" in the folder were \"wp-config.php\" is located."));
      }
      
      self::$cpSync->sync_log("cloudpress_pull_step_presetup");
    }

     static function cloudpress_pull_step_required() {
      self::$cpSync->sync_log("cloudpress_pull_step_required 1");
      self::$cpSync->sync_log("cloudpress_pull_step_required 2");
      self::$cpSync->sync_log("cloudpress_pull_step_required".self::$sync_data['project-url']);
      $oneTimeToken = $_POST['oneTimeToken'];

      $needs_sync = self::$cpSync->compare_remote_to_local(self::$sync_data['project-url'], $oneTimeToken);
      
      $sync_request_data = self::$cpSync->get_sync_request_data();

      $data = array();
      if (isset( $sync_request_data['data'])) {
        $data = $sync_request_data['data'];
      }

      if (isset($data['error'])) {
        return self::response_ok(array("needs_sync" => false, "error" => "Error : ".$data['error']));
      }

      $size = "Unkown";
      $raw_size = 0;
      if (isset( $data['size'])) {
        $raw_size = $data['size'];
        $size = self::format_size($data['size']);
      }

      $volumes = max(1, ceil($raw_size/CloudPressDump::volume_size()));
     
    
      global $wpdb;
      $table_prefix = $wpdb->prefix;

      $remote_table_prefix = "wp_";
      if (isset( $data['table_prefix'])) {
        $remote_table_prefix = $data['table_prefix'];
      }

      return self::response_ok(array("needs_sync" => $needs_sync, "download_size" => $size, "volumes" => $volumes));
     }

     static function cloudpress_pull_step_requirements() {
        $errors = self::$cpSync->check_requirements();
        $result = "";

        if (count($errors)) {
          $result = self::response_ok(array("can_sync" => false, "error" => json_encode($errors)));
        } else {
          $result = self::response_ok(array("can_sync" => true));
        }

        return $result;
     }

     static function cloudpress_pull_step_download_volume() {
        $oneTimeToken = $_POST['oneTimeToken'];
        $volume_id = $_POST['volume_id'];
        $zipUrl = self::$sync_data['project-url']."/wp-content/mu-plugins/cloudpress-sync/api2.php";
       
        $tmpfname = CloudPressDump::tempFile($zipUrl);

        self::$cpSync->sync_log('cloudpress_pull_step_download_volume##');
        self::$cpSync->sync_log('cloudpress_pull_step_download_volume pull_id##'.self::$cpSync->pull_id);
        $sync_request_data = self::$cpSync->get_sync_request_data();
    
        $data = array(
          "action" => "download_volume", 
          "oneTimeToken" => $oneTimeToken, 
          "volume_id" => $volume_id,
          "sync_request_data" => json_encode($sync_request_data)
        );

        self::$cpSync->sync_log('download_file start##'.$zipUrl."##".$tmpfname);

        $result = self::$cpSync->download_file($zipUrl, $tmpfname, 5000, $data);

        if (is_wp_error($result)) {
          return self::response_ok(array("error" => $result->get_error_message));
        }

        self::$cpSync->sync_log('download_file finished##'.$tmpfname);
        
        self::$cpSync->unzip_volume($tmpfname);

        self::$cpSync->sync_log('unzip_volume finished##');
        $next_volume = self::$cpSync->get_next_volume();

        self::$cpSync->sync_log('next_volume ##'.$next_volume);
        if (!$next_volume) {
          self::$cpSync->sync_log('join_files start##');
          self::$cpSync->join_files();
          self::$cpSync->sync_log('join_files end##');
        }

        return self::response_ok(array("success" => true, "next_volume" => $next_volume, "test" => self::$cpSync->pull_id/*, "zip" => self::$cpSync->get_sync_request_file(), "sync_request_data" => $sync_request_data*/));
     }
     
      static function cloudpress_pull_step_download() {
        $oneTimeToken = $_POST['oneTimeToken'];
        $zipUrl = self::$sync_data['project-url']."/wp-content/mu-plugins/cloudpress-sync/api2.php";
       
        $tmpfname = CloudPressDump::tempFile($zipUrl);

        $sync_request_data = self::$cpSync->get_sync_request_data();
    
        $data = array(
          "action" => "download", 
          "sync-source" => plugin_dir_url( __FILE__ ) . 'changed.js',
          "oneTimeToken" => $oneTimeToken, 
          "sync_request_data" => json_encode($sync_request_data)
        );

        $result = self::$cpSync->download_file($zipUrl, $tmpfname, 5000, $data);

        if (is_wp_error($result)) {
          return self::response_ok(array("error" => $result->get_error_message));
        }

        self::$cpSync->set_pull_value("download_file", $tmpfname);

        return self::response_ok(array("success" => true, "debug" => $tmpfname));
      }


     static function cloudpress_pull_step_backup() {
        if (self::$cpSync->should_backup_db()) {
          $result = self::$cpSync->backup_db();
        }

        //@set_time_limit(1);

        if (!is_wp_error($result)) {
          $result = self::$cpSync->backup_files();
        }

        if (!is_wp_error($result)) {
          return self::response_ok(array("success" => true));
        }

        return self::response_ok(array("error" => $result->get_error_message(), "data" => $result->get_error_data(), "success" => false));
     }

      static function cloudpress_pull_step_replace() {
        $sync_request_data = self::$cpSync->get_sync_request_data();
        
        $applyOptions = array("home", "siteurl", self::$pair_option_name);
        $activatePlugins = array("cloudpress-sync.php");

        $first_sync = self::$cpSync->first_sync;

        $result = self::$cpSync->apply_sync_data($sync_request_data, array(), $applyOptions, $activatePlugins);

        if (is_wp_error($result)) {
          return self::response_ok(array("success" => false, "error" => $result->get_error_message().":".$result->get_error_data()));
        } 

        self::$cpSync->save_pull_rollback($first_sync);

        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%postname%/');
        flush_rewrite_rules();

        return self::response_ok(array("success" => true));
      }

     static function cloudpress_push_step_required() {
        $oneTimeToken = $_POST['oneTimeToken'];

        $data = array();
        $needs_sync = self::$cpSync->compare_local_to_remote(self::$sync_data['project-url'], $oneTimeToken, $data);
        
        self::$cpSync->sync_log("get_sync_size data");
        self::$cpSync->sync_log(json_encode($data));
        
        if (isset($data['data']['error'])) {
          return self::response_ok(array("needs_sync" => false, "error" => "Error : ".$data['data']['error']));
        }

        $sync_size = CloudPressDump::get_sync_size($data);
        $size = "Unkown";
        if ($sync_size) {
          $size = self::format_size($sync_size);
        }

        return self::response_ok(array("needs_sync" => $needs_sync, "upload_size" => $size));
     }

     static function cloudpress_push_step_backup() {
        return self::response_ok(array("success" => false));
     }

     static function cloudpress_push_step_upload_volume() {
        $oneTimeToken = urlencode($_POST['oneTimeToken']);
        $volume_id = $_POST['volume_id'];
        
        $uploadUrl = self::$sync_data['project-url']."/wp-content/mu-plugins/cloudpress-sync/api2.php?action=upload_volume&oneTimeToken=$oneTimeToken&volume_id=$volume_id";
        $sync_request_data = self::$cpSync->get_sync_request_data();

        $data = array();

        $next_volume = 0;

        if ($volume_id == 0) {
          $sync_request_data = CloudPressDump::export_sync_data($sync_request_data, true);
          $sync_request_data = CloudPressDump::prepare_export_sync_data($sync_request_data);
    
          self::$cpSync->set_pull_value("sync_request_data_new", $sync_request_data);
        } else {
          $sync_request_data = self::$cpSync->get_pull_value("sync_request_data_new");
        }

        $zip = CloudPressDump::export_sync_data_volume($sync_request_data, $volume_id, $next_volume);

        $params = self::uploadFile($zip, $data);
        $result_arr = wp_remote_post( $uploadUrl,  $params);

        if ( is_wp_error( $result_arr ) ) {
          return self::response_ok(array("success" => false, "error" => $result_arr->get_error_message()));
        } else {
          if ($result_arr["body"]) {
            $result = json_decode($result_arr["body"], true);
            if (isset($result['error'])) {
              return self::response_ok(array("success" => false, "error" => $result['error']));
            }
          }
        }

        self::cloudpress_reset_changed_flag();

        return self::response_ok(array("success" => true, "file" => $zip, "next_volume" => $next_volume, "sync_request_data" => $sync_request_data));
     }

    static function can_rollback($has_many = false){
      $has_another = self::$cpSync->has_rollback();
      $has_first = self::$cpSync->has_first_rollback();

      if ($has_many) {
        if ($has_another !== FALSE && $has_first !== FALSE) {
          return true;
        }
      } else {
        if ($has_another !== FALSE || $has_first !== FALSE) {
          return true;
        }
      }
      return false;
    }

    static function rollback_time($first = false){
      return self::$cpSync->rollback_time($first);

    }

    static function cloudpress_rollback(){
      if (!current_user_can('manage_options')) {
          wp_die('Sorry, but you do not have permissions to sync.');
      }
      
      /* Make sure post was from this page */
      if ( ! empty( $_POST ) && check_admin_referer( self::$menu_slug, 'cp_sync_nounce') ) {
        
        if (!isset($_POST['oneTimeToken']) || !$_POST['oneTimeToken']) {
          die('no token provided');
        }

        require_once "inc/utils.php";

         
        self::$cpSync = new CPFS();
        self::$cpSync->load_pull_id();
        
        // if we don't have many backups, rollback first one//
        $has_another = self::$cpSync->get_rollback_ids();
        
        self::$cpSync->rollback($has_another === FALSE);
      }
    }

    static function cloudpress_rollback_first(){
      if (!current_user_can('manage_options')) {
        wp_die('Sorry, but you do not have permissions to sync.');
      }
      
      /* Make sure post was from this page */
      if ( ! empty( $_POST ) && check_admin_referer( self::$menu_slug, 'cp_sync_nounce') ) {
        
        if (!isset($_POST['oneTimeToken']) || !$_POST['oneTimeToken']) {
          die('no token provided');
        }

        require_once "inc/utils.php";

        self::$cpSync = new CPFS();
        self::$cpSync->load_pull_id();
        self::$cpSync->rollback(true);
      }
    }


    static function cloudpress_push() {

      if (!isset($_POST['oneTimeToken']) || !$_POST['oneTimeToken']) {
        die('no token provided');
      }

      /* Make sure post was from this page */
      if ( ! empty( $_POST ) && check_admin_referer( self::$menu_slug, 'cp_sync_nounce') ) {
          
          if (is_multisite()) {
            wp_die('Sorry, sync plugin does\'t work with multisites.');
          }
          
          if (!current_user_can('manage_options')) {
              wp_die('Sorry, but you do not have permissions to sync.');
          }

          @ini_set('max_execution_time', 300);
          @ini_set('memory_limit', '256M');
          @ini_set('opcache.enable', 0);
          @set_time_limit(0);
          
          require_once "inc/utils.php";

          $step = $_REQUEST['step'];
          self::$cpSync = new CPFS();
          self::$cpSync->load_pull_id(($step == "required") ? true : false);

          if (method_exists(__CLASS__, "cloudpress_push_step_".$step)) {
            ob_clean();
            echo call_user_func(array(__CLASS__, "cloudpress_push_step_".$step));
            die();
          } else {
            die('invalid step '."cloudpress_push_step_".$step);
          }
      }
    }


    static function enque_scripts() {
      $pair_url = self::$sync_data['project-url'];
      $pair_token = self::$sync_data['pair-token'];
      $pair_id = self::$sync_data['project-id'];
      $pair_name = self::$sync_data['project-name'];
      $server = self::$sync_data['server'];
      $app_url = strpos($server, "localhost") === FALSE ? "//app.".$server : "//".$server;

      add_thickbox();
      wp_enqueue_script( 'bluebird', plugin_dir_url( __FILE__ ) . '/public/bluebird.min.js?random='.time());
      wp_enqueue_script( 'cloudpress-sync', plugin_dir_url( __FILE__ ) . '/public/cloudpress-sync.js?random='.time());
      wp_enqueue_script( 'cloudpress-sync-token', $app_url."/getOneTimeToken?projectID=$pair_id&pairToken=$pair_token&random=".time()."&version=".self::$cpSync->version, array("bluebird", "cloudpress-sync"));
    }
    
    static function cloudpress_sync_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Sorry, but you do not have permissions to sync.');
        }
        global $syncUpdateCheck;
        $update = $syncUpdateCheck->checkForUpdates();
        if ($update) {
          self::update_notice();
          return;
        }

        
        if (!self::$sync_data || !isset(self::$sync_data['project-id'])) {
          wp_die('This site is not paired with one from cloudpress.');
        }

        
        require_once "inc/utils.php";
        self::$cpSync = new CPFS();

        $pair_url = self::$sync_data['project-url'];
        $pair_name = self::$sync_data['project-name'];
        $pair_token = self::$sync_data['pair-token'];
        $pair_id = self::$sync_data['project-id'];
        $server = self::$sync_data['server'];
        $app_url = strpos($server, "localhost") === FALSE ? "//app.".$server : "//".$server;

?>
  <script type="text/javascript">
    var cloudpress_server = "<?php echo $app_url; ?>";
    var cloudpress_nounce = "<?php echo wp_create_nonce( self::$menu_slug ); ?>";
    var auth_data = <?php echo json_encode(self::$cpSync->credentials); ?>;
  jQuery(document).ready(function() {
    jQuery("#cloudpress-sync-notification-close").unbind('click').click(function() {
      jQuery(this).parent().parent().hide();
    });
  });
  </script>
  <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700,900' rel='stylesheet' type='text/css'>
  
  <style type="text/css">
    #cloudpress-sync-installing,  #cloudpress-sync-installing2{
      width:100%;
      background-color:#F4F4F4;
      box-sizing:border-box;
      padding:36px 76px 0 46px;
      padding-bottom: 30px;
    }
    #cloudpress-sync-installing-logo, #cloudpress-sync-installing-logo2 {
      width:40px;
      height:40px;
      background-image:url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNXG14zYAAAAWdEVYdENyZWF0aW9uIFRpbWUAMDMvMDkvMTU7THe5AAADcElEQVRYhe3Yf2jUdRzH8cfdftzU8tc2tDCqVULDza3shzE0Uiq2tD8KKiJplX8ssrDoDynoh1T+EREY2SBEsB9IGIpzOgb5R8NCEpemoE3NiNTNzWpt7ba7XX98z23Ydtu4Ygvu9c/d8X1/Pp/nve79fn/f3wupTVyNzbjP5FIDngypTezB/RNNM4LqQmoTiYmmSKXwRAOMpgxgusoApqsMYLrKAKarSQ+YPa7oBGKIJ9+Hkjuk2iWeXNOfjM9Czn8BmDyoqJCS2UTC/N7H4XbOXkRuEmCoeph+JWUFzJ1CTz9H2jndloTM+rcA40yPsL6CR25kzpTBS6c72XKcNw8KXLqUNL1Ul/J8CWX5g/Hnuvm8hbcO0d49OuTogHHyp7K3kkWF7P2Z93+grYfimTxzM28sYmaEdfuJxhDj9bt4bRG/dPFcEwfaKMzj2WLWllJxFZX1XPhTyp889biVIBRjxwOsvJbqfWw5NrBy4PXtO1lXTvmXNJ/k1btZfxsbj/LyN0SjQ+ITVJeweQmftPBUI30hI5ZrasAoS67nqyo2HWNNI6b+0+FpEZbPY+dPPFzEF8vZdpJH65PuXH54lPeW8kIJpds5et6ILqZuMzEq5pAVYkMz8oaJyaarl51HuKOQbctoOs+qfYL8Gu6EBFtbCIeYFQk+j6Sx98FUc3eMeXPZXcmFKCvq6b1oTFV6eeGPDzA7cCOe4JVb0DNMTB+RLLbfS36Eql1MDVOz2GC/HIaqej6xftqH5ue4AXP5+gy7zlBTzKpS9AZQktUaCrNpGbcXsrKB71qpq+LDCj5YyrScZPylNX2sLmPNAj5t4cc2KZ0e/aEpzqw8Gldwa0GQ/O8epjNK8eyglSyczYv72djMjhVUXcP2UzxUxNnuoEc2nSM/j7UlPHgd37ZStYeOLimb3die6uLMiLBhMU/cxLQhG57q5J1DfHyQz1by2A3UNPHRAR4v46WFlA9p1B1Rtp4IoDv+Su3e2AEZuJ8umMv8GeSE+aOX79v5tQO53DOPnCwazgjyqpfpV1BeELjX18/x3zjRKnBtTEU0nufihCCX4gOrB4eFEKLBlxAxmPjDDQvZRi/fpMY3zYQEQ8FI8MNdyzImp0bSpJ8HM4DpKgOYrjKA6SoDmK7+F4B7JxoiherCeFrwl/9kUwNW/w14RAWEf1c13AAAAABJRU5ErkJggg==');
      float:left;
      margin-right: 20px;
    }
    #cloudpress-sync-installing-text,  #cloudpress-sync-normal-text, #cloudpress-sync-normal-text2 {
      margin: 0;
      float: left;
      text-transform: uppercase;
      font-weight: 900;
      font-size: 13px;
      font-family: Lato, sans-serif;
      letter-spacing: 2px;
      color: #333333;
    }
    #cloudpress-sync-normal-text, #cloudpress-sync-normal-text2 {
      line-height: 40px;
    } 
    #cloudpress-sync-installing-animation-wrapper {
      padding-left: 60px;
      margin-top: 29px;
      padding-top: 5px;
      padding-bottom: 5px;
    }
    #cloudpress-sync-installing-animation {
      width: 100%;
      height: 11px;
      background-color:#0099FF;
    }
    #cloudpress-sync-installing-animation .animate {
      animation: progress 2s linear infinite;
      -moz-animation: progress 2s linear infinite;
      -webkit-animation: progress 2s linear infinite;
      -ms-animation: progress 2s linear infinite;
      -o-animation: progress 2s linear infinite;
      -webkit-background-size: 10px 10px;
      background-size: 10px 10px;
      -moz-background-size: 10px 10px;
      display:block;
      height:100%;
      background-image: linear-gradient(135deg, rgba(255,255,255,0.35) 0%, rgba(255,255,255,0.35) 25%, rgba(255,255,255,0) 25%, rgba(255,255,255,0) 50%, rgba(255,255,255,0.35) 50%, rgba(255,255,255,0.35) 75%, rgba(255,255,255,0) 75%, rgba(255,255,255,0) 100%);
    }
    @-webkit-keyframes progress {
      from {
      background-position: -60px -60px;
      }
      to {
      background-position: 0 0;
      }
    }
    @-moz-keyframes progress {
      from {
      background-position: -60px -60px;
      }
      to {
      background-position: 0 0;
      }
    }
    @-ms-keyframes progress {
      from {
      background-position: -60px -60px;
      }
      to {
      background-position: 0 0;
      }
    }
    @-o-keyframes progress {
      from {
      background-position: -60px -60px;
      }
      to {
      background-position: 0 0;
      }
    }
    @keyframes progress {
      from {
      background-position: -60px -60px;
      }
      to {
      background-position: 0 0;
      }
    }
    #cloudpress-sync-notification-wrapper, #cloudpress-sync-notification-wrapper4, #cloudpress-sync-notification-wrapper5 {
      margin: 42px 76px 0px;
      box-sizing: border-box;
    }
    #cloudpress-sync-notification-wrapper2, #cloudpress-sync-notification-wrapper3 {
      width: 100%;
      background-color: #e3e3e3;
      box-sizing: border-box;
      padding: 0 76px 0 146px;
      position:relative;
    }
    #cloudpress-sync-notification, #cloudpress-sync-notification2, #cloudpress-sync-notification3, #cloudpress-sync-notification4, #cloudpress-sync-notification5 {
      width: 100%;
      padding-left:15px;
      height:52px;
      box-sizing: border-box;
      background-color: #dff0d8;
      border: 1px solid #d6e9c6;
      border-radius:4px;
      -webkit-border-radius:4px;
      -moz-border-radius:4px;
      font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
      font-size: 14px;
    }
    #cloudpress-sync-notification4, #cloudpress-sync-notification5 {
      background-color: #fcf8e3;
      border-color: #faebcc;
    }
    #cloudpress-sync-notification > p, #cloudpress-sync-notification2 > p, #cloudpress-sync-notification3 > p, #cloudpress-sync-notification4 > a, #cloudpress-sync-notification5 > p {
      margin: 0;
      float: left;
      line-height: 50px;
      color: #3c763d;
    }
    #cloudpress-sync-notification5 > p {
      color: #8a6d3b;
    }
    #cloudpress-sync-notification4 > a {
      cursor: pointer;
      color: #8a6d3b;
    }
    #cloudpress-sync-notification-close {
      font-size: 17px;
      font-weight: 700;
      line-height: 50px;
      color: #000;
      text-shadow: 0 1px 0 #fff;
      filter: alpha(opacity=20);
      opacity: .2;
      text-transform: none;
      font-family: inherit;
      -webkit-appearance: none;
      float: right;
      margin-right:20px;
    }
    #cloudpress-sync-notification-close:hover {
      color: #000;
      text-decoration: none;
      cursor: pointer;
      filter: alpha(opacity=50);
      opacity: .5;
    }
    #wpcontent {
      background:#ffffff;
      padding-left:0;
    }
    .cloudpress-sync-information {
      margin: 42px 76px 0;
      background-color: #F4F4F4;
      height: 207px;
      box-sizing: border-box;
      border:1px solid #EBEBEB;
      font-family:Lato, sans-serif;
      line-height:1;
      position:relative;
    }
    .cloudpress-sync-information-left {
      float: left;
      width: 70%;
      padding: 40px 30px 0;
      box-sizing: border-box;
    }
    .cloudpress-sync-information-right {
      float: right;
      width: 30%;
      text-align: center;
      height:100%;
      background-color:#E9E9E9;
      padding:47px 0 38px;
      box-sizing:border-box;
    }
    .cloudpress-sync-information h4, #cloudpress-sync-rollback h4 {
      text-transform: uppercase;
      font-size:13px;
      color: #333333;
      font-weight:900;
      margin:0;
      margin-bottom:36px;
      letter-spacing: 3px;
    }
    #cloudpress-sync-rollback h4 {
      margin-bottom:0;
      line-height:40px;
      margin-right: 30px;
    }
    .cloudpress-sync-information p, #cloudpress-sync-rollback p {
      font-size:15px;
      font-weight: 400;
      color: #333333;
      margin: 0;
    }
    #cloudpress-sync-rollback p {
      max-width:calc(100% - 200px);
    }
    .cloudpress-sync-information-right .button-primary{
      height:40px;
      text-transform: uppercase;
      box-sizing: border-box;
      font-size: 12px;
      font-family: Lato, sans-serif;
      margin-top: 38px;
      width: 191px;
    }
    #cloudpress-sync-rollback {
      font-family:Lato, sans-serif;
      line-height:1;
      margin: 42px 76px 0;
      padding:18px 32px;
      border:1px solid #EBEBEB;
      position:relative;
    }
    #cloudpress-sync-rollback > * {
      float:left;
    }
    #rollback-project, #rollback-first-project {
      height:40px;
      text-transform:uppercase;
      box-sizing: border-box;
      font-size: 12px;
      font-family: Lato, sans-serif;
      width: 200px;
      float:right;
      margin-top: 20px;
    }


    #cp_sync_log {
      margin: 42px 76px 0px;
      padding-left: 15px;
      box-sizing: border-box;
      background-color: #dff0d8;
      border: 1px solid #d6e9c6;
      border-radius: 4px;
      -webkit-border-radius: 4px;
      -moz-border-radius: 4px;
      font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
      font-size: 14px;
    }
    #cp_sync_log.empty {
      border: 0px;
    }
    #cp_sync_log li {
      margin-top: 6px;
      color: #3c763d;
    }
    #cloudpress-sync-popup-wrapper {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
    }
    #cloudpress-sync-popup-overlay {
      position:absolute;
      z-index: 10;
      left: 0px;
      right: 0;
      top: 0;
      bottom: 0;
      background: #f1f1f1;
      background: rgba(238,238,238,.9);
    }
    #cloudpress-sync-popup-content {
        background: #fff;
        -webkit-box-shadow: 0 1px 20px 5px rgba(0,0,0,.1);
        box-shadow: 0 1px 20px 5px rgba(0,0,0,.1);
        top: 3%;
        position: absolute;
        z-index: 11;
        width: 700px;
        left: 50%;
        margin-left: -350px;
        min-height: 550px;
    }
    #cloudpress-sync-popup-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 48px;
        border-bottom: 1px solid #ddd;
    }
    #cloudpress-sync-popup-header h1 {
      color: rgb(68, 68, 68);
      font-family: 'Open Sans', sans-serif;
      font-size: 22px;
      font-weight: 600;
      text-transform: uppercase;
      -webkit-font-smoothing: subpixel-antialiased;
      padding-left: 16px;
      padding-right: 16px;
      line-height: 48px;
      margin: 0;
    }
    #cloudpress-sync-popup-info {
        position: absolute;
        top: 49px;
        bottom: 49px;
        padding: 20px 50px 0 50px;
    }
    #cloudpress-sync-popup-info p {
        color: #555;
        font-size: 15px;
        font-weight: 400;
        line-height: 1.5;
        margin: 0 0 30px;
        font-family: 'Open Sans', sans-serif;
    }
    #cloudpress-sync-popup-info p.inside {
      margin-bottom: 10px;
      margin-left: 40px;
    }
    #cloudpress-sync-popup-actions {
      position:absolute;
      bottom:0;
      left:0;
      right:0;
      text-align: center;
      padding: 10px 25px 5px;
      background: #f3f3f3;
      border-top: 1px solid #eee;
    }
    #cloudpress-sync-popup-actions a {
        margin-right: 10px;
        margin-bottom: 5px;
        min-width: 75px;
    }
    .cloudpress-sync-general-overlay {
      position:absolute;
      background:#ffffff;
      display:none;
      opacity:0.7;
      top:0;
      left:0;
      right:0;
      bottom:0;
    }

    #progress-bg {
      position: relative;
      background-color: white;
      height: 18px;
    }

    #progress-value {
      background-color: #0099FF;
      height: 18px;
      width: 0%;
      position: absolute;
      -webkit-transition: width 2s; /* Safari */
      transition: width 2s;
    }

    #progress-percent {
      position: absolute;
      color:white;
       left:5px;
    }

    div#progress-message {
      margin: 10px 0px 0px 0px;
    }
  </style>

  <div id="cloudpress-sync-not-loggedin" style="display:none;">
  <div id="cloudpress-sync-installing2">
    <div id="cloudpress-sync-installing-logo2"></div>
    <h4 id="cloudpress-sync-normal-text2">CLOUDPRESS SYNC</h4>
    <div style="clear:both;"></div>
  </div>
  <div id="cloudpress-sync-notification-wrapper3">
    <div class="cloudpress-sync-general-overlay"></div>
    <div id="cloudpress-sync-notification3" style="background-color:#e3e3e3; padding: 0; border:0px">
      <p style="color:#333333;"><?php echo 'This site is currently linked to the following CloudPress project: <strong>'.$pair_name.'</strong> <a target="_blank" href="'. $pair_url.'">'. $pair_url.'</a>'; ?></p>
    </div>
  </div>
  <div id="cloudpress-sync-notification-wrapper4">
    <div id="cloudpress-sync-notification4">
       <a id="loggin-link" class="thickbox">You need to login to cloud-press.net</a>
      <div style="clear:both;"></div>
    </div>
  </div>
  </div>

  <div id="cloudpress-sync-loggedin" style="display:none;">



  <div id="cloudpress-sync-installing">
    <div id="cloudpress-sync-installing-logo"></div>
    <h4 id="cloudpress-sync-normal-text">CLOUDPRESS SYNC</h4>
    <h4 id="cloudpress-sync-installing-text" style="display:none;">Transferring site</h4>
    <div id="cloudpress-sync-installing-animation-wrapper" style="display:none;"> 
     <div id="progress-bg">
          <div id="progress-value"></div>
          <span id="progress-percent">0%</span>
      </div>
      <div id="progress-message"></div>
    </div>
    <!--<div id="cloudpress-sync-installing-animation-wrapper" style="display:none;"> 
      <div id="cloudpress-sync-installing-animation">
        <span class="animate"></span>
      </div>
    </div>-->
    <div style="clear:both;"></div>
  </div>

  <div id="cloudpress-sync-notification-wrapper2">
  <div class="cloudpress-sync-general-overlay"></div>
    <div id="cloudpress-sync-notification2" style="background-color:#e3e3e3; padding: 0; border:0px">
      <p style="color:#333333;"><?php echo 'This site is currently linked to the following CloudPress project: <strong>'.$pair_name.'</strong> <a target="_blank" href="'. $pair_url.'">'. $pair_url.'</a>'; ?></p>
    </div>
  </div>

  <ul id="cp_sync_log" class="empty">

    </ul>
  
  <?php
    if (!self::can_rollback()) {
    ?>
  <div id="cloudpress-sync-notification-wrapper5">
    <div id="cloudpress-sync-notification5">
      <p>Start transferring your CloudPress site by clicking on the <a href="javascript:void(0)" id="pull-project2" style="text-decoration: none; color: inherit; font-weight: bold;">"GET FROM CLOUDPRESS"</a> button.</p>
      <div style="clear:both;"></div>
    </div>
  </div>
    <?php } ?>


  <div id="cloudpress-sync-notification-wrapper" style="display:none;">
    <div id="cloudpress-sync-notification">
      <p><strong>The theme has been installed successfully.</strong> If you want to rollback to the previous version click on Rollback</p>
      <span id="cloudpress-sync-notification-close">x</span>
      <div style="clear:both;"></div>
    </div>
  </div>
  
  <div class="cloudpress-sync-information">
    <div class="cloudpress-sync-general-overlay"></div>
    <div class="cloudpress-sync-information-left">
      <h4>GET THE LATEST CHANGES FROM CLOUDPRESS</h4>
      <p>This operation will update your site with the latest changes in your CloudPress project. After this operation has finished your site will be identical to the site you have created on CloudPress. A backup will be automatically created for you in case you need to roll back to the current version of the site. To start this operation click the “Get from CloudPress” button.</p>
    </div>
    <div class="cloudpress-sync-information-right">
      <div>
        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAApCAYAAAB3LnrpAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNXG14zYAAAAWdEVYdENyZWF0aW9uIFRpbWUAMDMvMDkvMTU7THe5AAABc0lEQVRoge2XMXLCMBBFnzMuU+QItFRmGPXhJlD5GuEGoXbFUZyeJjcgF2DSuVSKyDPGkRNj7RKNR69cSct/g0HrzFqLBllFDTz3ym+2ZKPxeQ8aTf+DJBIbSSQ2kkhs5MaYvad+PJ1O5ztnGYUxZgHs+vUcePHsr4GzZqAAFngyz+bREhPJKp7ucWYIEZGs4gjUtwRze2t3NphgERdkCxSMlGkl3JmthEyQSEei5U+ZnkRLsMxkkaxiz7VES8F30NyzlvNTomXrek4i5Bs5AO8DawX+sEN1XK/D1DCTRWzJJ7BhWOZxZA3XY+N6TiLoNzJCZgzBEiDwrxUoIyIBQvfIRBkxCRC82W+UEZUA4VmrI3P5ZdsFYQlQGBpdwDXQeJYbYC0tAUrTry35AJZcyzTA0q2JozbG92RUJUD5faQjoyoB/nlIFG2BlvSGGBtJJDaSSGzMRmToHlkZY+4a5AZWvuKQyKtiEBVm82glkdiYjcgXeq1yWqtqsS4AAAAASUVORK5CYII="/>
      </div>
      <div>
        <input class="button-primary" type="submit" id="pull-project" value="Get from CloudPress">
      </div>
    </div>
    <div style="clear:both;"></div>
  </div>
  <div class="cloudpress-sync-information">
    <div class="cloudpress-sync-general-overlay"></div>
    <div class="cloudpress-sync-information-left">
      <h4>UPDATE THE CLOUDPRESS PROJECT WITH THE LATEST CHANGES ON THIS SITE</h4>
      <p>This operation will update your CloudPress project with the latest changes on this site. After this operation is completed your CloudPress site will look identical to this one. To start this operation click the “Send to CloudPress” button.</p>
    </div>
    <div class="cloudpress-sync-information-right">
      <div>
        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAApCAYAAAB3LnrpAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNXG14zYAAAAWdEVYdENyZWF0aW9uIFRpbWUAMDMvMDkvMTU7THe5AAABiElEQVRoge2YMW7CMBRAn6seAHVHQsrEhkQOwE3oxNwblBu0cyZ6kx6AgTB0aiWOUCTGSunQH8kFhziOjazIT2Lg+/+f/4QxFqqqKkKhCkbAl7zNqhXfoZ51F6qxsAce5LUP+aBgIqrgExhrobHEghBERBV8AJlhKZM173gXUQUbYHolZSo5XvEqIgMuLVKXvmW8iXSQqPEq40XEQaLGm4yaz+drQ3yz3W4PVg3cJXTeqhWPNol5nk/gMvceeDbkvwOHtqYtEj/Svy0Gf58MljITDDM7by1VsKZZ4giUhngpayaW0tOJPt+RV8zDHoEFcDKsnWTNJFNKTyecReTetOC/zBFYVCt2V+p2XMqUUud8F+t1ap3JtEpodbpMbwnwcPxqMlYSWl0t01sCzCdIZ2QQawmtrnNNE6Gv8TcjicRGEomNJBIbSSQ2BiPi5YrSwBMwOosF+6cxmIjPe5QNg9laSSQ2kkhsJJHYaPodmeV5ftNBOjAzBZtEXgIOEoTBbK0kEhuDEfkFDMxsWJsrSwYAAAAASUVORK5CYII="/>
      </div>
      <div>
        <input class="button-primary" type="submit" id="push-project" value="Send to CloudPress">
      </div>
    </div>
    <div style="clear:both;"></div>
  </div>
    
    
    <?php
    if (self::can_rollback()) {
      $timestamp = self::rollback_time();
    }
  ?>
    <div id="cloudpress-sync-rollback" <?php if (!self::can_rollback()) { echo 'class="disabled"'; } ?>>
        <div class="cloudpress-sync-general-overlay" <?php if (!self::can_rollback()) { echo 'style="display:block;"'; } ?>></div>
        <h4>RESTORE FROM LAST BACKUP</h4>
        <input class="button" type="submit" id="rollback-project" value="Restore from last backup">
        <div style="clear:both; float: none;"></div>
        <p>A backup of your site has been created on <?php if (self::can_rollback()) { echo date('l jS \of F Y - h:i:s A', $timestamp); } else { echo 'NEVER'; }?>. To restore the site to the previous version click the “Restore from backup” button.</p>
        <div style="clear:both; float: none;"></div>
    </div>

   <?php
      if (self::can_rollback(true)) {
        $timestamp = self::rollback_time(true);
   ?>
    <div id="cloudpress-sync-rollback">
        <div class="cloudpress-sync-general-overlay"></div>
        <h4>RESTORE FIRST BACKUP</h4>
        <input class="button" type="submit" id="rollback-first-project" value="Restore first backup">
        <div style="clear:both; float: none;"></div>
        <p>The first backup of your site has been created on <?php if (self::can_rollback(true)) { echo date('l jS \of F Y - h:i:s A', $timestamp); } else { echo 'NEVER'; }?>. To restore the site to the first version click the “Restore from first backup” button.</p>
        <div style="clear:both; float: none;"></div>
    </div>
    <?php
      }
   ?>
  </div>
  <div id="cloudpress-sync-popup-wrapper" style="display:none;">
  <div id="cloudpress-sync-popup-overlay"></div>
  <div id="cloudpress-sync-popup-content">
    <div id="cloudpress-sync-popup-header">
      <h1>A few things you need to know</h1>
    </div>
    <div id="cloudpress-sync-popup-info">
      <p>- <strong>This operation will completely replace the content you currently have in this site.</strong> After this operation this site will be identical to the site you have created on CloudPress. It is recommended that you do all the work and testing on your CloudPress site and when its ready you just update your live site using this operation</p>
      <p>- <strong>A backup of your current site will be created automatically.</strong> You can go back to the previous version of the site by using the “Rollback” section that will appear after this operation is completed</p>
      <p style="margin-bottom:15px;">- <strong>If you have content in this site that you want to keep</strong> here is what you have to do:</p>
      <p class="inside">- Export all your content using the WP export tool. </p>
      <p class="inside">- Import your content into the CloudPress site. </p>
      <p class="inside">- Make sure everything looks good with the new content. </p>
      <p class="inside">- Click "GET FROM CLOUDPRESS" again. </p>
      <p>By transfering your site you agree to the <a href="http://www.cloud-press.net/cloudpress-end-user-license-agreement/" target="_blank">CloudPress End User License Agreement</a></p>
    </div>
    <div id="cloudpress-sync-popup-actions">
      <a href="javascript:void(0)" id="pull-project-ok" class="button button-primary">OK</a>
      <a href="javascript:void(0)" id="pull-project-cancel" class="button button-secondary">Cancel</a>
    </div>
  </div>
  </div>
  
    <?php
    self::enque_scripts();
  }

    static function cloudpress_page_action() {
      add_management_page(self::$page_title, self::$page_title, self::$capability, self::$menu_slug, array(__CLASS__, 'cloudpress_sync_page'));
    }
}

CloudPressSync::init();
