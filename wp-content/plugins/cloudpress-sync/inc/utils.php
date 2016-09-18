<?php
include_once ("Ifsnop/Mysqldump/Mysqldump.php");

class CloudPressDump
{
    public static $cpSync;
    
    public static function zipFolder($source, $destination, $rootFolder, $exclude) {
        $zip = new ZipArchive;
        $zip->open($destination, ZipArchive::CREATE);
        self::addToZip($source, $zip, "", $exclude);
    }
    
    public static function addToZip($dir, $zipArchive, $zipdir = '', $exclude = array('/.bak$/i')) {
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != "..") {
                        $skip = false;
                        foreach ($exclude as $item) {
                            if (preg_match($item, $file)) {
                                $skip = true;
                                break;
                            }
                        }
                        if (!$skip) {
                            if (is_file($dir . "/" . $file)) {
                                $zipArchive->addFile($dir . "/" . $file, $zipdir . $file);
                            } 
                            else {
                                self::addToZip($dir . "/" . $file . "/", $zipArchive, $zipdir . $file . "/", $exclude);
                            }
                        }
                    }
                }
            }
        }
    }
    
    public static function get_temp_dir() {
        if (!is_dir(ABSPATH . "/" . CPFS::$sync_folder)) {
            mkdir(ABSPATH . "/" . CPFS::$sync_folder);
        }

        $temp_dir = ABSPATH . "/" . CPFS::$sync_folder. "/tempdata";
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir);
        }
        return $temp_dir;
    }

    public static function tempFile($prefix = "cloudpress", $ext = ".tmp") {
        return tempnam(self::get_temp_dir(), $prefix) . $ext;
    }
    
    public static function getTables($prefix = "", $postfix = "") {
        global $wpdb;
        $sql = "SHOW TABLES LIKE '$prefix" . $wpdb->prefix . "%$postfix'";
        $results = $wpdb->get_results($sql);
        $tables = array();
        foreach ($results as $index => $value) {
            foreach ($value as $tableName) {
                array_push($tables, $tableName);
            }
        }
        return $tables;
    }
    
    public static function getTablesLike($str) {
        global $wpdb;
        $sql = "SHOW TABLES LIKE '" . $str . "';";
        $results = $wpdb->get_results($sql);
        $tables = array();
        foreach ($results as $index => $value) {
            foreach ($value as $tableName) {
                array_push($tables, $tableName);
            }
        }
        return $tables;
    }
    
    public static function getDiff($hash2, $remote_to_local) {
        $hash1 = CPSyncUtils::hashes($remote_to_local);
        $hash2 = $hash2;
        if ($remote_to_local) {
            $diff = CPSyncUtils::diff2Hashes($hash2, $hash1, array('files' => array()));
        } 
        else {
            $diff = CPSyncUtils::diff2Hashes($hash1, $hash2, array('files' => array('/mu-plugins/')));
        }
        return $diff;
    }
    
    public static function get_db_patch($diff_tables) {
        $new_tables = array();
        $delete_tables = array();
        
        foreach ($diff_tables as $table => $action) {
            if ($action == "insert" || $action == "update") {
                array_push($new_tables, $table);
            }
            if ($action == "delete") {
                array_push($delete_tables, $table);
            }
        }
        
        return array("add" => $new_tables, "delete" => $delete_tables);
    }
    
    public static function get_files_patch($diff_files) {
        $new_files = array();
        $delete_files = array();
        
        foreach ($diff_files as $folder => $files) {
            foreach ($files as $file => $action) {
                if ($action == "insert" || $action == "update") {
                    array_push($new_files, array("folder" => $folder, "file" => $file));
                }
                if ($action == "delete") {
                    array_push($delete_files, array("folder" => $folder, "file" => $file));
                }
            }
        }
        
        return array("add" => $new_files, "delete" => $delete_files);
    }
    
    public static function foldersToExport() {
        $upload_obj = wp_upload_dir();
        $upload_root = $upload_obj['basedir'];
        $themes_root = WP_CONTENT_DIR . '/themes';
        $plugins_root = WP_CONTENT_DIR . '/plugins';
        $muplugins_root = WPMU_PLUGIN_DIR;
        
        $themes_exclude = array('/save-history/i');
        $plugins_exclude = array('/cloudpress-sync/i');
        $muplugins_exclude = array('/cloudpress-sync|cloudpress_page|force-strong-passwords|login_attempts|themebuilder|navmenu|wordpress-importer|preview_access\.php|token_auth\.php|powerslider_interface/i');
        
        $dirs = array();
        $dirs[$upload_root] = array("folder" => "uploads", "exclude" => array());
        $dirs[$themes_root] = array("folder" => "themes", "exclude" => $themes_exclude);
        $dirs[$plugins_root] = array("folder" => "plugins", "exclude" => $plugins_exclude);
        
        $dirs[$muplugins_root] = array("folder" => "mu-plugins", "exclude" => $muplugins_exclude);
        
        return $dirs;
    }
    
    public static function full_paths($include_temp = false, $filesystem = false) {
        if (!$filesystem) {
            $upload_obj = wp_upload_dir();
            $upload_root = $upload_obj['basedir'];
            $content_path = WP_CONTENT_DIR;
            $content_path = untrailingslashit($content_path);
            $themes_root = $content_path . '/themes';
            $plugins_root = $content_path . '/plugins';
            $muplugins_root = WPMU_PLUGIN_DIR;
            
            if (!$upload_root || strlen($upload_root) < 4) {
                $upload_root = $content_path . '/uploads';
            }
            
            if (!$muplugins_root || strlen($muplugins_root) < 4) {
                $muplugins_root = $content_path . '/mu-plugins';
            }
        } 
        else {
            global $wp_filesystem;
            $content_path = $wp_filesystem->wp_content_dir();
            $content_path = untrailingslashit($content_path);
            $upload_root = $content_path . '/uploads';
            $themes_root = $content_path . '/themes';
            $plugins_root = $content_path . '/plugins';
            $muplugins_root = $content_path . '/mu-plugins';
        }
        
        $dirs = array();
        $dirs["uploads"] = $upload_root;
        $dirs["themes"] = $themes_root;
        $dirs["plugins"] = $plugins_root;
        $dirs["mu-plugins"] = $muplugins_root;
        
        if ($include_temp) {
            $dirs["temp"] = self::get_temp_dir();
        }
        
        return $dirs;
    }
    
    public static function remove_table_prefix($tables) {
        global $wpdb;
        $prefix = $wpdb->prefix;
        $replaced = array();
        
        for ($i = 0; $i < count($tables); $i++) {
            array_push($replaced, preg_replace("/^" . $prefix . "/i", '', $tables[$i]));
        }
        
        return $replaced;
    }
    
    public static function tablesToExport() {
        global $wpdb;
        $tables = $wpdb->tables();
        $allTables = self::getTables();
        
        $exportedTables = array();
        
        global $exportedTablesObj;
        $exportedTablesObj = array();
        
        $skipedTables = array();
        foreach ($tables as $name => $table) {
            if ($name == "comments" || $name == "commentmeta" || $name == "users" || $name == "usermeta") {
                array_push($skipedTables, strtolower($table));
            }
            $exportedTablesObj[$table] = $name;
        }
        
        $exportedTables = array_values(array_udiff($allTables, $skipedTables, 'strcasecmp'));
        
        return $exportedTables;
    }
    
    public static $sync_variables = array('siteurl', 'home');
    
    public static function get_sync_metadata($hash = array(), $remote_to_local = true) {
        $diff = self::getDiff($hash, $remote_to_local);
        $files_patch = CloudPressDump::get_files_patch($diff['files']);
        $db_patch = CloudPressDump::get_db_patch($diff['tables']);
        $sync_request = array();
        $sync_request['files_patch'] = $files_patch;
        $sync_request['db_patch'] = $db_patch;
        
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        
        $sync_data = array("table_prefix" => $table_prefix);
        
        $sync_request['data'] = $sync_data;
        
        return $sync_request;
    }
    
    public static function get_sync_metadata_zip($hash = array(), $remote_to_local = true) {
        
        $debug_data = array("remote_to_local" => $remote_to_local, "passed-hash" => $hash, "local-hash" => CPSyncUtils::hashes());
        
        $zipFile = self::tempFile('sync_data_zip', '.zip');
        
        $zip = new ZipArchive;
        $zip->open($zipFile, ZipArchive::CREATE);
        
        $sync_request = self::get_sync_metadata($hash, $remote_to_local);
        
        $cpSync = new CPFS();
        $cpSync->sync_log("get_sync_metadata_zip sync_request1");
        $cpSync->sync_log(json_encode($sync_request));
        
        if ($remote_to_local) {
            $sync_request = self::export_sync_data($sync_request, !$remote_to_local);
            $cpSync->sync_log("get_sync_metadata_zip sync_request2");
            $cpSync->sync_log(json_encode($sync_request));
            
            $sync_request = self::prepare_export_sync_data($sync_request);
            $cpSync->sync_log("get_sync_metadata_zip sync_request3");
            $cpSync->sync_log(json_encode($sync_request));
            
            $sync_size = CloudPressDump::get_sync_size($sync_request);
            $sync_request['data']['size'] = $sync_size;
        }
        
        $sync_request_file = CloudPressDump::tempFile("sync_request");
        file_put_contents($sync_request_file, json_encode($sync_request));
        
        $debug_file = CloudPressDump::tempFile("debug_file");
        file_put_contents($debug_file, json_encode($debug_data));
        
        $zip->addFile($sync_request_file, "sync_request.json");
        $zip->addFile($debug_file, "debug_file.json");
        $zip->close();
        
        return $zipFile;
    }
    
    public static function join_files($files, $destination = "", $remove = false) {
        if ($destination) {
            $destinationFolder = dirname($destination);
            global $wp_filesystem;
            
            if (!$wp_filesystem->is_dir($destinationFolder)) {
                $wp_filesystem->mkdir($destinationFolder);
            }
            
            if (!$wp_filesystem->is_dir($destinationFolder)) {
                wp_mkdir_p($destinationFolder);
            }
        }
        
        $joined_file = self::tempFile('joined_file', '.zip');
        $fp = fopen($joined_file, 'wb+');
        for ($i = 0; $i < count($files); $i++) {
            $volume_file = fopen($files[$i], 'rb');
            fwrite($fp, fread($volume_file, filesize($files[$i])));
            fclose($volume_file);
            if ($remove) {
                unlink($files[$i]);
            }
        }
        fclose($fp);
        $wp_filesystem->copy($joined_file, $destination);
        return $joined_file;
    }
    
    public static function volume_size() {
        return 1 * 1024 * 1024;
    }
    
    public static function split_file($file, $tmp = false) {
        $volumes = array();
        
        $split_file = $tmp ? self::tempFile('split', '.tmp') : $file;
        
        $seek_start = 0;
        $fp = fopen($file, 'rb');
        if (!$fp) return $volumes;
        
        //seek to start of missing part
        fseek($fp, $seek_start);
        
        $volume = 0;
        
        //start buffered download
        while (!feof($fp)) {
            $volume++;
            
            //reset time limit for big files
            set_time_limit(0);
            $volume_file = fopen($split_file . "._volume_" . $volume . "", 'wb+');
            array_push($volumes, $split_file . "._volume_" . $volume . "");
            fwrite($volume_file, fread($fp, self::volume_size()));
            fclose($volume_file);
        }
        
        fclose($fp);
        
        return $volumes;
    }
    
    public static function get_sync_size($sync_request_metadata = array()) {
        
        $full_paths = self::full_paths(true);
        $size = 0;
        $files_patch = $sync_request_metadata['files_patch'];
        foreach ($files_patch['add'] as $fileObj) {
            if (isset($fileObj['source-folder'])) {
                $path = $full_paths[$fileObj['source-folder']] . $fileObj['source-file'];
            } 
            else {
                $path = $full_paths[$fileObj['folder']] . $fileObj['file'];
            }
            if (is_file($path)) {
                $size+= filesize($path);
            }
        }
        return $size;
    }
    
    public static function prepare_export_sync_data($sync_request_metadata = array()) {
        $full_paths = self::full_paths(true);
        $files_patch = & $sync_request_metadata['files_patch'];
        $add = array();
        for ($i = (count($files_patch['add'])) - 1; $i >= 0; $i--) {
            $fileObj = $files_patch['add'][$i];
            $path = $full_paths[$fileObj['folder']] . $fileObj['file'];
            if (is_file($path)) {
                $size = filesize($path);
                if ($size > self::volume_size()) {
                    $files = self::split_file($path, true);
                    for ($j = 0; $j < count($files); $j++) {
                        $new_file_obj = array_merge($fileObj, array("source-file" => "/" . basename($files[$j]), "source-folder" => "temp"));
                        array_push($add, $new_file_obj);
                    }
                    continue;
                }
            }
            array_push($add, $fileObj);
        }
        $files_patch['add'] = $add;
        return $sync_request_metadata;
    }
    
    public static function export_sync_data_volume($sync_request_metadata = array(), $volume_id = 0, &$next_volume_id = 0) {
        $zipFile = self::tempFile('sync_data_archive', '.zip');
        $zip = new ZipArchive;
        $zip->open($zipFile, ZipArchive::CREATE);
        
        $full_paths = self::full_paths(true);
        
        $files_patch = $sync_request_metadata['files_patch'];
        
        $next_volume = 0;
        $add_length = count($files_patch['add']);
        $size = 0;
        $currentfile = $volume_id;
        
        for ($i = $volume_id; $i < $add_length; $i++) {
            $fileObj = $files_patch['add'][$i];
            
            if (isset($fileObj['source-folder'])) {
                $source_folder = $fileObj['source-folder'];
                $source_file = $fileObj['source-file'];
            } 
            else {
                $source_folder = $fileObj['folder'];
                $source_file = $fileObj['file'];
            }
            
            $abs_folder = $full_paths[$source_folder];
            $path = $abs_folder . $source_file;
            
            $zip_folder = isset($fileObj['source-folder']) ? $fileObj['source-folder'] : $fileObj['folder'];
            $zip_file = isset($fileObj['source-file']) ? $fileObj['source-file'] : $fileObj['file'];
            
            $currentfile = $i;
            if (is_file($path)) {
                $size+= filesize($path);
                if ($size > self::volume_size()) {
                    $next_volume = $i;
                    break;
                } 
                else {
                    $zip->addFile($path, $zip_folder . $zip_file);
                }
            }
        }
        
        $sync_request_metadata['data']['next_volume'] = $next_volume;
        
        $next_volume_id = $next_volume;
        
        $file = CloudPressDump::tempFile("next_volume");
        $data = array("next_volume" => $next_volume, "sync_request_metadata" => $sync_request_metadata);
        file_put_contents($file, json_encode($data));
        
        $zip->addFile($file, "next_volume.json");
        $zip->close();
        
        return $zipFile;
    }
    
    public static function backupTablesToFile($dbFile, $filter = false, $replace = false) {
        $tables = CloudPressDump::getTables();
        $exlude = array();
        if ($filter) {
            $exclude = array("wp_users", "wp_usermeta");
            $tables = array_values(array_diff($tables, $exclude));
        }
        
        global $backup_filter;
        $backup_filter = $filter;
        
        self::exportDatabaseToFile($dbFile, array('skip-triggers' => true, 'include-tables' => $tables, 'whereFct' => function ($table, $row) {
            global $backup_filter;
            if ($backup_filter) {
                if ($table == "wp_options") {
                    if (strpos($row['option_name'], "_site_transient") === 0) {
                        return null;
                    }
                    
                    if (strpos($row['option_name'], "_transient") === 0) {
                        return null;
                    }
                    if ($replace && array_search($row['option_name'], self::$sync_variables)) {
                        $row['option_value'] = "%cloudpress-variable-" . $row['option_name'] . "%";
                    }
                }
            }
            return $row;
        }));
        return $tables;
    }
    
    public static function export_sync_data($sync_request_metadata = array(), $push = false, $skipOptions = array()) {
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        
        $files_patch = & $sync_request_metadata['files_patch'];
        $db_patch = $sync_request_metadata['db_patch'];
        
        if ($push) {
            $sync_request_metadata['data']["table_prefix"] = $table_prefix;
        }
        
        $exportedTables = $db_patch['add'];


        for ($i = 0; $i < count($exportedTables); $i++) {
            $exportedTables[$i] = $table_prefix . $exportedTables[$i];
        }

        
        $dbFile = self::tempFile('db_patch');
        $add = false;
        if (count($exportedTables)) {
            $add = true;
            
            self::exportDatabaseToFile($dbFile, array('skip-triggers' => true, 'include-tables' => $exportedTables, 'whereFct' => function ($table, $row) {
                global $exportedTablesObj;
                
                if (isset($exportedTablesObj[$table]) && $exportedTablesObj[$table] == "options") {
                    if (isset($skipOptions[$row['option_name']])) {
                        return null;
                    }
                    
                    if (array_search($row['option_name'], self::$sync_variables)) {
                        $row['option_value'] = "%cloudpress-variable-" . $row['option_name'] . "%";
                    }
                }
                return $row;
            }));
        }
        
        if (file_exists($dbFile)) {
            $fileContents = file_get_contents($dbFile);
        } 
        else {
            $fileContents = "";
        }
        
        if (isset($db_patch)) {
            $add = true;
            if (isset($db_patch['delete'])) {
                $to_delete = $db_patch['delete'];
                $sql = "";
                if (count($to_delete)) {
                    for ($i = 0; $i < count($to_delete); $i++) {
                        $to_delete[$i] = strtolower($table_prefix) . $to_delete[$i];
                    }
                    $sql = "DROP TABLE IF EXISTS " . implode(",", $to_delete) . ";" . PHP_EOL;
                }
            }
            
            if ($sql) {
                $fileContents = $sql . $fileContents;
            }
        }
        
        for ($i = 0; $i < count($exportedTables); $i++) {
            $table_name = $exportedTables[$i];
            $new_table_name = preg_replace("/^" . $table_prefix . "/i", '%cloudpress-variable-tableprefix%', $table_name);
            $fileContents = str_replace($table_name, $new_table_name, $fileContents);
        }
        
        if ($fileContents) {
            file_put_contents($dbFile, $fileContents);
        }
        
        $sync_request_metadata_file = CloudPressDump::tempFile("request_metadata_file");
        file_put_contents($sync_request_metadata_file, json_encode($sync_request_metadata));
        
        $debug_file = CloudPressDump::tempFile("debug_file");
        $debug_data = array("metadata" => $sync_request_metadata, "skipOptions" => $skipOptions, "variables" => self::$sync_variables);
        file_put_contents($debug_file, json_encode($debug_data));
        
        array_push($files_patch['add'], array("folder" => "temp", "file" => "/" . basename($dbFile), "destination-file" => "sqldata/data.sql"));
        array_push($files_patch['add'], array("folder" => "temp", "file" => "/" . basename($debug_file), "destination-file" => "debug_file.json"));
        array_push($files_patch['add'], array("folder" => "temp", "file" => "/" . basename($sync_request_metadata_file), "destination-file" => "sync_request.json"));
        
        $sync_request_metadata['data']['sql-data-file'] = "/" . basename($dbFile);
        $sync_request_metadata['data']['debug-file'] = "/" . basename($debug_file);
        $sync_request_metadata['data']['sync-request-file'] = "/" . basename($sync_request_metadata_file);
        
        return $sync_request_metadata;
    }
    
    public static function exportFilesToZip($hash = array(), $skipOptions = array()) {
        $diff = self::getDiff($hash, true);
        $diff_files = $diff['files'];
        
        $diff = CloudPressDump::get_files_patch($diff_files);
        
        $dbFile = self::tempFile('sql');
        
        $files_to_delete = CloudPressDump::tempFile("files_to_delete");
        file_put_contents($files_to_delete, json_encode($diff['delete']));
        
        $sync_file = CloudPressDump::tempFile("sync_file");
        $sync_data = array("request_hash" => $hash, "diff" => $diff, "all" => CPSyncUtils::hashes());
        file_put_contents($sync_file, json_encode($sync_data));
        
        $zip->addFile($dbFile, "sqldata/data.sql");
        $zip->addFile($sync_file, "sync_file.json");
        $zip->addFile($files_to_delete, "files_to_delete.json");
        $zip->close();
        
        return $zipFile;
    }
    
    public static function exportDatabaseToFile($file, $settings) {
        
        $dumpSettings = array('compress' => Mysqldump::NONE, 'no-data' => false, 'exclude-tables' => array(), 'add-drop-table' => true, 'single-transaction' => false, 'lock-tables' => true, 'add-locks' => true, 'extended-insert' => false, 'disable-keys' => true, 'skip-triggers' => false, 'add-drop-trigger' => true, 'databases' => false, 'add-drop-database' => false, 'hex-blob' => true, 'no-create-info' => false, 'where' => '');
        
        $dumpSettings = array_merge($dumpSettings, $settings);
        
        $dump = new Mysqldump(DB_NAME, DB_USER, DB_PASSWORD, DB_HOST, "mysql", $dumpSettings);
        
        $dump->start($file);
    }
}


