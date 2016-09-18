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
      <div class="ca-row_18">
        <div class="ca-column_13 gridContainer">
          <div class="ca-row_17">
            <h1 class="ca-heading3">Some of our Partners</h1>
            <div class="ca-align-inline-wrap3">
           <hr class="ca-customClass1">
          </div>
            <p class="ca-paragraph1">We greatly value our business partners, who share in a common vision of eliminating the stigma around mental illness. Through education, inspiration, and community involvement, we can all do our part to create a brighter tomorrow.</p>
          </div>
        </div>
      </div>
      <div class="content_column gridContainer">
        <div class="ca-row_15">
        
          <?php echo do_shortcode( '[custom_post_content pagination="true" link_prev="Newer" link_next="Older" desktop_cols="4" tablet_cols="3"]');?>
        </div>
      </div>
    </div>
    <?php echo do_shortcode('[footer]'); ?>
    <?php wp_footer(); ?>
  </body>
</html>