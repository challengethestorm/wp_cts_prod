<?php
/*
Template Name: Search Page
*/
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> >
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php wp_title(''); ?>
  </title>
  <?php wp_head(); ?>
</head>
<body <?php body_class(""); ?> >
  <!--="" sectionbegin="" id="s_x55112d7837266466261322170" shortcode="header" code="<span>" --="" &gt;="" <="" span="">
  <div class="header">
    <div class="row_17">
      <div class="row_15 gridContainer">
        <div class="row_16">
          <div class="column_7">
            <a href="<?php echo do_shortcode('[tag_link_site_url]'); ?>/" target="_self" rel="" data-width="800" data-height="600">
              <img class="image2" src="<?php echo do_shortcode('[tag_link_site_url]'); ?>/wp-content/uploads/2016/03/Large-Final_white_test_TM.png" href="<?php echo do_shortcode('[tag_link_site_url]'); ?>/about/"/>
            </a>
          </div>
          <div class="column_8">
            <?php echo do_shortcode('[xtd_drop_down_menu id="menu1" inherit=""]');?>
          </div>
        </div>
      </div>
    </div>
  </div> -->
  <!--SectionEnd id='s_x55112d7837266466261322170'-->
  <?php echo do_shortcode('[header name="default-inner-page-template"]'); ?>
  <div class="content">
    <div class="content_row">
      <div class="content_column gridContainer">
        <h3>Search results:
        </h3>
        <?php echo do_shortcode('[search_results]');?>
      </div>
    </div>
  </div>
  <?php echo do_shortcode('[footer name="default-inner-page-template"]'); ?>
  <?php wp_footer(); ?>
</body>
</html>
