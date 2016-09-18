<?php

require_once("inc/utils.php");

class CloudPressApi {
  private static $ONE_TIME_TOKEN = "cloudpress_one_time_token";
  private $wp_root;

  function __construct() {
    $this->loadWP();
  }

  private function loadWP() {
    ini_set('opcache.enable', 0);
    
    //find Wordpress root
    $wp_root = dirname($_SERVER['SCRIPT_FILENAME']);
    while (!file_exists($wp_root . '/wp-config.php') && !($wp_root === '/')) {
      $wp_root = dirname($wp_root);
    }
    if ($wp_root === '/') {
      exit;
    }
    require_once($wp_root . '/wp-load.php');

    $this->wp_root = $wp_root;

    if (isset($_REQUEST['action'])) {

      if (!$this->api_canCallApi()) {
        $this->fail();
        exit;
      }

      $action = $_REQUEST['action'];
      $params = array();
      if (isset($_REQUEST['params'])) {
        $params = json_decode($_REQUEST['params']);
      }

      $params = $_REQUEST;

      $fctName = 'api_'.$action;
      if (method_exists($this, $fctName)) {
        echo $this->success(call_user_func(array($this, $fctName), $params));
      } else {
        $this->fail();
      }
    }
  }

  function success($result) {
    return json_encode(array("code" => "200", "msg" => "Success", "result" => $result));
  }

  function fail($error_code = "503", $error_msg = "Invalid API request.") {
    die(json_encode(array("code" => $error_code, "msg" => $error_msg, "result" => array())));
  }
  
  function api_canCallApi(){
    try {
      return (is_user_logged_in() && current_user_can('edit_themes')) || (isset($_REQUEST['oneTimeToken']) && get_transient(self::$ONE_TIME_TOKEN) === $_REQUEST['oneTimeToken']);
    } catch (Exception $e) {
         
    }
    return false;
  }

  function api_getOneTimeToken(){
    $token = wp_generate_password(50, false);
    set_transient(self::$ONE_TIME_TOKEN, $token, 200);
    return $token;
  }

  function api_dumpData($params){
    $params = shortcode_atts( array(
        'exportedData' => 'all'
    ), $params );

    if ($params['exportedData'] == "all") {

    }

    $args = array(
      'content' => 'all',
    );
    ob_start();
    $wxr = export_wp($args);
    header('Content-Description:');
    header('Content-Disposition:');
    header('Content-Type:');
    $wxr = ob_get_contents();
    ob_end_clean();
    echo $wxr;
    exit;
  }

  private function codeToMessage($code)
    {
      switch ($code) {
          case UPLOAD_ERR_INI_SIZE:
              $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
              break;
          case UPLOAD_ERR_FORM_SIZE:
              $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
              break;
          case UPLOAD_ERR_PARTIAL:
              $message = "The uploaded file was only partially uploaded";
              break;
          case UPLOAD_ERR_NO_FILE:
              $message = "No file was uploaded";
              break;
          case UPLOAD_ERR_NO_TMP_DIR:
              $message = "Missing a temporary folder";
              break;
          case UPLOAD_ERR_CANT_WRITE:
              $message = "Failed to write file to disk";
              break;
          case UPLOAD_ERR_EXTENSION:
              $message = "File upload stopped by extension";
              break;

          default:
              $message = "Unknown upload error";
              break;
      }
      return $message;
  }

  function api_upload(){
    if ($_FILES['zip']['error'] === UPLOAD_ERR_OK) {
      $zip = $_FILES["zip"]["tmp_name"];
      $applyOptions = array("home", "siteurl");
      $cpSync = new CPFS();
      $cpSync->restore_from_zip($zip, array('mu-plugins'), $applyOptions, array(), true);
    } else {
      echo $this->codeToMessage($_FILES['zip']['error']);
    }
    exit;
  }

  function api_upload_volume(){
    $volume_id = $_REQUEST['volume_id'];
    if ($_FILES['zip']['error'] === UPLOAD_ERR_OK) {
      $zip = $_FILES["zip"]["tmp_name"];
      
      $cpSync = new CPFS();
      if (!$volume_id) {
        $cpSync->clean_push_folder();
      }
      $cpSync->unzip_volume($zip);

      $sync_request_data = $cpSync->get_sync_request_metadata();
      $next_volume = $cpSync->get_next_volume();

      $cpSync->sync_log("next_volume :: $next_volume");
      
      //echo "next_volume:".$next_volume;

      if (!$next_volume) {
        $cpSync->sync_log("apply_sync_data");

        $applyOptions = array("home", "siteurl");

        $cpSync->sync_log(json_encode($sync_request_data));

        $cpSync->join_files($sync_request_data);
        $cpSync->apply_sync_data($sync_request_data, array('mu-plugins'), $applyOptions, array(), true);
        $cpSync->clean_push_folder();

        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%postname%/');
        flush_rewrite_rules();
      }

    } else {
      echo $this->codeToMessage($_FILES['zip']['error']);
    }
    exit;
  }

