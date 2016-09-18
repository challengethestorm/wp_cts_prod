<?php
function setTax($tax) { //	PHP CUSTOM BUILT TO GET MY SPECIFIC TAXONOMY VALUES IN ORDER TO INSERT INTO THE PAGE

Global $post; //access the post to get the post_id of the current "single" page. This can be used to fetch appropriate child variables in the taxonomy (symptoms, etc.)
$term_list = wp_get_post_terms($post->ID, $tax, array("fields" => "all"));	//return all fields for the given taxonomy ("symptoms", in this case)
$tax_str = "";
$page_title = do_shortcode( '[cp_post_meta name="cp_title"]');

foreach ($term_list as $item):			//access whichever fields from the term list that we want.
	$item_name = $item->name;
	$item_desc = $item->description;
	$item_slug = $item->slug;
	$item_tax_name = $item->taxonomy;
	$item_tax_id = $item->term_taxonomy_id;
//	$item_link = "<a href=" .get_term_link($item). ">" .strtoupper($item_name). "</a>";		//sets the name to hyperlink to the appropriate page
//	$tax_str = $tax_str. "<li>" .$item_link. "<ul class='tax-list-desc'><li> " .$item_desc. "</li></ul></li>"; //build out list of the $taxonomy . Will be inserted into <ul> later on
//PLAYING WITH THIS TO SEE IF I CAN DO AS A SWEET TABLE INSTEAD
	$item_link = "<a href=" .get_term_link($item). ">" .strtoupper($item_name). "</a>";		//sets the name to hyperlink to the appropriate page
	$tax_str = $tax_str. "<tr><td>" .$item_link. "</td><td class='tax-list-desc'>" .$item_desc. "</td></tr>"; //build out list of the $taxonomy . Will be inserted into <ul> later on
endforeach; 

// set the introduction statement to present before each taxonomy group.
$tax_intro = "";

switch ($tax) {
    case "symptoms":
        $tax_intro = "Some common symptoms related to " .$page_title. " include:"; 
        break;
    case "resources":
        $tax_intro = "There are a number of helpful resources to access for " .$page_title. ". Here are some which Challenge the Storm recommends:"; 
        break;
    default:
        $tax_intro = "Here's what you need to know about " .$page_title. ".";
}

//echo <<<EOT
//	My name is "$tax_para".
//EOT;

if ( strlen($tax_str) > 0): ?>
	<h2>
		<?php echo strtoupper($tax); ?>
	</h2>
	<hr class="conds-customClass4">
	<span>
		<p class="conds-paragraph1">
			<?php echo $tax_intro; ?> 
		</p>
																	<!--
		<ul class="tax-list">										-->
			<?php //echo $tax_str; ?> 								<!--
		</ul>														-->
<!-- TESTING THIS OUT TO SEE IF WE CAN DO A SWEE TABLE -->
		<table class="container">
			<thead>
				<tr>
					<th><h1>
						<?php echo strtoupper($tax); ?>
					</h1></th>
					<th><h1>Description</h1></th>
				</tr>
			</thead>
			<tbody>
				<?php echo $tax_str; ?> 
			</tbody>
		</table>	
	</span>
<?php else : ?>
	<p>No <?php echo strtolower($tax); ?>found</p> 
<?php endif;
}
?>
        
