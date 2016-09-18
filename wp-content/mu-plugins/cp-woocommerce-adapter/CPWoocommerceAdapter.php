<?php

function cp_woocommerce_dequeue_styles( $enqueue_styles ) {
	unset( $enqueue_styles['woocommerce-general'] );	// Remove the gloss
	// unset( $enqueue_styles['woocommerce-layout'] );		// Remove the layout
	// unset( $enqueue_styles['woocommerce-smallscreen'] );	// Remove the smallscreen optimisation
	return $enqueue_styles;
}

function cp_woocommerce_custom_template_parts($template){

	$old_template = $template;
	$template = str_replace("\\","/",$template);
	$template = explode("/", $template);
	$template = array_pop($template);

	$cp_woocommerce_templates = realpath(dirname(__FILE__). '/templates');
	if(file_exists($cp_woocommerce_templates ."/". $template)){
		return $cp_woocommerce_templates ."/". $template;
	} else {
		return $old_template;
	}
}

function cp_woocommerce_create_pages($pages){
	
	

	$template_dir = get_template_directory();
	$file_php = $template_dir . "/woocommerce.php";
	if(!file_exists($file_php)){
		CPWoocommerceAdapter::woocommerce_template('page');
	}

	CPWoocommerceAdapter::create_shop_page();

	unset($pages['shop']);
	return $pages;
}


class CPWoocommerceAdapter{
	static function init(){

		$activePlugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		if ($activePlugins &&  in_array( 'woocommerce/woocommerce.php',  $activePlugins) ) {
			
			// make theme compatible with woocommerce
			global $_wp_theme_features;
			$_wp_theme_features['woocommerce'] = true; 
			

			// adapt woocommerce to run with editor
			add_filter( 'woocommerce_enqueue_styles', 'cp_woocommerce_dequeue_styles' ); // remove styles that will break the editor
			add_filter('wc_get_template_part', 'cp_woocommerce_custom_template_parts');
			add_filter('woocommerce_create_pages', 'cp_woocommerce_create_pages');

			// [cp_woocommerce_content]
			add_shortcode( 'cp_woocommerce_content', array(__CLASS__,'woocommerce_content_handler') );
		}
	}

	static function woocommerce_content_handler(){
		self::woocommerce_content();
	}

	// Default WooCommerce content function overwritten to match with cloudpress editor
	static function woocommerce_content(){
		ob_start();
		
		echo ' <div class="woocommerce-wrapper">';
			if(is_singular('product')){
				while(have_posts()){
					the_post();
					wc_get_template_part( 'content', 'single-product' );
				}
			} else {

				echo '<div class="shop-header">';
					if ( apply_filters( 'woocommerce_show_page_title', true ) ) {
					   echo '<h1 class="page-title">'. woocommerce_page_title(false).'</h1>';
					}

					do_action( 'woocommerce_archive_description' );

					if (have_posts()){
						do_action('woocommerce_before_shop_loop'); 
					}
				echo '</div>';

				if(have_posts()){
					woocommerce_product_loop_start();
					woocommerce_product_subcategories();

					while (have_posts()) {
						 the_post();
						 wc_get_template_part( 'content', 'product' );
					}

					woocommerce_product_loop_end();
					do_action('woocommerce_after_shop_loop');
				} elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) {
					 wc_get_template( 'loop/no-products-found.php' );
				}
			}
		echo '</div>';

		$content = ob_get_contents();
		
		ob_end_clean();
		echo $content;
	}

	static function create_shop_page(){
		$shoppage_exists = new WP_Query("post_type=page&meta_key=cp_woocommerce_shop");
		if(!count($shoppage_exists->posts)){
			$shoppage = array(
				'post_content'=>'',
				'post_name' => 'shop',
				'post_name' => 'Shop',
				'post_title' => 'Shop',
				'post_status' => 'publish',
				'post_type' => 'page',
				'page_template'=>'woocommerce.php'
			);

			$shoppage_id = wp_insert_post($shoppage);
			update_post_meta($shoppage_id,'cp_woocommerce_shop','true');
		}
	}

	static function woocommerce_template($template){

		$template_dir = get_template_directory();
	    $file = $template_dir . "/woocommerce";
	    $start_from = $template_dir. "/". $template;

		// @copy($template_dir."/". $template . ".php", $file . ".php");
		// @copy($template_dir."/". $template . ".css", $file . ".css");

	    echo $start_from;

		$content = ($content = file_get_contents($start_from . ".php"))?$content:"";
		file_put_contents($file . ".php", $content);


		$content = ($content = file_get_contents($start_from . ".css"))?$content:"";
		file_put_contents($file . ".css", $content);



		if(file_exists($file.".php")){
			$content = file_get_contents($file.".php");
			$content = preg_replace("/\[page_content(.*)\]/", "[cp_woocommerce_content]", $content);
			file_put_contents($file . ".php", $content);
		}
	}

}


CPWoocommerceAdapter::init();