class CPFS
{
    private $syncRoot;
    private $absSyncRoot;
    private $syncFolder;
    private $fs;
    public $sync_file;
    public $pull_id;
    public $pull_data;
    public $credentials;
    public $first_sync;
    
    public $FS_CHMOD_FILE = 0664;
    public $FS_CHMOD_DIR = 0777;

    public static $sync_folder = ".cloudpress-sync";
    
    function __construct($use_direct_filesystem = false) {
        
        CloudPressDump::$cpSync = $this;
        require_once (ABSPATH . 'wp-admin/includes/file.php');
        
        if (!$use_direct_filesystem) {
            $this->credentials = isset($_REQUEST['credentials']) ? $_REQUEST['credentials'] : false;
            if (!$this->credentials) {
                $creds = request_filesystem_credentials(site_url() . '/wp-admin/tools.php?page=cloudpress-sync-page', '', false, false, array());
                $this->credentials = $creds;
            }
            
            if (!WP_Filesystem($this->credentials)) {
                die();
            }
        } 
        else {
            add_filter('filesystem_method', function () {
                return "direct";
            });
            
            if (!WP_Filesystem()) {
                die('file system failed');
            }
        }
        
        global $wp_filesystem;
        $this->fs = $wp_filesystem;
        
        if (!$wp_filesystem->exists($wp_filesystem->abspath() . "/" . CPFS::$sync_folder . "/")) {
            $paths = array($wp_filesystem->abspath());
            foreach ($paths as $path) {
                if ($this->fs->is_writable($path)) {
                    $this->syncRoot = $path;
                    break;
                }
            }
        } 
        else {
            $this->syncRoot = $wp_filesystem->abspath();
        }
        
        $this->absSyncFolder = ABSPATH . "/" . CPFS::$sync_folder;
        $this->syncFolder = $this->syncRoot . CPFS::$sync_folder;
        
        $this->abs_sync_file = $this->absSyncFolder . "/.cloudpress_sync";
        $this->sync_file = $this->syncFolder . "/.cloudpress_sync";
        $this->rollback_file = $this->syncFolder . "/.cloudpress_rollback";
        $this->abs_rollback_file = $this->absSyncFolder . "/.cloudpress_rollback";
        $this->first_rollback_file = $this->syncFolder . "/.cloudpress_rollback_first";
        $this->abs_first_rollback_file = $this->absSyncFolder . "/.cloudpress_rollback_first";
        
        $version_path = dirname(__FILE__) . '/../version.txt';
        if (file_exists($version_path)) {
            $this->version = file_get_contents($version_path);
        } 
        else {
            $this->version = "-1";
        }
    }
    
