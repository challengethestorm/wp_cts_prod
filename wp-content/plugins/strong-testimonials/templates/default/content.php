<?php
/**
 * Template Name: Default
 * Description: The default template.
 */
?>
<!-- Strong Testimonials: Default Template -->
<?php do_action( 'wpmtst_before_view' ); ?>

<div class="strong-view <?php wpmtst_container_class(); ?>">
	<?php do_action( 'wpmtst_view_header' ); ?>

	<div class="strong-content <?php wpmtst_content_class(); ?>">

		<?php while ( $query->have_posts() ) : $query->the_post(); ?>
		<div class="<?php wpmtst_post_class(); ?>">

			<div class="testimonial-inner">
				<?php do_action( 'wpmtst_before_testimonial' ); ?>

				<?php wpmtst_the_title( '<h3 class="testimonial-heading">', '</h3>' ); ?>

				<div class="testimonial-content">
					<?php wpmtst_the_thumbnail(); ?>
					<div class="maybe-clear"></div>
					<?php wpmtst_the_content(); ?>
				</div>

				<div class="testimonial-client">
					<?php wpmtst_the_client(); ?>
				</div>
				<div class="clear"></div>

				<?php do_action( 'wpmtst_after_testimonial' ); ?>
			</div>

		</div>
		<?php endwhile; ?>

		<?php do_action( 'wpmtst_after_content' ); ?>
	</div>

	<?php do_action( 'wpmtst_view_footer' ); ?>
</div>

<?php do_action( 'wpmtst_after_view' ); ?>
