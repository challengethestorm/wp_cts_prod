<?php
/*
Template Name: Default Page Template
*/
?>
<!DOCTYPE html>
<html>
 <head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta charset="utf-8" />
  <title><?php wp_title(''); ?></title>
  <?php wp_head(); ?>
 </head>
 <body <?php body_class(""); ?>>
  <?php echo do_shortcode('[header name="default-inner-page-template"]'); ?>
  <div class="content">
   <?php echo do_shortcode('[page_content]');?>
  </div>
  <?php echo do_shortcode('[footer name="default-inner-page-template"]'); ?>
  <?php wp_footer(); ?>
 </body>

</html>

