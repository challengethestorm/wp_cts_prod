<?php global $ns_slidebar; ?>
<div id="ns-slidebar" class="init-pos close">
	<div id="ns-slidebar-control">
		<h3>
			<?php echo $ns_slidebar->settings['trigger_text']; ?>
			<?php if(!empty($ns_slidebar->settings['trigger_img'])) {
				echo "&nbsp;<img id='ns-slidebar-title-img' src='" . $ns_slidebar->settings['trigger_img'] . 
					 "' alt='" . $ns_slidebar->settings['trigger_text'] . "' />"; 
			} ?>
		</h3>
	</div>
	<div id="ns-slidebar-search-form">
		<?php 
			// use wp or theme searchform.php
			//get_search_form();
			
			// use custom slidebar search form
		?>
	<!--	<div class="ns-search-form ns-form-inline">
		<label class="ns-sr-only">RECENT POSTS</label> -->

		<div role="search" method="get" class="ns-search-form ns-form-inline" action="<?php echo esc_url(home_url('/')); ?>">
<!--
		<label class="ns-sr-only"><?php _e('Search for:', 'ns-slidebar'); ?></label>
		<div class="ns-input-group">
				<input type="search" value="<?php echo get_search_query(); ?>" name="s" class="ns-search-field ns-form-control" placeholder="<?php _e('What are you looking for?', 'ns-slidebar'); ?>">
				<span class="ns-input-group-btn">
					<button type="submit" class="ns-search-submit"><?php _e('Search', 'ns-slidebar'); ?></button>
		    	</span> 
			</div>	-->
		</div>
	</div>
	<div id="ns-slidebar-content">
		<div id="ns-slidebar-search-results">
			<div id="ns-slidebar-search-results-message"></div>
			<section class="ns-slidebar-search-result ns-slidebar-hidden">
				<h5 class="ns-slidebar-search-title"><a></a></h5>
				<strong class="ns-slidebar-search-post-type"></strong>
				<div class="ns-slidebar-search-excerpt"></div>
			</section>
			<div id="ns-slidebar-search-results-more"><?php echo $ns_slidebar->settings['more_text']; ?></div>
		</div>			
		<div id="ns-slidebar-widgets">
			<?php dynamic_sidebar('ns_slidebar'); ?>
		</div>
	</div>
</div>