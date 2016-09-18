<?php
/*
Template Name: Default inner page template
*/
?>
<!DOCTYPE html>

 <head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta charset="utf-8" />
  <title><?php wp_title(''); ?></title>
  <?php wp_head(); 

  ?>
 </head>

 
  <!-- <div class="widget-area login-sidebar"><?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('header_sidebar') ) : endif; ?>
  </div> -->
  <?php echo do_shortcode('[header name="default-inner-page-template"]'); ?>
  <div class="content">
   <?php echo do_shortcode('[page_content]'); ?>
  </div>

<div class="testshortcode">
 <?php echo do_shortcode('[wherepub]'); ?>
 </div>
  <?php echo do_shortcode('[footer name="default-inner-page-template"]'); ?>
  <?php wp_footer(); ?>
 

