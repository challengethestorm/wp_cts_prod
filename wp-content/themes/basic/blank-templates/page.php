<?php
/*
Template Name: Default Page Template
*/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> >
 <head>
  <meta charset="utf-8" />
  <title><?php wp_title(''); ?></title>
  <?php wp_head(); ?>
  <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>
 </head>
 <body <?php body_class(); ?> >
  <?php echo do_shortcode('[header]'); ?>
  <div class="content">
      <?php echo do_shortcode('[page_content]');?>
  </div>
  <?php echo do_shortcode('[footer]'); ?>
  <?php wp_footer(); ?>
 </body>

</html>