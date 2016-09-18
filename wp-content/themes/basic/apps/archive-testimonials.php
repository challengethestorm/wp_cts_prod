<!DOCTYPE html>
<html <?php language_attributes(); ?>>
 <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php wp_title(''); ?></title>
  <?php wp_head(); ?>
 </head>
 <body <?php body_class(""); ?>>
  <?php echo do_shortcode('[header]'); ?>
  <div class="content">
   <div class="tsa-row_14">
    <div class="tsa-column_5 gridContainer">
      <h1 class="tsa-heading2">Customer stories</h1>

     <div class="align-inline-wrap7">
      <hr class="tsa-customClass2">
     </div>
     <p class="tsa-paragraph1">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
    </div>
   </div>
   <div class="content_column gridContainer">
    <?php echo do_shortcode('[custom_post_content pagination="true" link_prev="Newer" link_next="Older" desktop_cols="3" tablet_cols="2"]'); ?>
   </div>
  </div>
  <?php echo do_shortcode('[footer]'); ?>
  <?php wp_footer(); ?>
 </body>

</html>