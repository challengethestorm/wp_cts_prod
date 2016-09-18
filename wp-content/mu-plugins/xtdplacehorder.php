<?php

function enqueue_js_placeholder() {
	wp_enqueue_script( 'js-placeholder', site_url() . '/wp-content/mu-plugins/assets/jquery.placeholder.js',array('jquery'));
	
}

add_action( 'wp_enqueue_scripts', 'enqueue_js_placeholder' );


function exec_js_placeholder(){
	echo "<script>jQuery(function() {
	 jQuery('input, textarea').placeholder();
	});</script>";
}

add_action('wp_head','exec_js_placeholder');