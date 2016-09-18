<?php

add_action("wp_head", "xtd_look_for_favicon");
add_action("admin_head", "xtd_look_for_favicon");

function xtd_look_for_favicon(){
	$wp_path  = dirname(__FILE__) . '/../../';
	if(file_exists($wp_path . '/favicon.ico')){
		?>
			<link rel="icon" href="<?php bloginfo('url'); ?>/favicon.ico?t=<?php echo time() ?>" type="image/x-icon" />
			<link rel="shortcut icon" href="<?php bloginfo('url'); ?>/favicon.ico?t=<?php echo time() ?>" type="image/x-icon" />
		<?php
	}
}