<?php
function add_full_signup_form() {
	?>
<link href="//cdn-images.mailchimp.com/embedcode/classic-10_7.css" rel="stylesheet" type="text/css">
<link href="../wp-content/themes/basic/mailchimp-embedded-form.css" rel="stylesheet" type="text/css">
<form id="landing-page-subscriptions-1" class="half-mc-form" action="//challengethestorm.us11.list-manage.com/subscribe/post?u=5850ff3787d7ce24fb9dac43b&amp;id=98c8f813af" method="POST" data-attr-form-id="1" target="_blank" novalidate="">
  <div class="bottom-full">
    <label for="EMAIL" class="EMAIL-label yikes-mailchimp-field-required">
      <!-- dictate label visibility -->
      <input id="yikes-easy-mc-form-1-EMAIL" name="EMAIL" placeholder="YOUR EMAIL" class="mc-text form-control" required="required" type="email" value="">
    </label>
    <label for="FNAME" class="FNAME-label">
      <!-- dictate label visibility --> 
      <input id="yikes-easy-mc-form-1-FNAME" name="FNAME" placeholder="YOUR FIRST NAME" class="mc-text form-control" type="text" value="" style="cursor: auto;">
    </label>
    <label for="LNAME" class="LNAME-label">
      <!-- dictate label visibility -->
      <input id="yikes-easy-mc-form-1-LNAME" name="LNAME" placeholder="YOUR LAST NAME" class="mc-text form-control" type="text" value="">
    </label>
  </div>
  <div class="left-half">
    <input type="submit" value="Subscribe" name="subscribe" class="submit" />
<!--  ...................................... HIDDEN FIELDS WITH DEFAULT VALUES ........................................  --> 
    <!-- Honepot Trap -->
    <input type="hidden" name="yikes-mailchimp-honeypot" id="yikes-mailchimp-honeypot" value="">
    <!-- List ID -->
    <input type="hidden" name="yikes-mailchimp-associated-list-id" id="yikes-mailchimp-associated-list-id" value="98c8f813af">
    <input type="hidden" id="yikes_easy_mc_new_subscriber" name="yikes_easy_mc_new_subscriber" value="b0054225dc">
    <!-- Form that is being submitted! Used to display error/success messages above the correct form -->              
    <input type="hidden" name="yikes-mailchimp-submitted-form" id="yikes-mailchimp-submitted-form" value="1"> 
        <!-- Nonce Security Check -->
    <input type="hidden" name="_wp_http_referer" value="/landing-page">
<!--  ...................................... END FIELDS WITH DEFAULT VALUES ........................................  --> 
  </div>
  <div>
    <div class="right-half">
      <label for="17345" class="17345-label">
        <!-- dictate label visibility -->
        <span class="17345-label chkbox-parent-label">
          Interests                     
        </span>
        <label for="17345-0" class="yikes-easy-mc-checkbox-label yikes-easy-mc-checkbox-label mc-half-chkbox-label">
          <input type="checkbox" name="17345[]" id="17345-0" checked="checked" value="Blog Posts">
          Blog Posts                      
        </label>
        <label for="17345-1" class="yikes-easy-mc-checkbox-label yikes-easy-mc-checkbox-label last-selection mc-half-chkbox-label">
          <input type="checkbox" name="17345[]" id="17345-1" checked="checked" value="Special Updates &amp; Offers">
          Special Updates &amp; Offers                      
        </label>
      </label>
      <div id="mce-responses" class="clear">
      <div class="response" id="mce-error-response" style="display: none;">
      </div>
      <div class="response" id="mce-success-response" style="display: none;">
      </div>
      </div>
    </div>
<!--  ...................................... HIDDEN FIELDS WITH DEFAULT VALUES ........................................  --> 
    <div style="display:none;">
      <label for="GROUP" style="display:none;" class="GROUP-label">
        <!-- dictate label visibility -->
        <span class="GROUP-label checkbox-parent-label">
          Website Signups                     
        </span>
        <label for="GROUP-4" class="yikes-easy-mc-checkbox-label ">

          <input type="hidden" name="GROUP" value="Landing Page" id="mce-GROUP-9" checked>
          <span class="GROUP-label">Landing Page
          </span>
        </label>
      </label>
      <label for="13021" style="display:none;" class="13021-label">
        <label for="13021-1" class="yikes-easy-mc-checkbox-label field-no-label" style="display:none;">
          <input type="radio" name="13021[]" id="13021-1" checked="checked" value="Website Signups">
          Website Signups                     
        </label>
      </label>
    </div>
<!--  ...................................... END FIELDS WITH DEFAULT VALUES ........................................  --> 
  </div>
</form>
<script src="//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js" type="text/javascript">
</script>
<script type="text/javascript">
(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';fnames[3]='GROUP';ftypes[3]='radio';}(jQuery));var $mcj = jQuery.noConflict(true);
</script>
<?php
}
?>

<?php
function add_mailchimp_embed() {
	?>
<!-- Begin MailChimp Signup Form -->
<link href="//cdn-images.mailchimp.com/embedcode/classic-10_7.css" rel="stylesheet" type="text/css">
<style type="text/css">
  #mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; }
  /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
     We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
 #mc_embed_signup {
  position: fixed;  
    top: 50%;  
    left: 50%;  /* bring your own prefixes */  
    transform: translate(-50%, -90%); 
    border:1px solid #808080;
  width: 500px;    
}
</style>
<div id="mc_embed_signup">
<form action="//challengethestorm.us11.list-manage.com/subscribe/post?u=5850ff3787d7ce24fb9dac43b&id=98c8f813af" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
    <div id="mc_embed_signup_scroll">
  <h2>Subscribe to Challenge the Storm</h2>
<div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
<div class="mc-field-group">
  <label for="mce-EMAIL">Email Address  <span class="asterisk">*</span>
</label>
  <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
</div>
<div class="mc-field-group" style="display:none;">
  <label for="mce-FNAME">First Name </label>
  <input type="text" value="" name="FNAME" class="" id="mce-FNAME"> <!-- changed from type-"text" to type="hidden" -->
</div>
<div class="mc-field-group" style="display:none;">
  <label for="mce-LNAME">Last Name </label>
  <input type="text" value="" name="LNAME" class="" id="mce-LNAME"> <!-- changed from type-"text" to type="hidden" -->
</div>
<div class="mc-field-group input-group">
    <strong>Interests </strong>
    <ul style="list-style:none;"><li><input type="checkbox" value="256" name="group[17345][256]" id="mce-group[17345]-17345-0" checked><label for="mce-group[17345]-17345-0">Blog Posts</label></li> <!-- added style="display:none;" -->
<li><input type="checkbox" value="512" name="group[17345][512]" id="mce-group[17345]-17345-1" checked><label for="mce-group[17345]-17345-1">Special Updates & Offers</label></li>
</ul>
</div>
  <div id="mce-responses" class="clear">
    <div class="response" id="mce-error-response" style="display:none"></div>
    <div class="response" id="mce-success-response" style="display:none"></div>
  </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_5850ff3787d7ce24fb9dac43b_98c8f813af" tabindex="-1" value=""></div>
    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
    </div>
</form>
</div>
<script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';fnames[3]='GROUP';ftypes[3]='radio';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
  <!--End mc_embed_signup-->
<?php
}
?>	
 