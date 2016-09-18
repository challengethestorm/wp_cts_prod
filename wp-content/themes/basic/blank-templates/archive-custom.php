<!DOCTYPE html>
<html <?php language_attributes(); ?> >
 <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php wp_title(''); ?></title>
  <?php wp_head(); ?>
 </head>
 <body <?php body_class(); ?> >
  <?php echo do_shortcode('[header]'); ?>
  <div class="content">
   <div class="content_column gridContainer">
    <div class="content_column_row">
     <?php echo do_shortcode( '[custom_post_content pagination="true" link_prev="Newer" link_next="Older"]');?>
     <div class="column_1">
      <?php echo do_shortcode('[categories title="Categories" count="0" hierarchical="0" dropdown="0" id="wp_widget0"]');?>
      <?php echo do_shortcode('[archives title="Archives" count="0" dropdown="0" id="wp_widget1"]');?>
      <?php echo do_shortcode('[recent_posts title="Recent Posts" number="10" id="wp_widget3"]');?>
     </div>
    </div>
   </div>
  </div>
  <?php echo do_shortcode('[footer]'); ?>
  <?php wp_footer(); ?>
 </body>

</html>