  function api_download_volume(){
    $volume_id = $_REQUEST['volume_id'];
    
    if (isset($_REQUEST['sync_request_data'])) {
      $sync_request = json_decode(stripslashes($_REQUEST['sync_request_data']), true);
    }

    $next_volume = 0;
    $zip = CloudPressDump::export_sync_data_volume($sync_request, $volume_id, $next_volume);

    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Transfer-Encoding: binary');
    header('Content-Description: File Transfer');
    header('Content-Encoding: application/zip');
    header('Content-type: application/octet-stream');
    header('Content-Length: ' . filesize($zip));
    header('Content-Disposition: attachment; filename="file.zip"');
    ob_clean();
    readfile($zip);
    unlink($zip);
    exit;
  }
  
  
    
  function api_download(){
    if (isset($_REQUEST['sync-source'])) {
      $sync_source = $_REQUEST['sync-source'];
      file_put_contents(dirname(__FILE__)."/../../../.sync_source", $sync_source);
    }

    if (isset($_REQUEST['sync_request_data'])) {
      $sync_request = json_decode(stripslashes($_REQUEST['sync_request_data']), true);
    }

    $zip = "";
    if (isset($sync_request['data'])) {
      $zip = $sync_request['data']['zip'];
    }

    if (!$zip || !file_exists($zip)) {
      $zip = CloudPressDump::export_sync_data($sync_request);
    }

    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Transfer-Encoding: binary');
    header('Content-Description: File Transfer');
    header('Content-Encoding: application/zip');
    header('Content-type: application/octet-stream');
    header('Content-Length: ' . filesize($zip));
    header('Content-Disposition: attachment; filename="file.zip"');
    ob_clean();
    readfile($zip);
    unlink($zip);
    exit;
  }

  function api_compare_local_to_remote(){
    if (!isset($_REQUEST['version'])) {
      die('Incompatible version');
    }

   

    $hash = json_decode(stripslashes($_REQUEST['hashes']), true);
   
    $cpSync = new CPFS();

    if ($_REQUEST['version'] !== $cpSync->version) {
      die('Incompatible version');
    }

    $cpSync->sync_log("api_compare_local_to_remote hashes");
    $cpSync->sync_log(json_encode($hash));

    $zip = CloudPressDump::get_sync_metadata_zip($hash, false);
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Transfer-Encoding: binary');
    header('Content-Description: File Transfer');
    header('Content-Encoding: application/zip');
    header('Content-type: application/octet-stream');
    header('Content-Length: ' . filesize($zip));
    header('Content-Disposition: attachment; filename="file.zip"');
    ob_clean();
    readfile($zip);
    unlink($zip);
    exit;
  }

  function api_compare_remote_to_local(){
    if (!isset($_REQUEST['version'])) {
      die('Incompatible version');
    }


    
    $local_hash = json_decode(stripslashes($_REQUEST['hashes']), true);
    
    $cpSync = new CPFS();

    if ($_REQUEST['version'] !== $cpSync->version) {
      die('Incompatible version');
    }
    
    $cpSync->sync_log("api_compare_remote_to_local local_hash");
    $cpSync->sync_log(json_encode($local_hash));
    $cpSync->sync_log("------------------------");
    try {   
      $zip = CloudPressDump::get_sync_metadata_zip($local_hash);
  } catch (Exception $e) {

      $cpSync->sync_log( 'Caught exception: '. $e->getMessage().'##'.$e->getLine()."##".$e->getTraceAsString());
      die( 'Caught exception: '.  $e->getMessage());
  }
    //echo $zip;
    $cpSync->sync_log("zip=".$zip);
    $cpSync->sync_log("zip=".filesize($zip));

    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Transfer-Encoding: binary');
    header('Content-Description: File Transfer');
    header('Content-Encoding: application/zip');
    header('Content-type: application/octet-stream');
    header('Content-Length: ' . filesize($zip));
    header('Content-Disposition: attachment; filename="file.zip"');
    ob_clean();
    readfile($zip);
    unlink($zip);
    exit;
  }

  function api_syncHashes() {
    $hash = json_decode(stripslashes($_REQUEST['hashes']), true); 
    return CPSyncUtils::hashes();
  }

  function api_diff() {
    $hash1 = CPSyncUtils::hashes();
    $hash2 = isset($_REQUEST['hashes']) ? json_decode($_REQUEST['hashes'], true) : $hash1;
    return CPSyncUtils::diff2Hashes($hash1, $hash2);
  }

