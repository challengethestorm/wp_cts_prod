<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: Layouts nav menu
	Plugin URI: 
	Description: Layouts nav menu
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/

// [nav_menu]

class XtdMaps{
	static function init(){
		add_shortcode( 'xtd_maps', array(__CLASS__,'handle_shortcode') );
		// add_action('init', array(__CLASS__, 'register_assets'));
		//add_action('wp_head', array(__CLASS__, 'display_assets'));
	}
	

	static function handle_shortcode($atts){
		self::register_assets();
		xtd_add_scripts(array("google-maps-api"));
		$atts = shortcode_atts(
			array(
				'id' => 'xtd_map_'.time(),
				'address' 	=> 'Bucharest',
				'zoom' => '65',
				'type' => 'ROADMAP',
				'lat' => '0',
				'long' => '0',
			),
			$atts
		);

		if (xtd_in_editor()) {

				$imageLink = plugins_url('', __FILE__) . '/gmaps.png';

			return '<div id="'.$atts['id'].'" style="background:#EEEEEE url('.$imageLink.') no-repeat center;"></div>';
		}

		$address = $atts['address'];
		$location = self::get_location( $address );
		$atts['zoom'] = round($atts['zoom'] * 0.21) ;


		if( !is_array( $location ) ){
			if( $atts['lat'] &&  $atts['long']){
				$location = array();
				$location['lat'] = $atts['lat'];
				$location['lng'] = $atts['long'];
				$address = $location['lat'] . ',' . $location['lng'] ;
			} else {
				return '<p style="color:red">Google Maps Error: '.$location.'</p>';
			}
		} else {
			if($atts['lat'] &&  $atts['long']){
				$location['lat'] = $atts['lat'];
				$location['lng'] = $atts['long'];
				$address = $location['lat'] . ',' . $location['lng'] ;
			} 
		}

		 $mapPreviewtype = "m";

		 switch (strtolower($atts['type'])) {
		 	case 'satellite':
		 		$mapPreviewtype = "k";
		 		break;
		 	case 'hybrid':
		 		$mapPreviewtype = "h";
		 		break;
		 	case 'terrain':
		 		$mapPreviewtype = "e";
		 		break;
		 }

		
		 	$src = 'http://maps.google.com/maps?f=q&hl=en&geocode='.
		 		'&q=' . urlencode($address) . 
		 		'&f=q&aq=0&ie=UTF8&hq='.
		 		// '&hnear=' . urlencode($address) . 
		 		'&t='. $mapPreviewtype .
		 		'&sll=' . $location['lat'] . ',' . $location['lng'] . 
		 		'&z=' . $atts['zoom'] . 
		 		'&iwloc=near&output=embed&om=';

		 		$uid = uniqid();

		 	return 	'<div id="'.$atts['id'].'">'.
		 			'	<style>div#'.$atts['id'].' * {max-width: none !important;box-sizing: content-box;vertical-align: inherit;font-size: initial;margin: auto;border: inherit;line-height: initial;}</style>'.
		 		   	'	<div class="map_content" style="width:100%;height:100%" ></div>'. "\n" .
					// '	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js"></script>'.
					'	    <script type="text/javascript">'. "\n" .
					'	      function initialize_'.$atts['id'].'() {'. "\n" .
					'			var windowWidth = typeof(window.outerWidth) !== "undefined" ? window.outerWidth : document.documentElement.clientWidth;'. "\n" .
					'	        var mapOptions = {'. "\n" .
					'	          center: { lat: '.$location['lat'].', lng: '.$location['lng'].'},'. "\n" .
					'	          scrollwheel: false, ' .  "\n" .
					'			  draggable: (windowWidth > 800),'. "\n" .
					'	          zoom: '. $atts['zoom'] . ',' . "\n" .
					'			  mapTypeId: google.maps.MapTypeId.'. strtoupper($atts['type']) . "\n" .
					'	        };'. "\n" .
					'			var mapHolder = jQuery("#'.$atts['id'].' .map_content");'. "\n" .
					'	        var map = new google.maps.Map(mapHolder[0],'. "\n" .
					'	            mapOptions);'. "\n" .
					'	 		var latLang = new google.maps.LatLng('.$location['lat'].', '.$location['lng'].');'. "\n" .
					'		        var marker = new google.maps.Marker({'. "\n" .
					'	            position: latLang,'. "\n" .
					'	            map: map'. "\n" .
					'	        });'. "\n" .
					'			if(windowWidth < 800) { mapHolder.click(function() { map.set("draggable", true); }); }'. "\n" .
					'	      }'. "\n" .
					'	      jQuery(document).ready(function(){ initialize_'.$atts['id'].'() })'. "\n" .
					'	    </script>'. "\n" .
		 			'</div>';
		
			

		

	}

	static function get_location( $address, $force_refresh = false ) {

	    $address_hash = md5( $address );

	    $coordinates = get_transient( $address_hash );

	    if ($force_refresh || $coordinates === false) {

	    	$args       = array( 'address' => urlencode( $address ), 'sensor' => 'false' );
	    	$url        = add_query_arg( $args, 'http://maps.googleapis.com/maps/api/geocode/json' );
	     	$response 	= wp_remote_get( $url );

	     	if( is_wp_error( $response ) )
	     		return;

	     	$data = wp_remote_retrieve_body( $response );



	     	if( is_wp_error( $data ) )
	     		return;

			if ( $response['response']['code'] == 200 ) {

				$data = json_decode( $data );

				if ( $data->status === 'OK' ) {

				  	$coordinates = $data->results[0]->geometry->location;

				  	$cache_value['lat'] 	= $coordinates->lat;
				  	$cache_value['lng'] 	= $coordinates->lng;
				  	$cache_value['address'] = (string) $data->results[0]->formatted_address;

				  	// cache coordinates for 3 months
				  	set_transient($address_hash, $cache_value, 3600*24*30*3);
				  	$data = $cache_value;

				} elseif ( $data->status === 'ZERO_RESULTS' ) {
				  	return __( 'No location found for the entered address.', 'pw-maps' );
				} elseif( $data->status === 'INVALID_REQUEST' ) {
				   	return __( 'Invalid request. Did you enter an address?', 'pw-maps' );
				} else {
					return __( 'Something went wrong while retrieving your map, please ensure you have entered the short code correctly.', 'pw-maps' );
				}

			} else {
			 	return __( 'Unable to contact Google API service.', 'pw-maps' );
			}

	    } else {
	       // return cached results
	       $data = $coordinates;
    	}

    	return $data;
	}


	static function register_assets(){
		wp_register_script( 'google-maps-api', 'https://maps.googleapis.com/maps/api/js' );
	
	}




	static function display_assets() {
		// wp_print_scripts( 'google-maps-api' );
		
	}
}

XtdMaps::init();