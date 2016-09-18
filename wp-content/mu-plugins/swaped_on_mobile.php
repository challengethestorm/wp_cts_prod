<?php
/*
Plugin Name: CloudPress Swap Mobile Fallback
Version: 1.0.0
Plugin URI: http://cloud-press.net
Description: CloudPress Swap Mobile Fallback
Author: CloudPress Team
Author URI: http://cloud-press.net
*/

add_action('wp_footer', 'cp_mobile_content_swap_fix');

function cp_is_mobile_device() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

function cp_mobile_content_swap_fix(){
	
	if(cp_is_mobile_device()):
		?>
			<style type="text/css" id="cp_mobile_parallax_fallback">
				.force-view{
					display:block!important;
					opacity:1!important;
					transition: all 800ms ease!important;
					width:100%!important;
					height:100%!important;
					transform: scale(1)!important;
				}
			</style>
			<script type="text/javascript">
				(function($) {
					jQuery(document).ready(function($) {
						$('[hover-fx] .swap-inner, [hover-fx] .overlay').addClass('force-view');
					});
				})(jQuery)
			</script>
		<?php
	 endif;
}