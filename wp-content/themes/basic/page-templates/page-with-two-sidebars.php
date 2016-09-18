<?php
/*
Template Name: Page With Two Sidebars
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
  <?php echo do_shortcode('[header]'); ?>
  <div class="content">
   <div class="page-column gridContainer">
    <div class="page-row">
     <?php echo do_shortcode('[sidebar]');?>
     <?php echo do_shortcode('[page_content]');?>
     <?php echo do_shortcode('[sidebar name="secondary"]');?>
    </div>
   </div>
  </div>
  <?php echo do_shortcode('[footer]'); ?>
  <?php wp_footer(); ?>
 </body>

</html>