    function last_backup_file() {
        if ($this->fs->exists($this->rollback_file)) {
            return $this->rollback_file;
        }
        if ($this->fs->exists($this->first_rollback_file)) {
            return $this->first_rollback_file;
        }
        return false;
    }
    
    function rollback_time($first = false) {
        $rollback_file = $first ? $this->first_rollback_file : $this->last_backup_file();
        $rollbackFile = json_decode($this->fs->get_contents($rollback_file), true);
        $timestamp = str_replace('cp_pull_', '', $rollbackFile['files']);
        $timestamp = explode('_', $timestamp);
        $timestamp = $timestamp[0];
        return $timestamp;
    }
    
    function discard_old_db_backups($last_id) {
        $drop_tables = array();
        
        for ($i = 1; $i < $last_id; $i++) {
            $discard_tables = CloudPressDump::getTablesLike("cp_" . $i . "_%");
            if (count($discard_tables)) {
                $drop_tables = array_merge($discard_tables);
            }
        }
        
        if (count($drop_tables)) {
            $sql = "DROP TABLE IF EXISTS " . implode(",", $drop_tables) . ";";
            return $this->execute_sql($sql);
        }
    }
    
    function discard_old_files_backups($backup_id, $skip = array()) {
        global $wp_filesystem;
        $backup_folder = $this->syncFolder . "/";
        
        $filelist = $wp_filesystem->dirlist($backup_folder);
        $matches = array();
        preg_match("/^cp_pull_([\d]+?)__([\w\W]+)/", $backup_id, $matches);
        $last_id = intval($matches[1]);
        
        $this->sync_log("last_id :: $last_id");
        $to_delete = array();
        
        foreach ($filelist as $file_name => $file) {
            $this->sync_log("file_name :: $file_name");
            if ($file['type'] == "d") {
                if (array_search($file_name, $skip) !== FALSE) {
                    continue;
                }
                $matches = array();
                preg_match("/^cp_pull_([\d]+?)__([\w\W]+)/", $file_name, $matches);
                if (count($matches)) {
                    $id = intval($matches[1]);
                    if ($id < $last_id) {
                        $wp_filesystem->delete($backup_folder . $file_name, true);
                        array_push($to_delete, $backup_folder . $file_name);
                        $this->sync_log("id = $id; < last_id=$last_id; delete old folder ##" . $backup_folder . $file_name);
                    }
                }
            }
        }
    }
    
    function rollback_db($backup_id) {
        $this->sync_log("rollback_db::$backup_id");
        
        $rollbackTables = CloudPressDump::getTablesLike("cp_" . $backup_id . "_%");
        if (count($rollbackTables)) {
            $sql = "";
            foreach ($rollbackTables as $old_table) {
                $new_table = str_replace("cp_" . $backup_id . "_", "", $old_table);
                $old_table_temp = str_replace("cp_" . $backup_id . "_", "cp_t_" . $backup_id . "_", $old_table);
                $sql.= "DROP TABLE IF EXISTS $old_table_temp; RENAME TABLE $new_table TO $old_table_temp, $old_table TO $new_table;";
            }
            
            $this->sync_log("rollback_db");
            $this->sync_log("$sql");
            
            return $this->execute_sql($sql);
        }
        return new WP_Error('rollback', "no backup with id $backup_id exists");
    }
    
    function getchmod($file) {
        return fileperms($file) & 0777;
    }
    
    function rollback_files($backup_id, $first = false) {
        global $wp_filesystem;
        
        $this->sync_log('rollback_files');
        
        $backup_folder = $this->syncFolder . "/" . $backup_id . "/backup";
        $rollback_backup_folder = $this->syncFolder . "/" . $backup_id . "/rollback_" . time() . "/";
        
        //$this->sync_log('backup_files start##'.$rollback_backup_folder);
        //$this->backup_files($rollback_backup_folder);
        //$this->sync_log('backup_files end##'.$rollback_backup_folder);
        
        $full_paths_abs = CloudPressDump::full_paths();
        $full_paths = CloudPressDump::full_paths(false, true);
        foreach ($full_paths as $folder_name => $full_path) {
            $bk_folder = $backup_folder . "/" . $folder_name;
            $old_mod = false;
            
            $this->sync_log("exists a ? " . $wp_filesystem->exists($full_path));
            
            if ($wp_filesystem->exists($full_path)) {
                
                if (isset($full_paths_abs[$folder_name])) {
                    $old_mod = $this->getchmod($full_paths_abs[$folder_name]);
                }
                
                if ($folder_name == "plugins") {
                    $filelist = $wp_filesystem->dirlist(trailingslashit($full_path));
                    $this->sync_log('exists##' . $full_path);
                    if (!empty($filelist)) {
                        foreach ($filelist as $delete_file) {
                            $this->sync_log('name##' . $delete_file['name']);
                            if ($delete_file['name'] != "cloudpress-sync") {
                                $to_delete = trailingslashit($full_path) . $delete_file['name'];
                                if ($wp_filesystem->is_dir($to_delete)) {
                                    $wp_filesystem->delete($to_delete, true);
                                    $this->sync_log("delete folder $to_delete");
                                } 
                                else {
                                    $this->sync_log("delete file $to_delete");
                                    $wp_filesystem->delete($to_delete);
                                }
                            }
                        }
                    }
                } 
                else {
                    $to_delete = trailingslashit($full_path);
                    if ($wp_filesystem->is_dir($to_delete)) {
                        $wp_filesystem->delete($to_delete, true);
                        $this->sync_log("delete folder $to_delete");
                    } 
                    else {
                        $this->sync_log("delete file $to_delete");
                        $wp_filesystem->delete($to_delete);
                    }
                }
            }
            
            $this->sync_log("exists b ? " . $wp_filesystem->exists($full_path));
            
            if (!$wp_filesystem->is_dir($full_path)) {
                $wp_filesystem->mkdir($full_path);
            }
            
            $this->sync_log("copy_dir $bk_folder -> $full_path");
            copy_dir($bk_folder, $full_path, array("cloudpress-sync"));
            
            $this->sync_log("old_mod=$old_mod");
            
            if ($old_mod) {
                $wp_filesystem->chmod($full_path, $old_mod);
                $this->sync_log("chmod $full_path $old_mod");
            }
        }
        
        if ($first) {
            if ($wp_filesystem->exists($this->first_rollback_file)) {
                $wp_filesystem->delete($this->first_rollback_file);
            }
        }
        
        if ($wp_filesystem->exists($this->rollback_file)) {
            $wp_filesystem->delete($this->rollback_file);
        }
        
        return true;
    }
    
