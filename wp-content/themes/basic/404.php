<?php
?>
<!DOCTYPE html>
<html>
 <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php wp_title(''); ?></title>
  <?php wp_head(); ?>
 </head>
 <body <?php body_class(""); ?>>
  <?php echo do_shortcode('[header]'); ?>
  <div class="content">
   <div class="content_column gridContainer">
     <h2 class="error_404">404</h2>

    <p>Yikes! The requested page could not be found. We'll look into this right away. Click on another page and keep exploring.</p>
    <div class="row_94">
     <div class="column_73">
      <?php echo do_shortcode('[search title="Search" id="wp_widget1" placeholder="Search" label="" useSubmit="0" btntext="Search"]');?>
     </div>
    </div>
   </div>
  </div>
  <?php echo do_shortcode('[footer]'); ?>
  <?php wp_footer(); ?>
 </body>

</html>