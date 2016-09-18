<?php

$xtd_scripts = array();
$xtd_styles = array();


function xtd_create_preview_styles($styles_handles) {
    $styles = "";
    global $xtd_shortcode_preview;
    if ($xtd_shortcode_preview || xtd_in_html_preview()) {
        ob_start();
        wp_print_styles($styles_handles);
        $styles = ob_get_contents();
        ob_end_clean();
        global $xtd_original_template;
        if ($xtd_original_template) {
          $styles = str_replace("/.decorated/", "/".$xtd_original_template."/", $styles);
        }
        $styles = '<!-- addtohead="'.esc_attr($styles).'" -->';
    }
    return $styles;
}

function xtd_add_scripts($scripts_handles) {
  global $xtd_scripts;
  if (!is_array($scripts_handles)) {
    $scripts_handles = array();
  }
  
  if (is_array($xtd_scripts)) {
    $xtd_scripts = array_merge($xtd_scripts, $scripts_handles);
  } else {
    $xtd_scripts = $scripts_handles;
  }
}

function xtd_add_styles($styles_handles) {
  global $xtd_styles; 
  if (!is_array($xtd_styles)) {
    $xtd_styles = array();
  }
  $xtd_styles = array_merge($xtd_styles, $styles_handles);
}

function xtd_in_editor(){
  return (isset($_REQUEST['decorate']) && $_REQUEST['decorate']) ||  (isset($_REQUEST['get_shortcode_preview']) && $_REQUEST['get_shortcode_preview']);
}

function xtd_in_html_preview(){
  return isset($_REQUEST['htmlpreview']);
}

function xtd_in_preview(){
  return isset($_REQUEST['previd']);
}

function theme_echo_head(){
    echo "{xtd_wp_head}";
}

add_action('wp_head', 'theme_echo_head', 1000);


function theme_wp_footer() {
    $included_files = get_included_files();
    $check = false;
    global $xtd_head;

    $handles = array();
    $handles[] = 'reset';
    $handles[] = 'blank-style';
    for ($i=0; $i < count($included_files); $i++) { 
        $file = $included_files[$i];
        if ($check) {
            $cssfile = str_replace(".php", ".css", $file);
            if (file_exists($cssfile)) {
                $name = basename($cssfile, '.css');
                $path = str_replace(realpath(get_template_directory()), "", realpath($cssfile));
                $path = str_replace('\\','/',$path);
                if (strpos($path, 'page-templates') !== FALSE) {
                  $handles[] = 'page-style';
                }
                $handles[] = $name;
                wp_register_style($name, get_template_directory_uri()."".$path, array(), '');
            }
        }

        if (preg_match('/(includes.theme\.php)/', $file)) {
            $check = true;
        }
    }


    global $xtd_scripts;
    global $xtd_styles;
    ob_start();
    wp_print_styles($xtd_styles);
    wp_print_styles($handles);
    wp_print_scripts($xtd_scripts);
    $xtd_head = ob_get_contents();

    if (xtd_in_editor()) {
      $cssfiles = array();
      $links = array();
      $replace = array();
      preg_match_all('/href=[\'"]+([^\'"]+)[\'"]+/i', $xtd_head, $cssfiles);
      for ($i=0; $i < count($cssfiles[1]); $i++) {
        $exp = explode("?", $cssfiles[1][$i]);
        $url = $exp[0];
        $file = str_replace(get_template_directory_uri(), get_template_directory(), $url);
        $links[$cssfiles[1][$i]] = file_get_contents($file);
        //$links[$cssfiles[1][$i]."_f"] = $file;
      }

      //foreach ($replace as $key => $value) {
      //  $xtd_head = str_replace($key, $value, $xtd_head);
      //}

       $xtd_head = preg_replace_callback(
        '/href=[\'"]+([^\'"]+)[\'"]+/s',
        function ($matches) {
          $url = $matches[1];
          $parts = explode("/", $url);
          array_pop($parts);
          array_push($parts, 'dummy.css');
          $new_url = implode("/", $parts);

          return 'href="'.$new_url.'" data-dummy-href="'.$url.'"';
        },
        $xtd_head
      );


      $xtd_head = $xtd_head.'<!-- cssfiles="'.json_encode($links).'" -->';
    }
    if (xtd_in_preview()) {
        $random = time();
        $xtd_head = preg_replace('/\.css\?ver=[^\'"]+/i', ".css?ver=$random", $xtd_head);
    }

    ob_end_clean();

}

add_action('wp_footer', 'theme_wp_footer');


function make_sections_unique($buffer) {
  if (!function_exists("nextSectionID")) {
    return $buffer;
  }
  global $cp_ids;
  $cp_ids = array();
  $buffer = preg_replace_callback(
    '/(<!-- SectionBegin id=[\'"]+)(.+?)([\'"]+)/s',
    function ($matches) {
      global $cp_ids;
      $uid = nextSectionID();
      array_push($cp_ids, array("old" => $matches[2], "new" => $uid));
      return $matches[1].$uid.$matches[3];
    },
    $buffer
  );
  foreach ($cp_ids as $key => $value) {
    $buffer = preg_replace('/'.$value['old'].'/s', $value['new'], $buffer, 1);
  }
  return $buffer;
}

function xtd_unparse_url($parsed_url) { 
  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
  $pass     = ($user || $pass) ? "$pass@" : ''; 
  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
  return "$scheme$user$pass$host$port$path$query$fragment"; 
} 

function theme_head_buffer($buffer) {
    global $xtd_head;
    if (xtd_in_preview()) {
        $buffer = preg_replace_callback(
          '/href=[\'"]([^\'"]+)[\'"]/s',
          function ($matches) {
            $url = $matches[1];
            if (preg_match('/:\/\//', $url)) {
              $obj = parse_url($url);
              if (!isset($obj['query'])) {
                $obj['query'] = "";
            } 
              $obj['query'] .= "previd=".$_REQUEST['previd'];
              return "href='".xtd_unparse_url($obj)."'";
            }
            return $matches[0];
          },
          $buffer
        );
    }
    global $xtd_decorate_files;
    if ($xtd_decorate_files) {
      global $xtd_original_template;
      if ($xtd_original_template) {
        $xtd_head = str_replace("/.decorated/", "/".$xtd_original_template."/", $xtd_head);
        $buffer = str_replace("/.decorated/", "/".$xtd_original_template."/", $buffer);
      }
    }
    return make_sections_unique(str_replace("{xtd_wp_head}", $xtd_head, $buffer));
}

function parse_template_content($template = "") {
    ob_start('theme_head_buffer');
    return $template;
}

add_action('template_include', 'parse_template_content');

add_action('init', 'theme_register_assets');

function  theme_register_assets() {
   wp_register_style('reset', get_template_directory_uri() . '/reset.css', 0, false, 'all');
  wp_register_style('blank-style', get_stylesheet_uri(), array('reset'));
  wp_register_style('page-style', get_template_directory_uri() . '/page.css', 0, false, 'all');
  wp_register_script('browser-compatibility', get_template_directory_uri() . '/js/browser-compatibility.js');
  xtd_add_scripts(array('browser-compatibility'));
}

?>