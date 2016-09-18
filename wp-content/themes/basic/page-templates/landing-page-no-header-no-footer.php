<?php
/*
Template Name: Landing Page - No header - No footer
*/
?>
<!DOCTYPE html>
<html>
 <head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta charset="utf-8" />
  <title><?php wp_title(''); ?></title>
  <?php wp_head(); ?>
  <?php echo do_shortcode('[xtd_drop_down_menu id="menu1" inherit=""]');?>
 </head>
 <body <?php body_class(""); ?>>

   <?php echo do_shortcode('[header name="landing-page-template"]'); ?>
  <div class="content">
   <?php echo do_shortcode('[page_content]'); 
   // add_mailchimp_embed(); 
   ?>

  </div>
  <?php echo do_shortcode('[footer name="landing-page-template"]'); ?>

  <?php wp_footer(); ?>
 </body>

</html>