<?php
$cp_simple_tags = array(
		'cp_title',
		'cp_thumbnail',
		'cp_author',
		'cp_author_image',
	);
$cp_in_attribute_tags = array(
		'cp_post_permalink', 
		'cp_shortlink',
		'cp_author_link',
		'cp_author_posts_links',
	);
add_shortcode('cp_post_meta','handle_cp_post_meta');
add_filter('xtd_in_attribute_shortcode_filter', 'cp_attribute_shortcode_filter', 10, 2); // 2 atribute
function get_all_cp_post_meta_shortcodes($with_in_attr=false){
    global $cp_simple_tags;
    global $cp_in_attribute_tags;
    $to_return = array();
if (is_array($cp_simple_tags)) {
    foreach ($cp_simple_tags as $tag) {
       $to_return[] = "cp_post_meta name=\"$tag\"";
    }
}
if (is_array($cp_in_attribute_tags)) {
    if($with_in_attr){
        foreach ($cp_in_attribute_tags as $tag) {
             $to_return[] = "cp_post_meta name=\"$tag\"";
        }
    }
}
    return $to_return;
}
 function handle_cp_post_meta($atts) {
        global $cp_in_attribute_tags;
        global $cp_simple_tags;
        $a = shortcode_atts(array(
		    'name' => ''
                ), $atts);
        $name = trim($a['name']);
       
        if(in_array($name, $cp_in_attribute_tags) || in_array($name, $cp_simple_tags)  ){
            return call_user_func_array ('handle_' . $name, array($atts));
        }
        if($name == ""){
            ob_start();
                the_permalink();
            $result = ob_get_contents();
            ob_end_clean();
            return $result;
        }
        $meta = get_post_meta(get_the_ID(), $name);
        ob_start();
            echo CloudPressApps::render_post_custom_fields($meta, get_post_type(), $a['name']);
            $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
function cp_attribute_shortcode_filter($response,$shortcode){
    global $post;
    global $cp_in_attribute_tags;
    try {
        if (strpos($shortcode, 'cp_post_meta') !== FALSE) {
            $matches = array();
            $shortcode = html_entity_decode($shortcode);
            preg_match('/name="(.*?)"/s', $shortcode, $matches);
            $field_name = array_pop($matches);
            if(in_array($field_name, $cp_in_attribute_tags)){
                return true;
            }
            $app = CloudPressApps::get_app_by_slug($post->post_type);
            $fields = $app['fields'];
            if (!isset($fields[$field_name])) {
                return false;
            }
            switch ($fields[$field_name]) {
                case "text":
                case "video";
                    $response = false;
                    break;
            }
        }
    } catch (Exception $e) {
        
    }
    return $response;
}
function handle_cp_title($atts){
    ob_start();
        the_title();
        $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
function handle_cp_thumbnail($atts){
    $args = array(
        'size' => 'full'
    );
    ob_start();
    
    if (has_post_thumbnail()) {
        the_post_thumbnail($args['size']);
    } else {
        if(xtd_in_editor()) {
            echo '<img class="wp-post-image" src="'.  get_site_url() .'/wp-content/uploads/2014/09/placeholder_image.png"></img>';
        }
    }
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
function handle_cp_author($atts){
    ob_start();
        the_author();
        $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
function handle_cp_author_image($atts){
    $atts = shortcode_atts(
        array(
            'size' => 64
        ),
        $atts
    );
    ob_start();
    echo get_avatar(get_the_author_meta('ID'),$atts['size']);
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}
function handle_cp_post_permalink($atts){
    ob_start();
        the_permalink();
        $result = ob_get_contents();
    ob_end_clean();
    return $result;
}
function handle_cp_shortlink($atts){
   
    $post = get_post();
    $result = wp_get_shortlink( $post->ID );
    return $result;
}
function handle_cp_author_link($atts){
    $post = get_post();
    $url =  get_the_author_meta('url',$post->post_author);
    // if url is not set for the user use users post url
    if(!$url){
        $url = handle_cp_author_posts_links($atts);
    };
    return $url;
}
function handle_cp_author_posts_links($atts){
    global $authordata;
    return get_author_posts_url( $authordata->ID, $authordata->user_nicename );
}
$tags_types = array('page','post');
foreach ($tags_types as  $tag_type) {
    if(is_array($blank_tags_shortcodes[$tag_type])){
        $blank_tags_shortcodes[$tag_type] = array_merge($blank_tags_shortcodes[$tag_type],get_all_cp_post_meta_shortcodes(true));
    } else {
        $blank_tags_shortcodes[$tag_type] = get_all_cp_post_meta_shortcodes(true);
    }
}