    function rollback($first = false) {
        $this->sync_log('rollback start');
        $this->sync_log('first :: ' . $first ? "true" : "false");
        $ids = $first ? $this->get_first_rollback_ids() : $this->get_rollback_ids();
        if ($ids) {
            $this->sync_log('ids :: ' . json_encode($ids));
            $files_rollback = $this->rollback_files($ids['files'], $first);
            $db_rollback = $this->rollback_db($ids['database']);
            $this->toggle_sync_folder(false);
        }
    }
    
    function get_first_rollback_ids() {
        $rollback = FALSE;
        if (file_exists($this->abs_first_rollback_file)) {
            $rollback = json_decode(file_get_contents($this->abs_first_rollback_file), true);
        }
        return $rollback;
    }
    
    function get_rollback_ids() {
        $rollback = FALSE;
        if (file_exists($this->abs_rollback_file)) {
            $rollback = json_decode(file_get_contents($this->abs_rollback_file), true);
        }
        return $rollback;
    }
    
    function has_rollback() {
        if ($this->fs->exists($this->rollback_file)) {
            return true;
        }
        return FALSE;
    }
    
    function has_first_rollback() {
        if ($this->fs->exists($this->first_rollback_file)) {
            return true;
        }
        return FALSE;
    }
    
    function save_pull_rollback($first = false) {
        $rollback_data = array("database" => $this->last_backup_db_id(), "files" => $this->pull_id);
        
        $skip_folders = array();
        $first_ids = $this->get_first_rollback_ids();
        if ($first_ids !== FALSE) {
            array_push($skip_folders, $first_ids['files']);
        }
        
        $success = file_put_contents($first ? $this->abs_first_rollback_file : $this->abs_rollback_file, json_encode($rollback_data));
        if ($success !== FALSE) {
            $this->discard_old_db_backups($rollback_data['database']);
            $this->discard_old_files_backups($rollback_data['files'], $skip_folders);
        }
        
        $this->toggle_sync_folder(false);
    }
    
    function toggle_sync_folder($enable) {
        $this->fs->chmod($this->syncFolder, $enable ? 0777 : 0700);
    }
    
    function get_pull_folder($pull_id = false, $abs = false) {
        return ($abs ? $this->absSyncFolder : $this->syncFolder) . "/" . ($pull_id !== false ? $pull_id : $this->pull_id);
    }
    
    function remote_changed($project_url, $oneTimeToken) {
    }
    
    function local_changed() {
        $hash = CPSyncUtils::hashes();
        $data = CloudPressDump::get_sync_metadata($hash, false);
        return $this->check_diff_data($data);
    }
    
    function compare_local_to_remote($project_url, $oneTimeToken, &$sync_data = array()) {
        return $this->compare_version($project_url, $oneTimeToken, "local_to_remote", $sync_data);
    }
    
    function compare_remote_to_local($project_url, $oneTimeToken, &$sync_data = array()) {
        return $this->compare_version($project_url, $oneTimeToken, "remote_to_local", $sync_data);
    }
    
    function get_sync_request_file() {
        return $this->get_pull_folder(false, true) . "/sync_request/sync_request.json";
    }
    
    function get_sync_request_data() {
        $diff_file = $this->get_sync_request_file();
        if (!file_exists($diff_file)) {
            return null;
        }
        return json_decode(file_get_contents($diff_file), true);
    }
    
    function check_diff_data($diff_data) {
        $needs_sync = false;
        
        if ($diff_data && isset($diff_data['files_patch'])) {
            $patch = $diff_data['files_patch'];
            if (isset($patch['add']) && count($patch['add'])) {
                $needs_sync = true;
            }
            if (isset($patch['delete']) && count($patch['delete'])) {
                $needs_sync = true;
            }
        }
        
        if ($diff_data && isset($diff_data['db_patch'])) {
            $patch = $diff_data['db_patch'];
            if (isset($patch['add']) && count($patch['add'])) {
                $needs_sync = true;
            }
            if (isset($patch['delete']) && count($patch['delete'])) {
                $needs_sync = true;
            }
        }
        
        return $needs_sync;
    }
    
    function compare_version($project_url, $oneTimeToken, $way, &$sync_data = array()) {
        $this->sync_log("compare_version");
        $to_file = CloudPressDump::tempFile($oneTimeToken);
        $this->sync_log("compare_version::to_file::".$to_file);
        
        $hashes = json_encode(CPSyncUtils::hashes($way, $this));

        $this->sync_log("compare_version::hashes::".$hashes);

        $data = array("action" => "compare_" . $way, "oneTimeToken" => $oneTimeToken, "hashes" => $hashes, "hash_method" => "size", "version" => $this->version);
        
        $this->sync_log("file=" . $to_file);
        $url = $project_url . "/wp-content/mu-plugins/cloudpress-sync/api2.php";
        $diffs = wp_safe_remote_post($url, array("stream" => true, "filename" => $to_file, 'timeout' => 6000, 'body' => $data));
        
        $this->sync_log("file exists=" . file_exists($to_file));
        if (!file_exists($to_file)) {
            return new WP_Error('failed', __("compare failed##" . json_encode($diffs), ""));
        }
        
        $this->sync_log("compare_version :: " . $to_file . "::" . "compare_" . $way);
        
        $this->sync_log($hashes);
        
        $unzip_path = $this->get_pull_folder() . "/sync_request";
        
        $this->fs->mkdir($unzip_path, $this->FS_CHMOD_DIR);
        
        $this->sync_log("unzip_path" . $unzip_path);

        $this->sync_log("zip_file" . $to_file);
        
        $unzip = unzip_file($to_file, $unzip_path);
        
        ob_start();
        //print_r($diffs);
        //print_r($data);
        var_dump($unzip);
        $result = ob_get_clean();
        
        $this->sync_log("result" . $result);
        
        $diff_file = $this->get_sync_request_file();
        
        $this->sync_log("diff_file" . $diff_file);
        
        if (!file_exists($diff_file)) {
            return false;
        }
        
        $diff_content = file_get_contents($diff_file);
        
        $diff_data = json_decode($diff_content, true);
        $sync_data = json_decode($diff_content, true);
        
        return $this->check_diff_data($diff_data);
    }
    
    function check_requirements() {
        $errors = array();
        if (!$this->syncRoot) {
            array_push($errors, array("error" => "1001", "msg" => "No writable folder found."));
        }
        
        /*
        //check for free space
        $free_space = disk_free_space($this->syncRoot)/1024/1024;
        */
        
        return $errors;
    }
    
    function last_backup_db_id() {
        $backupTables = CloudPressDump::getTablesLike("cp_%");
        $max = 0;
        foreach ($backupTables as $table) {
            $matches = array();
            preg_match('/^cp_([\d]+)_/i', $table, $matches);
            if (count($matches)) {
                $id = intval($matches[1]);
                if ($id > $max) {
                    $max = $id;
                }
            }
        }
        
        return $max;
    }
    
    function backup_db_id() {
        $max = $this->first_sync ? 0 : ($this->last_backup_db_id() + 1);
        $newTables = CloudPressDump::getTablesLike("cp_" . $max . "_%");
        return $max;
    }
    function getQueriesFromSQLFile($sqlfile) {
        $keywords = array('ALTER', 'CREATE', 'DELETE', 'DROP', 'INSERT', 'REPLACE', 'SELECT', 'SET', 'TRUNCATE', 'UPDATE', 'USE');
        
        // create the regular expression for matching the whitelisted keywords
        $regexp = sprintf('/\s*;\s*(?=(%s)\b)/si', implode('|', $keywords));
        
        // split there
        $splitter = preg_split($regexp, $sqlfile);
        //echo('splitter::' . json_encode($splitter));
        // remove trailing semicolon or whitespaces
        $splitter = array_map(create_function('$line', 'return preg_replace("/[\s;]*$/", "", $line);'), $splitter);
        
        // remove empty lines
        return array_filter($splitter, create_function('$line', 'return !empty($line);'));
    }

