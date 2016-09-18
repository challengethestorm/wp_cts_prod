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
     
      <div class="pa-row_16">
        <div class="pa-column_5 gridContainer">
          <div class="pa-row_14">
            <h1 class="pa-heading2">Our projects</h1>
            <hr class="pa-customClass1">
            <p class="pa-paragraph1"><?php echo do_shortcode('[tag_post_excerpt]');?></p>
          </div>
        </div>
      </div>
     
      <div class="pa-cat-col gridContainer"><?php echo do_shortcode( '[categories title=" " count="0" hierarchical="0" dropdown="0" id="wp_widget2" taxonomy="Portfolio_category"]');?></div>
      <div class="pa-content_column gridContainer">
        <div class="pa-content_column_row">
          <?php echo do_shortcode( '[custom_post_content pagination="true" link_prev="Newer" link_next="Older" desktop_cols="4" tablet_cols="3"]');?>
        </div>
      </div>
    </div>
    <?php echo do_shortcode('[footer]'); ?>
    <?php wp_footer(); ?>
  </body>
</html>