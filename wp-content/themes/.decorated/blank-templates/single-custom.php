<!-- TemplateBegin id="s_x57007a1f4c54b398343778203" template="/blank-templates/single-custom.php"--><?php echo blank_generate_loop_tags("/blank-templates/single-custom.php") ?><!DOCTYPE html>
<html <?php language_attributes(); ?> >
 <head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php wp_title(''); ?></title>
  <!-- SectionBegin id='s_x57007a1f4c5b6390283527206' code='&lt;?php wp_head(); ?&gt;'--><?php wp_head(); ?><!-- SectionEnd id='s_x57007a1f4c5b6390283527206' -->
 </head>
 <body <?php body_class(); ?> >
  <!-- SectionBegin id='s_x57007a1f4c5e9879366576208' shortcode='header' code='&lt;?php echo do_shortcode(&#039;[header]&#039;); ?&gt;'--><?php echo do_shortcode('[header]'); ?><!-- SectionEnd id='s_x57007a1f4c5e9879366576208' -->
  <div class="content">
   <div class="content_column gridContainer">
    <div class="content_column_row">
      <!-- SectionBegin id='s_x57007a1f4c615433780970209' shortcode='custom_post_content pagination=&quot;true&quot; link_prev=&quot;Newer&quot; link_next=&quot;Older&quot;' code='&lt;?php echo do_shortcode( &#039;[custom_post_content pagination=&quot;true&quot; link_prev=&quot;Newer&quot; link_next=&quot;Older&quot;]&#039;);?&gt;'--><?php echo do_shortcode( '[custom_post_content pagination="true" link_prev="Newer" link_next="Older"]');?><!-- SectionEnd id='s_x57007a1f4c615433780970209' -->
      <!-- SectionBegin id='s_x57007a1f4c652830052857210' shortcode='sidebar' code='&lt;?php echo do_shortcode(&#039;[sidebar]&#039;) ?&gt;'--><?php echo do_shortcode('[sidebar]') ?><!-- SectionEnd id='s_x57007a1f4c652830052857210' -->
    </div>
   </div>
  </div>
  <!-- SectionBegin id='s_x57007a1f4c697066956286211' shortcode='footer' code='&lt;?php echo do_shortcode(&#039;[footer]&#039;); ?&gt;'--><?php echo do_shortcode('[footer]'); ?><!-- SectionEnd id='s_x57007a1f4c697066956286211' -->
  <!-- SectionBegin id='s_x57007a1f4c6c6894143215212' code='&lt;?php wp_footer(); ?&gt;'--><?php wp_footer(); ?><!-- SectionEnd id='s_x57007a1f4c6c6894143215212' -->
 </body>

</html><!-- TemplateEnd id="s_x57007a1f4c54b398343778203" -->