    function remove_comments(&$output)
        {
           $lines = explode("\n", $output);
           $output = "";

           // try to keep mem. use down
           $linecount = count($lines);

           $in_comment = false;
           for($i = 0; $i < $linecount; $i++)
           {
              if( preg_match("/^\/\*/", preg_quote($lines[$i])) )
              {
                 $in_comment = true;
              }

              if( !$in_comment )
              {
                 $output .= $lines[$i] . "\n";
              }

              if( preg_match("/\*\/$/", preg_quote($lines[$i])) )
              {
                 $in_comment = false;
              }
           }

           unset($lines);
           return $output;
        }

        //
        // remove_remarks will strip the sql comment lines out of an uploaded sql file
        //
        function remove_remarks($sql)
        {
           $lines = explode("\n", $sql);

           // try to keep mem. use down
           $sql = "";

           $linecount = count($lines);
           $output = "";

           for ($i = 0; $i < $linecount; $i++)
           {
              if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0))
              {
                 if (isset($lines[$i][0]) && $lines[$i][0] != "#")
                 {
                    $output .= $lines[$i] . "\n";
                 }
                 else
                 {
                    $output .= "\n";
                 }
                 // Trading a bit of speed for lower mem. use here.
                 $lines[$i] = "";
              }
           }

           return $output;

        }

        //
        // split_sql_file will split an uploaded sql file into single sql statements.
        // Note: expects trim() to have already been run on $sql.
        //
        function split_sql_file($sql, $delimiter)
        {
           // Split up our string into "possible" SQL statements.
           $tokens = explode($delimiter, $sql);

           // try to save mem.
           $sql = "";
           $output = array();

           // we don't actually care about the matches preg gives us.
           $matches = array();

           // this is faster than calling count($oktens) every time thru the loop.
           $token_count = count($tokens);
           for ($i = 0; $i < $token_count; $i++)
           {
              // Don't wanna add an empty string as the last thing in the array.
              if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
              {
                 // This is the total number of single quotes in the token.
                 $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
                 // Counts single quotes that are preceded by an odd number of backslashes,
                 // which means they're escaped quotes.
                 $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

                 $unescaped_quotes = $total_quotes - $escaped_quotes;

                 // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
                 if (($unescaped_quotes % 2) == 0)
                 {
                    // It's a complete sql statement.
                    $output[] = $tokens[$i];
                    // save memory.
                    $tokens[$i] = "";
                 }
                 else
                 {
                    // incomplete sql statement. keep adding tokens until we have a complete one.
                    // $temp will hold what we have so far.
                    $temp = $tokens[$i] . $delimiter;
                    // save memory..
                    $tokens[$i] = "";

                    // Do we have a complete statement yet?
                    $complete_stmt = false;

                    for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
                    {
                       // This is the total number of single quotes in the token.
                       $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
                       // Counts single quotes that are preceded by an odd number of backslashes,
                       // which means they're escaped quotes.
                       $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

                       $unescaped_quotes = $total_quotes - $escaped_quotes;

                       if (($unescaped_quotes % 2) == 1)
                       {
                          // odd number of unescaped quotes. In combination with the previous incomplete
                          // statement(s), we now have a complete statement. (2 odds always make an even)
                          $output[] = $temp . $tokens[$j];

                          // save memory.
                          $tokens[$j] = "";
                          $temp = "";

                          // exit the loop.
                          $complete_stmt = true;
                          // make sure the outer loop continues at the right point.
                          $i = $j;
                       }
                       else
                       {
                          // even number of unescaped quotes. We still don't have a complete statement.
                          // (1 odd and 1 even always make an odd)
                          $temp .= $tokens[$j] . $delimiter;
                          // save memory.
                          $tokens[$j] = "";
                       }

                    } // for..
                 } // else
              }
           }

           return $output;
        }
    
    function restore_db_from_string($sql) {
        $this->sync_log('restore_db_from_string::1');
        //$this->sync_log('restore_db_from_string::'.$sql);
        return $this->execute_sql($sql);
    }
    
    function execute_sql($sql) {
        $this->sync_log('execute_sql::'.$sql);
        //$queries = $this->getQueriesFromSQLFile($sql);
      
        try {
            $db = new PDO(Mysqldump::get_pdo_dsn("mysql", DB_HOST, DB_NAME), DB_USER, DB_PASSWORD, array(PDO::ATTR_TIMEOUT => "10000", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        catch(PDOException $e) {
            return new WP_Error('executesql', $e->getMessage());
        }

        $version_e = $db->query('select version()')->fetchColumn();
        $version = mb_substr($version_e, 0, 6);

        if (version_compare($version, '5.5.3') < 0) {
            $this->sync_log('mysql old version::' . $version);

            $sql = str_replace('utf8mb4_unicode_ci', 'utf8_general_ci', $sql);
            $sql = str_replace('utf8mb4', 'utf8', $sql);
        } else {
            $this->sync_log('mysql version ok ::' . $version);
        }

        $queries = $this->split_sql_file($sql, ";");
        
        $this->sync_log('execute sqls::' . json_encode($queries));

        // works regardless of statements emulation
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
        
        //$db->setAttribute(PDO::ATTR_PERSISTENT, true);
        //$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $db->beginTransaction();
        
        $error = "";
        
        try {
            foreach ($queries as $query) {
                try {
                    $this->sync_log('execute sql::' . $query);
                    $result = $db->exec($query);
                }
                catch(Exception $e) {
                    $this->sync_log('execute sql error::' . $e->getMessage());
                    echo $e->getMessage() . "<br /> <p>The sql is: $query</p>";
                }
            }
            $db->commit();
            
            if ($result === FALSE) {
                $db->rollback();
            } 
            else {
            }
        }
        catch(PDOException $e) {
            $db->rollback();
            $error = new WP_Error('executesql', $e->getMessage());
        }
        
        return array("sql" => $sql, "error" => $error);
    }
    
    function pull_in_progress() {
        return $this->fs->exists($this->sync_file);
    }
    
    function discard_pull() {
        if ($this->fs->exists($this->sync_file)) {
            $this->fs->delete($this->sync_file);
        }
    }
    
    function is_first_sync() {
        $first_backup = $this->get_first_rollback_ids();
        if ($first_backup !== FALSE) {
            return false;
        }
        
        $last_backup = $this->get_rollback_ids();
        if ($last_backup !== FALSE) {
            return false;
        }
        
        return true;
    }
    
    function sync_folder_exists() {
        if ($this->fs->is_dir($this->syncFolder)) {
            return true;
        }
        return false;
    }

    function load_pull_id($discard_existing = false) {
        if (!$this->fs->is_dir($this->syncFolder)) {
            $this->fs->mkdir($this->syncFolder, $this->FS_CHMOD_DIR);

            if (!$this->fs->is_dir($this->syncFolder)) {
                return false;
            }
        }
        
        $this->toggle_sync_folder(true);
        
        $this->sync_log('load_pull_id start');
        $this->sync_log('dir list');
        $this->sync_log(json_encode($this->fs->dirlist($this->fs->wp_content_dir())));
        
        $this->sync_log("syncfile##" . $this->sync_file);
        
        try {
            $this->first_sync = $this->is_first_sync();
        }
        catch(Exception $e) {
        }
        
        $this->sync_log("##first_sync##" . $this->first_sync);
        $this->sync_log("##sync_file##" . $this->sync_file . "##" . $this->abs_sync_file);
        
        if ($discard_existing || !file_exists($this->abs_sync_file)) {
            $this->sync_log("##before put_contents##cp_pull_" . time() . "__" . wp_generate_password(10, false));
            try {
                file_put_contents($this->abs_sync_file, "cp_pull_" . time() . "__" . wp_generate_password(10, false));
            }
            catch(Exception $e) {
            }
            $this->sync_log("##after put_contents##cp_pull_" . time() . "__" . wp_generate_password(10, false));
        }
        
        $this->pull_id = file_get_contents($this->abs_sync_file);
        
        //$this->pull_id = $this->fs->get_contents($this->sync_file);
        
        $this->sync_log("##load_pull_id##" . $this->pull_id);
        
        $pull_folder = $this->get_pull_folder();
        
        $this->sync_log("##pull_folder##" . $pull_folder);
        
        if (!$this->fs->exists($pull_folder)) {
            $this->fs->mkdir($pull_folder, $this->FS_CHMOD_DIR);
            $this->fs->chmod($pull_folder, $this->FS_CHMOD_DIR, false);
        }
    }
    
    function get_pull_file() {
        return $this->absSyncFolder . "/" . $this->pull_id . "/.sync_status";
    }
    
    function load_pull_data() {
        if ($this->pull_data) {
            return $this->pull_data;
        }
        $this->load_pull_id();
        $data_file = $this->get_pull_file();
        $data = array();
        if (file_exists($data_file)) {
            $data = json_decode(file_get_contents($data_file), true);
        }
        $this->pull_data = $data;
        $this->save_pull_data();
    }
    
    function save_pull_data() {
        $data_file = $this->get_pull_file();
        file_put_contents($data_file, json_encode($this->pull_data));
    }
    
    function set_pull_value($name, $value) {
        $this->load_pull_data();
        $this->pull_data[$name] = $value;
        $this->save_pull_data();
        return $this->pull_data;
    }
    
    function get_pull_value($name) {
        $this->load_pull_data();
        if (isset($this->pull_data[$name])) {
            return $this->pull_data[$name];
        }
        return null;
    }
    
    function backup_files_rename($backup_folder = "") {
        if (!$backup_folder) {
            $backup_folder = $this->get_pull_folder() . "/backup/";
        }
        
        $errors = array();
        $full_paths = CloudPressDump::full_paths(false, true);
        
        foreach ($full_paths as $folder_name => $full_path) {
            $bk_folder = $backup_folder . "/" . $folder_name;
            
            //if (!$this->fs->is_dir($bk_folder)) {
            //  $this->sync_log('make folder '.$bk_folder);
            //}
            $this->sync_log('chmod ' . $full_path);
            $result = $this->fs->chmod($full_path, $this->FS_CHMOD_DIR, false);
            if (is_wp_error($result)) {
                array_push($errors, $result);
            }
            
            $destination = untrailingslashit($full_path) . "_" . $this->pull_id;
            $this->sync_log('move ' . $full_path . "##" . $destination);
            $result = $this->fs->copy($full_path, $destination, true);
            
            /*
            $this->sync_log('copy folder '.$full_path.' to '.$bk_folder);
            $result = copy_dir($full_path, $bk_folder);
            */
            
            if (is_wp_error($result)) {
                array_push($errors, $result);
            }
        }
        
        if (count($errors)) {
            return $errors[0];
        }
        
        return true;
    }

    function list_dir($from, $to, &$files, $skip_list = array()) {
        global $wp_filesystem;
     
        $dirlist = $wp_filesystem->dirlist($from);
        $from = trailingslashit($from);
        $to = trailingslashit($to);

        array_push($files, array("source" => $from, "destination" => $to, "type" => 'd' ));

        foreach ( (array) $dirlist as $filename => $fileinfo ) {
            if ( in_array( $filename, $skip_list ) )
                continue;

            if ( 'f' == $fileinfo['type'] || 'd' == $fileinfo['type'] ) {
                array_push($files, array("source" => $from . $filename, "destination" => $to .$filename, "type" => $fileinfo['type'] ));
            }
            
            if ( 'd' == $fileinfo['type'] ) {
                $this->list_dir($from . $filename, $to .$filename, $files, $skip_list);
            }
        }
    }

    function copy_dir($from, $to, $skip_list = array() ) {
        global $wp_filesystem;
     
        $dirlist = $wp_filesystem->dirlist($from);
     
        $from = trailingslashit($from);
        $to = trailingslashit($to);
     
        foreach ( (array) $dirlist as $filename => $fileinfo ) {
            if ( in_array( $filename, $skip_list ) )
                continue;
            
            if ( 'f' == $fileinfo['type'] ) {
                if ( ! $wp_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ) {
                    // If copy failed, chmod file to 0644 and try again.
                    $wp_filesystem->chmod( $to . $filename, FS_CHMOD_FILE );
                    if ( ! $wp_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ){
                        return new WP_Error( 'copy_failed_copy_dir', __( 'Could not copy file.' ), $to . $filename );
                    } else {
                        backup_progress($from . $filename);
                    }
                } else {
                    backup_progress($from . $filename);
                }
            } elseif ( 'd' == $fileinfo['type'] ) {
                if ( !$wp_filesystem->is_dir($to . $filename) ) {
                    if ( !$wp_filesystem->mkdir($to . $filename, FS_CHMOD_DIR) )
                        return new WP_Error( 'mkdir_failed_copy_dir', __( 'Could not create directory.' ), $to . $filename );
                }
     
                // generate the $sub_skip_list for the subdirectory as a sub-set of the existing $skip_list
                $sub_skip_list = array();
                foreach ( $skip_list as $skip_item ) {
                    if ( 0 === strpos( $skip_item, $filename . '/' ) )
                        $sub_skip_list[] = preg_replace( '!^' . preg_quote( $filename, '!' ) . '/!i', '', $skip_item );
                }
     
                $result = $this->copy_dir($from . $filename, $to . $filename, $sub_skip_list);
                if ( is_wp_error($result) )
                    return $result;
            }
        }
        return true;
    }


    function copy_files($list, $progress) {
       global $wp_filesystem;
       for ($i=0 ; $i < count($list); $i++) { 
            $f = $list[$i];
            $source = $f['source'];
            $dest = $f['destination'];
            $type = $f['type'];
            if ( 'f' ==  $type) {
                if (!$wp_filesystem->copy($source, $dest, true, FS_CHMOD_FILE) ) {
                    // If copy failed, chmod file to 0644 and try again.
                    $wp_filesystem->chmod( $dest, FS_CHMOD_FILE );
                    if ( ! $wp_filesystem->copy($source, $dest, true, FS_CHMOD_FILE) ){
                        return new WP_Error( 'copy_failed_copy_dir', __( 'Could not copy file.' ), $dest);
                    }
                }
            } elseif ( 'd' ==  $type ) {
                if ( !$wp_filesystem->is_dir($dest) ) {
                    if ( !$wp_filesystem->mkdir($dest, FS_CHMOD_DIR) )
                        return new WP_Error( 'mkdir_failed_copy_dir', __( 'Could not create directory.' ), $dest);
                }
            }

            $this->set_backup_progress($progress++);
       }
    }
    

    function should_backup_db() {
        $backup_ready = $this->get_pull_folder()."/backup/db.flag";
        if (file_exists($backup_ready)) {
           return false;
        }
        return true;
    }

    function get_backup_progress() {
        $progress = 0;
        $progress_file = $this->get_pull_folder()."/backup/progress.json";
        if (file_exists($progress_file)) {
            $progress = intval(file_get_contents($progress_file));
        }
        return $progress;
    }

    function set_backup_progress($progress) {
        $progress_file = $this->get_pull_folder()."/backup/progress.json";
        file_put_contents($progress_file, $progress);
    }

    function backup_list($backup_folder) {
        $progress = $this->get_backup_progress();
        $progress_file = $this->get_pull_folder()."/backup/files.json";
        if (!file_exists($progress_file)) {
            $full_paths = CloudPressDump::full_paths(false, true);
            
            $list = array();
            foreach ($full_paths as $folder_name => $full_path) {
                $bk_folder = $backup_folder . "/" . $folder_name;
                $this->list_dir($full_path, $bk_folder, $list);
            }

            file_put_contents($progress_file, json_encode($list));    
        } else {
            $content = file_get_contents($progress_file);
            $list = json_decode($content, true);
        }

        return $list;
    }


    function backup_files($backup_folder = "") {
       
        if (!$backup_folder) {
            $backup_folder = $this->get_pull_folder() . "/backup/";
        }
        
        $backup_folder = untrailingslashit($backup_folder);
        
        $this->sync_log('backup_files start');
        
        if (!$this->fs->is_dir($backup_folder)) {
            $this->fs->mkdir($backup_folder);
            $this->sync_log('create backup dir##' . $backup_folder);
        }
        
        $backup = CloudPressDump::tempFile('backupsql', '.data');
        CloudPressDump::backupTablesToFile($backup);
        
        $this->fs->copy($backup, $backup_folder . "/data.sql");
        
        $this->sync_log('copy ' . $backup . ' to ' . $backup_folder . "data.sql");
        
        @unlink($backup);
        
        $this->sync_log('remove ' . $backup);
        
        $errors = array();
        
        $list = $this->backup_list($backup_folder);

        $progress = $this->get_backup_progress();

        if ($progress + 1 == count($list)) {
            return true;   
        }

        $remaining = array_slice($list, $progress);

        //echo "------------##".$progress."##";
        //print_r($remaining);
        //@set_time_limit( 1 );

        $result = $this->copy_files($remaining, $progress);

        if (is_wp_error($result)) {
            $errors[] = $result;
        }
        
        if (count($errors)) {
            return $errors[0];
        }
        
        return true;
    }
    
    function backup_db() {
        $action = $this->backup_db_action();
        
        if (!is_wp_error($action)) {
            $action_result = call_user_func_array($action['action']['call'], $action['action']['params']);
            if (is_wp_error($action_result)) {
                $rollback_result = call_user_func_array($action['rollback']['call'], $action['rollback']['params']);
                return $action_result;
            }
            return $action_result;
        }
        return $action;
    }
    
    function backup_db_action($backup_id = "") {
        $allTables = CloudPressDump::getTables();
        
        if (!$backup_id) {
            $backupid = $this->backup_db_id();
        } 
        else {
            $backupid = $backup_id;
        }
        
        if (is_wp_error($backupid)) {
            return $backupid;
        }
        
        $sql = "";
        
        $new_tables = array();
        foreach ($allTables as $table) {
            $new_table = "cp_" . $backupid . "_" . $table;
            $sql.= "DROP TABLE IF EXISTS $new_table; CREATE TABLE $new_table like $table; INSERT $new_table SELECT * FROM $table;";
            array_push($new_tables, $new_table);
        }
        
        $rollback_sql = "";
        if (count($new_tables)) {
            $rollback_sql = "DROP TABLE IF EXISTS " . implode(",", $new_tables) . ";";
        }
        
        $this->sync_log('backup_db_action');
        $this->sync_log($sql);
        
        return array("action" => array("call" => array($this, "execute_sql"), "params" => array($sql)), "rollback" => array("call" => array($this, "execute_sql"), "params" => array($rollback_sql)));
    }
    
    function download_file($url, $to_file, $timeout, $data = array()) {
        if (!$to_file) {
            return new WP_Error('http_no_file', __('Could not create Temporary file.'));
        }
        $response = wp_remote_post($url, array('timeout' => $timeout, 'blocking' => true, 'random' => time(), 'stream' => true, 'filename' => $to_file, 'body' => $data));
        return $response;
    }
    
    function is_in_folders($folders, $file) {
        foreach ($folders as $key => $folder) {
            if (strpos($file, $folder) === 0) {
                return true;
            }
        }
        return false;
    }
    
    function delete_files($request_sync_data) {
        $full_paths = CloudPressDump::full_paths(false, true);
        $this->sync_log("delete_files =. ");
        $this->sync_log(json_encode($request_sync_data));
        $removed_folders = array();
        
        if (isset($request_sync_data['files_patch'])) {
            $fs_patch = $request_sync_data['files_patch'];
            foreach ($fs_patch['delete'] as $fileObj) {
                $path = $full_paths[$fileObj['folder']] . $fileObj['file'];
                
                if ($this->is_in_folders($removed_folders, $path)) {
                    $this->sync_log("is_in_folders :: " . $path);
                    continue;
                } 
                else {
                    $this->sync_log("!is_in_folders :: " . $path);
                }
                
                $this->sync_log("check for removal file/folder :: " . $path);
                
                if ($this->fs->is_dir($path)) {
                    $this->sync_log("remove folder :: " . $path);
                    $this->fs->delete($path, true);
                    array_push($removed_folders, $path);
                } 
                else {
                    if ($this->fs->exists($path)) {
                        $this->sync_log("remove file :: " . $path);
                        $this->fs->delete($path);
                    }
                }
            }
        }
    }
    
    function sync_log($msg) {
        $log_file = $this->get_pull_folder("", true) . "/debug_log.json";
        $content = time() . "::" . $msg . "\r\n";

        $size = 3*1024*1024;
        if (file_exists($log_file) && filesize($log_file) > 3*$size) {
            $f = @fopen($log_file, "rb");
            fseek($f, -$size, SEEK_END);
            $chunk = fread($f, $size);
            fclose($f);
            file_put_contents($log_file, $chunk);
        }

        file_put_contents($log_file, $content, FILE_APPEND);
    }
    
    function get_next_volume() {
        $pull_path = $this->get_pull_folder(false, true) . "/cloudpress-content/";
        $volume_file = $pull_path . "next_volume.json";
        if (file_exists($volume_file)) {
            $content = file_get_contents($volume_file);
            $content = json_decode($content, true);
            @unlink($volume_file);
            return $content['next_volume'];
        }
    }
    
    function get_sync_request_metadata() {
        $pull_path = $this->get_pull_folder(false, true) . "/cloudpress-content/";
        $volume_file = $pull_path . "next_volume.json";
        if (file_exists($volume_file)) {
            $content = file_get_contents($volume_file);
            $content = json_decode($content, true);
            return $content['sync_request_metadata'];
        }
    }
    
    function clean_push_folder() {
        $pull_path = $this->get_pull_folder() . "/cloudpress-content/";
        if ($this->fs->is_dir($pull_path)) {
            $this->fs->delete($pull_path, true);
        }
    }
    
    function unzip_volume($zip) {
        $pull_path = $this->get_pull_folder() . "/cloudpress-content/";
        
        if (!$this->fs->is_dir($pull_path)) {
            
            /* directory didn't exist, so let's create it */
            $this->fs->mkdir($pull_path);
        }
        
        $this->sync_log('unzip volume##' . $zip);
        $this->sync_log('WP_MAX_MEMORY_LIMIT##' . WP_MAX_MEMORY_LIMIT);
        if ($zip) {
            $unzipfile = unzip_file($zip, $pull_path);
            
            if (is_wp_error($unzipfile)) {
                return $unzipfile;
            }
        }
    }
    
    function join_files($sync_request_metadata = null) {
        $full_paths = CloudPressDump::full_paths();
        $pull_path = $this->get_pull_folder() . "/cloudpress-content/";
        $sync_request_metadata = $sync_request_metadata ? $sync_request_metadata : $this->get_sync_request_data();
        $this->sync_log('sync_request_metadata');
        $this->sync_log(json_encode($sync_request_metadata));
        $files_patch = $sync_request_metadata['files_patch'];
        
        $merge = array();
        
        foreach ($files_patch['add'] as $fileObj) {
            if (isset($fileObj['source-folder'])) {
                $source = $pull_path . "/" . $fileObj['source-folder'] . $fileObj['source-file'];
                $destination = $pull_path . "/" . $fileObj['folder'] . $fileObj['file'];
                if (!isset($merge[$destination])) {
                    $merge[$destination] = array();
                }
                array_push($merge[$destination], $source);
            }
        }
        
        foreach ($merge as $destination => $sources) {
            $this->sync_log("----merge file : " . $destination);
            $this->sync_log(json_encode($sources));
            CloudPressDump::join_files($sources, $destination, true);
        }
    }
    
    function apply_sync_data($data, $skipDelete, $applyOptions = array(), $activatePlugins = array(), $push = false) {
        global $wp_filesystem;
        $pull_path = $this->get_pull_folder() . "/cloudpress-content/";
        
        $content_path = trailingslashit($wp_filesystem->wp_content_dir());
        $wp_themes_dir = trailingslashit($wp_filesystem->wp_themes_dir());
        $wp_plugins_dir = trailingslashit($wp_filesystem->wp_plugins_dir());
        
        $upload_obj = wp_upload_dir();
        $upload_root = $wp_filesystem->find_folder($upload_obj['basedir']);
        
        if ($upload_root === "") {
            $upload_root = $content_path . "uploads";
        }
        $this->sync_log('basedir##' . $upload_obj['basedir']);
        $this->sync_log('upload_root##' . $upload_root);
        $folders = array("mu-plugins" => 'WP_CONTENT/mu-plugins', "plugins" => $wp_plugins_dir, "uploads" => 'WP_UPLOADS', "themes" => $wp_themes_dir);
        
        if (!$push) {
            $sync_request_metadata = $this->get_sync_request_data();
        } 
        else {
            $sync_request_metadata = $data;
        }
        
        if (isset($sync_request_metadata['data']['sync-request-file'])) {
            $sync_request_file = $pull_path . "temp/" . $sync_request_metadata['data']['sync-request-file'];
        }
        
        if (isset($sync_request_metadata['data']['sql-data-file'])) {
            $sql_data_file = $pull_path . "temp/" . $sync_request_metadata['data']['sql-data-file'];
        }
        
        $this->sync_log("sql_data_file=" . $sql_data_file);
        
        $this->sync_log("----delete data-----");
        $this->sync_log("sync_request_file=" . $sync_request_file);
        if ($wp_filesystem->is_file($sync_request_file)) {
            $request_data = json_decode($wp_filesystem->get_contents($sync_request_file), true);
        }
        
        if (!$push) {
            $sync_request_data = $this->get_sync_request_data();
        } 
        else {
            $sync_request_data = $data;
        }
        
        $this->sync_log("----copy files-----");
        
        global $wpdb;
        $table_prefix = $wpdb->prefix;
        
        $old_table_prefix = $table_prefix;
        
        if (isset($sync_request_data['data'])) {
            $old_table_prefix = $sync_request_data['data']['table_prefix'];
        }
        
        foreach ($folders as $source => $destination) {
            $destFolder = str_replace('WP_CONTENT/', $content_path, $destination);
            $destFolder = str_replace('WP_UPLOADS', $upload_root, $destFolder);
            
            if (!$wp_filesystem->is_dir($destFolder)) {
                $wp_filesystem->mkdir($destFolder);
            }
            
            if ($wp_filesystem->is_dir($pull_path . $source)) {
                $this->sync_log("copy dir :: source=" . $pull_path . $source . "; destination=" . $destFolder);
                $result = copy_dir($pull_path . $source, $destFolder);
                if (is_wp_error($result)) {
                    return $result;
                }
            }
        }
        
        $this->sync_log("----delete files start -----");
        $this->delete_files($request_data);
        $this->sync_log("----delete files end -----");
        
        $oldValues = array();
        for ($i = 0; $i < count($applyOptions); $i++) {
            $oldValues[$applyOptions[$i]] = get_option($applyOptions[$i]);
        }

        $siteurl = get_option('siteurl');
        $home = get_option('home');
        
        if ($wp_filesystem->is_file($sql_data_file)) {
            $sql = $wp_filesystem->get_contents($sql_data_file);
            
            foreach ($oldValues as $name => $value) {
                $sql = str_replace('%cloudpress-variable-' . $name . '%', $value, $sql);
            }
            $sql = str_replace('%cloudpress-variable-tableprefix%', $table_prefix, $sql);
            
            $sql.= "SET autocommit=0;";
            $sql.= "UPDATE " . $table_prefix . "options SET `option_name`=REPLACE(`option_name`,'" . $old_table_prefix . "','" . $table_prefix . "') WHERE `option_name` LIKE '" . $old_table_prefix . "%';" . PHP_EOL;
            
            //fix update option is restricted on some hosts
            $sql.= "UPDATE " . $table_prefix . "options SET `option_value`='".$siteurl."' WHERE `option_name` = 'siteurl';".PHP_EOL;
            $sql.= "UPDATE " . $table_prefix . "options SET `option_value`='".$home."' WHERE `option_name` = 'home';".PHP_EOL;
            
            $sql.= "COMMIT;" . PHP_EOL;
            $sql.= "SET autocommit=1;" . PHP_EOL;
            
            $this->sync_log("-----------------restore from string start-------------------");
            $this->sync_log($sql);
            $this->sync_log("-----------------restore from string end-------------------");
            $this->restore_db_from_string($sql);
            
            $this->sync_log("old_table_prefix=$old_table_prefix;table_prefix=$table_prefix");
        }
        
        try {
            $db_upgrade_url = admin_url('upgrade.php?step=upgrade_db');
            wp_remote_post($db_upgrade_url, array('timeout' => 60));
        }
        catch(Exception $e) {
        }
        
        wp_cache_flush();
        wp_cache_delete('alloptions', 'options');
        
        global $wpdb;
        
        $wpdb->query("UPDATE $wpdb->posts wpp
      LEFT JOIN
      (SELECT comment_post_id AS c_post_id, count(*) AS cnt FROM $wpdb->comments
       WHERE comment_approved = 1 GROUP BY comment_post_id) wpc
      ON wpp.id=wpc.c_post_id
      SET wpp.comment_count=wpc.cnt
      WHERE wpp.post_type IN ('post', 'page')
            AND (wpp.comment_count!=wpc.cnt OR (wpp.comment_count != 0 AND wpc.cnt IS NULL));");
        
        $wpdb->flush();
        
        foreach ($oldValues as $name => $value) {
            update_option($name, $value);
        }
        
        $plugins_to_activate = array();
        if (count($activatePlugins)) {
            $plugins_dir = $content_path . "plugins/";
            $filelist = $wp_filesystem->dirlist($plugins_dir);
            foreach ($filelist as $file_name => $file) {
                if ($file['type'] == "d") {
                    $plugins = get_plugins("/" . $file_name);
                    foreach ($plugins as $plugin_php => $plugin) {
                        array_push($plugins_to_activate, $file_name . "/" . $plugin_php);
                    }
                }
            }
            
            $this->sync_log("----activate plugins-----");
            
            $activePlugins = get_option("active_plugins");
            for ($i = 0; $i < count($activatePlugins); $i++) {
                for ($j = 0; $j < count($plugins_to_activate); $j++) {
                    $plugin = $plugins_to_activate[$j];
                    
                    $this->sync_log($plugin . "--" . $activatePlugins[$i] . "--" . strpos($plugin, $activatePlugins[$i]));
                    
                    if (strpos($plugin, $activatePlugins[$i]) !== FALSE) {
                        array_push($activePlugins, $plugin);
                    }
                }
            }
            
            update_option("active_plugins", $activePlugins);
        }
        
        return true;
    }
}

class CPSyncUtils
{

    public static $cachedHashes;
    public static function hashes($way = "remote_to_local", $cpSync = null, $method = 'md5') {
        $start_time = microtime(true);

        $toExport = CloudPressDump::foldersToExport();
        $hashes = array();
        foreach ($toExport as $source => $data) {
            $obj = array();
            $folder = $data['folder'];
            $obj['list'] = CPSyncUtils::listDirectory($source, $data['exclude'], "", $method);
            $obj['hash'] = hash($method, json_encode($obj['list']), false);
            $hashes[$folder] = $obj;

            CPSyncUtils::save_hashes();
        }
        
        $exportedTables = CloudPressDump::tablesToExport();
        $tables = implode(",", $exportedTables);
        $checksumSql = "checksum table $tables extended";
        global $wpdb;
        
        $renamedTables = CloudPressDump::remove_table_prefix($exportedTables);
        $tables_hashes = array();
        $table_checksum = $wpdb->get_results($checksumSql);
        
        if ($cpSync) {
            $cpSync->sync_log('hashes##way=' . $way . "##" . json_encode($hashes));
            $cpSync->sync_log('exportedTables##' . json_encode($exportedTables));
            $cpSync->sync_log('renamedTables##' . json_encode($renamedTables));
            $cpSync->sync_log('table_checksum##' . json_encode($table_checksum));
            $cpSync->sync_log('hashtime##' . (microtime(true) - $start_time));
        }
        
        foreach ($table_checksum as $key => $table) {
            $tables_hashes[$renamedTables[$key]] = $table->Checksum;
        }
        
        $obj = array();
        $obj['tables'] = $tables_hashes;
        $obj['files'] = $hashes;
        
        return $obj;
    }
    
    public static function diffFiles($old, $new, $exclude = array()) {
        $diffs = array();
        $root_folders = array_merge($old, $new);
        
        if (CloudPressDump::$cpSync) {
            CloudPressDump::$cpSync->sync_log('diffFiles##' . json_encode($exclude));
        }
        
        // echo "-----------------root_folders---------------------".PHP_EOL;
        // echo json_encode($root_folders).PHP_EOL;
        // echo "-----------------22222222222222222---------------------".PHP_EOL;
        // echo json_encode($old).PHP_EOL;
        // echo "-----------------33333333333333333---------------------".PHP_EOL;
        // echo json_encode($new).PHP_EOL;
        // echo "-----------------end---------------------".PHP_EOL;
        
        foreach ($root_folders as $rootfolder => $struct) {
            $rootDiffs = array();
            
            // echo "-----------------root_folder---------------------".PHP_EOL;
            // echo "$rootfolder".PHP_EOL;
            
            $diffs[$rootfolder] = array();
            
            $skip = false;
            for ($i = 0; $i < count($exclude); $i++) {
                if (preg_match($exclude[$i], $rootfolder)) {
                    $skip = true;
                    break;
                }
            }
            if ($skip || $old[$rootfolder]['hash'] == $new[$rootfolder]['hash']) {
                continue;
            }
            
            $oldfiles = $old[$rootfolder]['list'];
            $newfiles = $new[$rootfolder]['list'];
            
            if (count($oldfiles)) {
                foreach ($oldfiles as $file => $hash) {
                    if (isset($newfiles[$file])) {
                        if ($newfiles[$file] != $hash) {
                            $rootDiffs[$file] = 'update';
                        }
                    } 
                    else {
                        $rootDiffs[$file] = 'delete';
                    }
                }
            }
            
            if (count($newfiles)) {
                foreach ($newfiles as $file => $hash) {
                    if (!isset($oldfiles[$file])) {
                        $rootDiffs[$file] = 'insert';
                    }
                }
            }
            
            $diffs[$rootfolder] = $rootDiffs;
        }
        return $diffs;
    }
    
    public static function diffTables($old, $new) {
        $diffs = array();
        
        foreach ($old as $table => $checksum) {
            if (isset($new[$table])) {
                if ($new[$table] != $checksum) {
                    $diffs[$table] = 'update';
                }
            } 
            else {
                $diffs[$table] = 'delete';
            }
        }
        
        foreach ($new as $table => $checksum) {
            if (!isset($old[$table])) {
                $diffs[$table] = 'insert';
            }
        }
        return $diffs;
    }
    
    public static function diff2Hashes($old_hashes, $new_hashes, $exclude) {
        if (!isset($exclude)) {
            $exclude = array('files' => array());
        }
        if (empty($old_hashes)) {
            $old_hashes = array("tables" => array(), "files" => array());
        }
        
        foreach ($new_hashes['files'] as $folder => $obj) {
            if (!isset($old_hashes[$folder])) {
                $old_hashes[$folder] = array("list" => array(), "hash" => "---");
            }
        }
        
        if (empty($new_hashes)) {
            $new_hashes = array("tables" => array(), "files" => array());
        }
        
        foreach ($old_hashes['files'] as $folder => $obj) {
            if (!isset($new_hashes[$folder])) {
                $new_hashes[$folder] = array("list" => array(), "hash" => "---");
            }
        }
        
        $tableDiffs = self::diffTables($old_hashes['tables'], $new_hashes['tables']);
        $fileDiff = self::diffFiles($old_hashes['files'], $new_hashes['files'], $exclude['files']);
        
        return array("tables" => $tableDiffs, "files" => $fileDiff);
    }
    
    
    public static function hash_file($file, $hash_method){
        if ($hash_method == "filesize") {
            return filesize($file);
        }

        if (!self::$cachedHashes) {
           self::$cachedHashes = array();
           self::load_hashes();
        }

        $hash = $file."##".filesize($file)."##".filemtime($file)."##".$hash_method;
        if (!isset(self::$cachedHashes[$hash])) {
            self::$cachedHashes[$hash] = hash_file($hash_method, $file, FALSE);//hash_file("crc32b", $file, FALSE);
        }

        return self::$cachedHashes[$hash];
    }

    public static function save_hashes(){
        global $wp_filesystem;
        $cache_file = $wp_filesystem->abspath() . "/" . CPFS::$sync_folder . "/cached_hash.json";
        file_put_contents($cache_file, json_encode(self::$cachedHashes));
    }

    public static function load_hashes(){
        global $wp_filesystem;
        $cache_file = $wp_filesystem->abspath() . "/" . CPFS::$sync_folder . "/cached_hash.json";
        if (!self::$cachedHashes) {
            self::$cachedHashes = array();
        }

        if (file_exists($cache_file)) {
            self::$cachedHashes = json_decode(file_get_contents($cache_file), true);
        }
    }

    public static function listDirectory($directory, $exclude, $root = "", $hash_method) {
        if (!is_dir($directory)) {
            return array();
        }
        
        if (!$root) {
            $root = $directory;
        }
        
        $files = array();
        $dir = dir($directory);
        
        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                
                $skip = false;
                foreach ($exclude as $item) {
                    if (preg_match($item, $file)) {
                        $skip = true;
                        break;
                    }
                }
                
                if (!$skip) {
                    $relFile = str_replace($root, '', $directory . '/' . $file);
                    if (is_dir($directory . '/' . $file)) {
                        $files[$relFile] = "";
                        $files = array_merge($files, self::listDirectory($directory . '/' . $file, $exclude, $root, $hash_method));
                    } 
                    else {
                        $files[$relFile] = self::hash_file($directory . '/' . $file, $hash_method);
                    }
                }
            }
        }
        
        $dir->close();
        
        return $files;
    }
    
    private function hashDirectory($directory) {
        if (!is_dir($directory)) {
            return false;
        }
        
        $files = array();
        $dir = dir($directory);
        
        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                if (is_dir($directory . '/' . $file)) {
                    $files[] = self::hashDirectory($directory . '/' . $file);
                } 
                else {
                    $files[] = md5_file($directory . '/' . $file);
                }
            }
        }
        
        $dir->close();
        
        return md5(implode('', $files));
    }
}
?>