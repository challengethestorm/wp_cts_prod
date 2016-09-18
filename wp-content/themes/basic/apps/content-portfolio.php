<div class="contentswap-effect contentswapporfolioarchive" hover-fx="contentswapporfolioarchive" id="contentswapporfolioarchive">
  <div class="contentswapporfolioarchive_content initial-image">
    <div class="pa-image-holder" style="background-image:url(<?php echo do_shortcode( '[cp_post_meta name="Thumb Image"]'); ?>)"></div>
  </div>
  <div class="overlay" style="display: none; opacity: 0;"></div>
  <div class="swap-inner" style="opacity: 0; display: none;">
   <a class="post-permalink" href="<?php echo do_shortcode('[cp_post_meta name="cp_post_permalink"]') ?>">Details</a>
  </div>
</div>

<h2 class="heading5"><?php echo do_shortcode('[cp_post_meta name="cp_title"]'); ?></h2>
<p class="paragraph3">
  <?php echo do_shortcode('[tag_post_excerpt]'); ?>
</p>
  <?php echo do_shortcode('[tag_post_tags sep="" before=""]'); ?>
