<!DOCTYPE html>
<html <?php language_attributes(); ?> >
 <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php wp_title(''); ?></title>
  <?php wp_head(); ?>
 </head>
 <body <?php body_class(""); ?>>
  <?php echo do_shortcode('[header]'); ?>
  <div class="content">
   <div class="ta-row_111">
    <div class="ta-column_9 gridContainer">
     <div class="ta-row_14">
       <h1 class="ta-heading2">Our team of superheroes</h1>

      <div class="ta-align-inline-wrap18">
       <hr class="customClass1">
      </div>
      <p class="ta-paragraph1">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
     </div>
    </div>
   </div>
   <div class="content_column gridContainer">
    <div class="ta-content_column_row">
     <?php echo do_shortcode( '[custom_post_content pagination="true" link_prev="Newer" link_next="Older" desktop_cols="4" tablet_cols="3"]');?>
    </div>
   </div>
  </div>
  <?php echo do_shortcode('[footer]'); ?>
  <?php wp_footer(); ?>
 </body>

</html>