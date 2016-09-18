      <div class="post-content-single">
        <h1 class="heading1"><?php echo do_shortcode('[tag_page_title]'); ?></h1>

       <div class="row_post">
        <div class="column_2"> <span class="span6">Written by </span>

         <?php echo do_shortcode( '[tag_author_name link="1"]'); ?><span class="span8">&nbsp;in category&nbsp;</span>

         <?php echo do_shortcode('[tag_post_categories]');?>
        </div>
        <div class="column_3"> <span class="span9"><?php echo do_shortcode('[tag_post_date]');?></span>

        </div>
       </div>
       <div class="element1">
        <?php echo do_shortcode('[tag_post_content]'); ?>
       </div>
      </div>
     