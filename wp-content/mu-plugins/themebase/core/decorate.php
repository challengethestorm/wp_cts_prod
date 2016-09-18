<?php
  
  function generate_tags_ul($type) {
    global $xtd_decorate_files;
    if(!$xtd_decorate_files){
      return "";
    }

    $shortcodes = array();
    global $blank_tags_shortcodes;
    if (isset($blank_tags_shortcodes[$type])) {
      $shortcodes = $blank_tags_shortcodes[$type];
    }

    if (count($shortcodes) == 0) {
      return "";
    }
    
    $tags = $shortcodes;
    $tags_str = '';
    foreach ($tags as $key => $value) {
      $commentedTag = "<?php echo do_shortcode( '[".$value."]'); ?>";
      if(function_exists('add_section_comments')) {
        $commentedTag = add_section_comments(clean_internal_comments($commentedTag));
      } else {
        $commentedTag = '['.$value.']';
      }

      $shortcode_value = do_shortcode('['.$value.']');
      if(strpos($commentedTag, "<!--") === 0){
          $commentedTag = preg_replace("/-->.*?<!--/s", '-->'. $shortcode_value.'<!--', $commentedTag);
      } else {
          // in attribute tags
          $commentedTag = preg_replace("/\*\/.*?\/\*/s", '*/'.$shortcode_value.'/*', $commentedTag);
      }

      $escaped = esc_attr($value);
      $tags_str .= "<li data-id=\"[$escaped]\">".$commentedTag."</li>";
    }
    return "<ul data-id='editor-dummy-modules' style='display:none'>".$tags_str."</ul>";
  }

  function blank_generate_loop_tags($current_template) {
    if (in_the_loop()) {
      $type = get_post_type(get_the_ID());
    } else {
      $type = get_post_type(get_the_ID());

      global $template;

      //skip dummy ul for main template//
      if ($type == "page" || $type == "post" || strpos($template, $current_template) !== FALSE) {
        $type = "";
      }
    }
    return generate_tags_ul($type);
  }

  function xtd_shutdown_clean() {
      if (isset($_GET['cleanfiles'])) {
          xtd_remove_files_decorations();
      }
  }
  
  add_action('shutdown', 'xtd_shutdown_clean');


?>