<?php
/**
 * Donation Form
 * 
 * @author  Varun Sridharan
 * @package WooCommerce Quick Donation/Templates
 * @version 0.1
 */
?>
<div class="wcqd-single-quick-container">
<form class="wcqd-single-quick-form" name="single_donation_form" method="post">
<!--/*
	<?php if(!empty($title)){?>
		<h3><?php 
		//echo $title; ?></h3>
	<?php } ?>

<h3>
	<?php echo "Donate to Voices for Change"; ?>
</h3>
*/-->

	<?php if(!empty($content)){?>
		<p><?php echo $content; ?></p>
	<?php }?>
	
	<input type="submit" name="donation_add" value="Donate to Voices for Change"/>
	<span>Amount ($)</span> <?php echo $donation_price; ?></br>
	
     
</form>
</div>