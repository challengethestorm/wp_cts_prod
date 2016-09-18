<?php
/*
Plugin Name: WP Full Stripe Free
Plugin URI: http://paymentsplugin.com
Description: Free version of WP Full Stripe, a Stripe payments plugin.
Author: Mammothology
Version: 1.4.0
Author URI: http://mammothology.com
Text Domain: wp-full-stripe-free
Domain Path: /languages
*/

//defines
if ( ! defined( 'WP_FULL_STRIPE_NAME' ) ) {
	define( 'WP_FULL_STRIPE_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
}

if ( ! defined( 'WP_FULL_STRIPE_BASENAME' ) ) {
	define( 'WP_FULL_STRIPE_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'WP_FULL_STRIPE_DIR' ) ) {
	define( 'WP_FULL_STRIPE_DIR', WP_PLUGIN_DIR . '/' . WP_FULL_STRIPE_NAME );
}


//Stripe PHP library
if ( ! class_exists( 'Stripe' ) ) {
	include_once( 'stripe-php/lib/Stripe.php' );
}

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'wp-full-stripe-main.php';
register_activation_hook( __FILE__, array( 'MM_WPFSF', 'setup_db' ) );

function wp_full_stripe_free_load_plugin_textdomain() {
	load_plugin_textdomain( 'wp-full-stripe-free', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'wp_full_stripe_free_load_plugin_textdomain' );