  function api_backup(){
    $cpSync = new CPFS();
    return $cpSync->backup_db();
  }
 function api_cleanup($remove_apps = false) {
        if ($remove_apps) {
            CloudPressApps::remove_all();
        }
        
        global $wpdb;
        
        $cleanup_revs = 'DELETE a,b,c
            FROM wp_posts a
            LEFT JOIN wp_term_relationships b ON (a.ID = b.object_id)
            LEFT JOIN wp_postmeta c ON (a.ID = c.post_id)
            WHERE a.post_type = "revision"';
        
        $results = $wpdb->query($cleanup_revs, OBJECT);
        
        function delete_expired_db_transients() {
            global $wpdb;
            $deletescount = 0;
            $expired = $wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient%';");
            
            if (count($expired) > 0) {
                foreach ($expired as $transient) {
                    echo '<ol>';
                    $key = str_replace('_transient_', '', $transient);
                    if (delete_transient($key)) {
                        echo '<li>Deleted:' . $key . '</li>';
                        $deletescount = $deletescount + 1;
                    }
                    echo '</ol>';
                }
            } 
            else {
                echo 'No expired transients found.';
            }
            
            return $deletescount;
        }
        
        function delete_expired_db_site_transients() {
            global $wpdb;
            $deletescount = 0;
           
            $site_transients_table = $wpdb->options;
            $keycol = 'option_name';
            $valcol = 'option_value';
          
            
            $expired = $wpdb->get_col("SELECT $keycol FROM $site_transients_table WHERE $keycol LIKE '_site_transient%';");
            
            if (count($expired) > 0) {
                foreach ($expired as $transient) {
                    echo '<ol>';
                    $key = str_replace('_site_transient_', '', $transient);
                    if (delete_site_transient($key)) {
                        echo '<li>Deleted:' . $key . '</li>';
                        $deletescount = $deletescount + 1;
                    }
                    echo '</ol>';
                }
            } 
            else {
                echo 'No expired transients found.';
            }
            
            return $deletescount;
        }
        
        $deletescount = delete_expired_db_site_transients();
        $deletescount = delete_expired_db_transients();

        $wpdb->query("DELETE FROM wp_posts WHERE post_status = 'auto-draft';", OBJECT);
        $wpdb->query("OPTIMIZE TABLE `wp_commentmeta`, `wp_comments`, `wp_links`, `wp_options`, `wp_postmeta`, `wp_posts`, `wp_terms`, `wp_term_relationships`, `wp_term_taxonomy`, `wp_usermeta`, `wp_users`", OBJECT);
    }
    
    function api_export() {

        $remove_apps = isset($_REQUEST['removeapps']) && $_REQUEST['removeapps'];
        $this->api_cleanup($remove_apps);

        $file = CloudPressDump::tempFile('export', '.sql');;
        $tables = CloudPressDump::backupTablesToFile($file, true, true);
        
        if (file_exists($file)) {
            $fileContents = file_get_contents($file);
        } else {
            $fileContents = "";
        }
        
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        
        for ($i = 0; $i < count($tables); $i++) {
            $table_name = $tables[$i];
            $new_table_name = preg_replace("/^" . $table_prefix . "/i", '%cloudpress-variable-tableprefix%', $table_name);
            $fileContents = str_replace($table_name, $new_table_name, $fileContents);
        }
        
        if ($fileContents) {
            file_put_contents($file, $fileContents);
        }

        $zipFile = CloudPressDump::tempFile('data_archive', '.zip');
        $zip = new ZipArchive;
        $zip->open($zipFile, ZipArchive::CREATE);

        $full_paths = CloudPressDump::full_paths(false);

        foreach ($full_paths as $path => $abs_path) {
          //CloudPressDump::addToZip($abs_path, $zip, $path."/");
        }

        $upload_obj = wp_upload_dir();
        $upload_root = $upload_obj['basedir'];

        CloudPressDump::addToZip($upload_root, $zip, "uploads/", array("/uploads.zip/"));
        CloudPressDump::addToZip(get_template_directory(), $zip, "theme/". get_template()."/", array("/blank-templates/", "/js/"));
        

        $zip->addFile($file, "theme/". get_template()."/data.sql");
        $zip->addFile(get_template_directory()."/cloudpress-screenshot.png", "thumb.png");
        $zip->close();

        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Transfer-Encoding: binary');
        header('Content-Description: File Transfer');
        header('Content-Encoding: application/zip');
        header('Content-type: application/octet-stream');
        header('Content-Length: ' . filesize($zipFile));
        header('Content-Disposition: attachment; filename="data.zip"');

        ob_clean();
        readfile($zipFile);
        unlink($file);
        exit;
    }
    
    
  function api_rollback(){
    $cpSync = new CPFS();
    $backup_id = $_REQUEST['backupid'];
    return $cpSync->rollback_db($backup_id);
  }
}

$cpApi = new CloudPressApi();

?>