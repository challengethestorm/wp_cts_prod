<?php

/**
 * 	@package ExtendStudio
 * @version 1.0
 *
 */


/*
	Plugin Name: SimpleSlider
	Plugin URI:   
	Description: Layouts simple slider
	Author: Extend Studio
	Version: 1
	Author URI: extendstudio.com
*/


class XtdFacebook{
	static $id ="";
	static $config ="";
 	static $settings = "";


	static function init(){
			add_shortcode( 'xtd_fb', array(__CLASS__,'handle_shortcode') );
			add_action('init', array(__CLASS__, 'register_assets'));
			add_action('wp_head', array(__CLASS__, 'display_assets'));
	}
	

	static function handle_shortcode($atts){

		
		ob_start();
			the_permalink();
 	 		$defaulHref=  ob_get_contents();
 	 	ob_end_clean();

 	 	

 	 	$id = isset($atts['id']) ? $atts['id'] : 'FB_' . time();
		$type = $atts['type'];
 	 	$href = self::getAtt($atts,'href',$defaulHref);
		$width= self::getAtt($atts,'width','200');
		$layout = self::getAtt($atts,'layout','standard');
		$action = self::getAtt($atts,'action','like');
		$show_faces = self::getAtt($atts,'show_faces','false');
		$share = self::getAtt($atts,'share','true');

		if($type == "share"){
	    	$width = "48";
	    	$height = "14";
	    } else {
	    	$height =self::getAtt($atts,'height','100');
	    }

		$color = self::getAtt($atts,'color','light','light');
		$posts = self::getAtt($atts,'posts','100');
		$appID = self::getAtt($atts,'appID');
	    $maxage = self::getAtt($atts,'maxage');
	    $header = self::getAtt($atts,'header','true');
	    $maxrows = self::getAtt($atts,'maxrows',1);
	    $photosize = self::getAtt($atts,'photosize','medium');
	    $show_count = self::getAtt($atts,'show_count','true');




	    if($href == ""){
 	 		$href = 'http' .  ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")? "s":"") . "://" . $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 	 	}

	    if(xtd_in_editor() && !in_array($type, array('share','like','follow','facepile'))) { // show placeholder in editor
		 	self::$settings['id'] = $id;
		    self::$settings['type'] = $type;
		    self::$settings['href'] = $href;
		    self::$settings['layout'] = $layout;
		    self::$settings['action'] = $action;
		    self::$settings['show_faces'] = $show_faces;
		    self::$settings['share'] = $share;
		    self::$settings['width'] = $width;
		    self::$settings['height'] = $height;
		    self::$settings['color'] = $color;
		    self::$settings['posts'] = $posts;
		    self::$settings['appID'] = $appID;
		    self::$settings['maxage'] = $maxage;
		    self::$settings['header'] = $header;
		    self::$settings['maxrows'] = $maxrows;
		    self::$settings['photosize'] = $photosize;
		    self::$settings['show_count'] = $show_count;
			return  '<div id='.$id.'>' . self::getPlaceHolder() . "</div>";
		} 

		
		 $originalHref =  $href ;
		 $href = urlencode($href);
		 ob_start();
		 switch ($type) {
		 	case 'like':
		 		echo '<iframe src="//www.facebook.com/plugins/like.php?href='.$href . /*'&width='. $width . '&height='. $height . */'&layout=' . $layout. '&action=' . $action . 
		 			'&show_faces=' . $show_faces . '&share='. $share .'"  scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
		 		break;
		 
		 	case 'share':
		 		// self::echoFBJSAPI();
				// echo '<div class="fb-share-button" data-href="'.$href.'" style="width:48px;height:14px;display:inline-block;" ></div>';
				if($layout == 'standard')
					$layout = 'icon_link';
				echo '<iframe src="//www.facebook.com/plugins/share_button.php?href='. $href.
					'&layout='. $layout.'" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
				break;

			case 'follow':
				echo '<iframe style="border:none; overflow:hidden;width:100%;height:100%;display:inline-block;background-color:'. (($color == "dark")?"rgb(51, 51, 51);":"none") .'" src="//www.facebook.com/plugins/follow.php?href='
					.$href.'&colorscheme='.$color.'&layout='.$layout.'&show_faces='.$show_faces.
					'" scrolling="no" frameborder="0" allowTransparency="true"></iframe>';
				break;

			case 'comments':
			    echo'<div class="fb-comments" style="width:'.$width.'px;min-height:'.$height.'px;background-color:'. (($color == "dark")?"rgb(51, 51, 51);":"none") .'" data-href="'.$originalHref.'" data-numposts="'.$posts.'" data-colorscheme="'.$color.'" data-width="'.$width.' "data-height="'.$height.'">Facebook Comments</div>';
			    self::echoFBJSAPI();
				break;

			case 'feed':
			    self::echoFBJSAPI();
			    echo'<div class="fb-activity" style="width:'.$width.'px;min-height:'.$height.'px;" data-app-id="'.$appID.'" data-site="'.$originalHref.'" data-action="'.$action.'" data-width="'.$width.'" data-height="'
			    	.$height.'" data-max-age="'.$maxage.'" data-colorscheme="'.$color.'" data-header="'.$header.'"></div>';
				break;

			case 'facepile':
			    echo'<iframe style="background-color:'. (($color == "dark")?"rgb(51, 51, 51);":"none") .'" src="//www.facebook.com/plugins/facepile.php?app_id='.$appID.'href='.
			    	$href.'&action'.$action.'&width='.$width.'&height='.$height.'&max_rows='.
			    	$maxrows.'colorscheme='.$color.'&size='.$photosize.'&show_count='.$show_count.
			    	'"  style="width:'.$width.'px;min-height:'.$height.'px;" scrolling="no" frameborder="0" style="border:none; overflow:hidden;" allowTransparency="true"></iframe>';
				break;

		 }

		 $content = '<div id='.$id.'>' . ob_get_contents() . "</div>";
		 ob_end_clean();


		 return $content;
		
	}

	static function getPlaceHolder(){

		$style ="";
		switch (self::$settings['type']) {
			case 'share':
				return ''.
					'<div class="fb-share-button" data-href="'.self::$settings['href'].'" style="width:48px;height:14px;display:inline-block;" >'.
						'<iframe style="width:48px;height:14px;display:inline-block;" name="f166f50cf8" width="'.self::$settings['width'].'" height="'.self::$settings['width'].'" frameborder="0" allowtransparency="true" scrolling="no" title="fb:share_button Facebook Social Plugin" src="https://www.facebook.com/plugins/share_button.php?app_id=113869198637480&amp;channel=https%3A%2F%2Fs-static.ak.facebook.com%2Fconnect%2Fxd_arbiter%2FoDB-fAAStWy.js%3Fversion%3D41%23cb%3Df11f7ff16%26domain%3Ddevelopers.facebook.com%26origin%3Dhttps%253A%252F%252Fdevelopers.facebook.com%252Ff2683bcb5%26relation%3Dparent.parent&amp;href=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;locale=en_US&amp;sdk=joey" style="border: none; visibility: visible; width: 48px; height: 14px;" class=""></iframe>'.
					'</div>';
				break;


			case 'feed':
				$imageName = array("feed");
				$style = 'background-color:#3A5998;';
				$style .= 'background-position:center center;';
				$class = "fb-activity";
				break;

			case 'comments':
				$imageName = array("comments");
				$style = 'background-color:#3A5998;';
				$style .= 'background-position:center center;';
				self::$settings['height'] = 300;
				$class = "fb-comments";
				break;
			case 'facepile':
				$imageName = array("comments");

		}

		$imageLink = plugins_url('', __FILE__) . '/assets/ph/' . implode('_', $imageName) . '.png';
		// return '<iframe style="width:100%;height:100%;background:url('.$imageLink.') no-repeat; '.$style.'"></iframe>';
		return '<div class="'.$class.'" style="width:'.self::$settings['width'] .'px;min-height:200px;background:url('.$imageLink.') no-repeat; '.$style.'"></div>';
	}


	static function getAtt($atts,$attname, $falseResponse=''){
		if(isset($atts[$attname]) && $atts[$attname] ){
			return $atts[$attname];
		} else {
			return $falseResponse;
		}
	}

	static function echoFBJSAPI(){
			echo '<div id="fb-root"></div>
					<script>
					jQuery(document).ready(function(){
						(function(d, s, id) {
						  var js, fjs = d.getElementsByTagName(s)[0];
						  if (d.getElementById(id)) return;
						  js = d.createElement(s); js.id = id;
						  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.0";
						  fjs.parentNode.insertBefore(js, fjs);
						}(document, "script", "facebook-jssdk"));
					})</script>';
	}

	static function register_assets(){
		
		
	}

	static function display_assets() {

	}
}

XtdFacebook::init();