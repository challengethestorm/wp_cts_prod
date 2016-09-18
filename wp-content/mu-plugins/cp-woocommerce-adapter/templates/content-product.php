<?php
/**
 * The template for displaying product content within loops.
 *
 *
 * @author 		Iulian Palade
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) )
	$woocommerce_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );


// Ensure visibility
if ( ! $product || ! $product->is_visible() )
	return;

// Increase loop count
$woocommerce_loop['loop']++;
?>


<li class="product-item">
	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

	<a class="product-info" href="<?php the_permalink(); ?>">
		<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>
		<h3><?php the_title(); ?></h3>
		<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
	</a>

	<div class="add-to-cart">
		<?php do_action( 'woocommerce_after_shop_loop_item' ); 	?>
	</div>
</li>
