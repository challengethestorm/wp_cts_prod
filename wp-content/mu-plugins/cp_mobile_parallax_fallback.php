<?php
/*
Plugin Name: CloudPress Parallax Mobile Fallback
Version: 1.0.0
Plugin URI: http://cloud-press.net
Description:  CloudPress parallax mobile fallback
Author: CloudPress Team
Author URI: http://cloud-press.net
*/

add_action('wp_head', 'cp_mobile_parallax_fallback');


function cp_mobile_parallax_fallback(){
	?>
		<style type="text/css" id="cp_mobile_parallax_fallback"></style>
		<script type="text/javascript">
		    (function($) {
		        var styleEl = document.getElementById('cp_mobile_parallax_fallback');

		        function supportsFixedBackground() {
		            try {
		                var style = document.body.style;
		                if (!("backgroundAttachment" in style)) return false;
		                var oldValue = style.backgroundAttachment;
		                style.backgroundAttachment = "fixed";
		                var isSupported = (style.backgroundAttachment === "fixed");
		                style.backgroundAttachment = oldValue;
		                return isSupported;
		            } catch (e) {
		                return false;
		            }
		        }
		        
		        jQuery(document).ready(function($) {
		            if (!supportsFixedBackground()) {
		                styleEl.outerHTML = '<style type="text/css">*{background-attachment:scroll !important; background-repeat:repeat;}</style>';
		            }
		        });
		    })(jQuery)
		</script>
	<?php
}