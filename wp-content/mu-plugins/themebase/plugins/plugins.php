<?php
    
  if(!function_exists('get_plugin_data_folder')) {
    function get_plugin_data_folder($pluginName=""){
      $themefolder = wp_get_theme()->get_template();

      $folder = wp_get_theme()->get_template_directory() . '/plugins-data';
      
      if ($themefolder) {
        if(!file_exists($folder)){
          mkdir($folder,0771,true);
        }
      }

      if($pluginName){
        $folder .= '/' . $pluginName;
      }

      if ($themefolder) {
        if(!file_exists($folder)){
          mkdir($folder, 0771,true);
        }
      }

      return $folder;
    }
  }

  function get_plugin_data_url($pluginName=""){

    $url = get_template_directory_uri() . '/plugins-data';

    if($pluginName){
      $url .= '/' . $pluginName;
    }
    return $url;
  }
?>