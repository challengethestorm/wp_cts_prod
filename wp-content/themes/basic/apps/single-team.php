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
   <div class="ts-content_column gridContainer">
    <div class="content_column_row">
     <?php echo do_shortcode('[custom_post_content pagination="true" link_prev="Newer" link_next="Older"]'); ?>
    </div>
   </div>
  </div>
  <?php echo do_shortcode('[footer]'); ?>
  <?php wp_footer(); ?>
 </body>

</html>