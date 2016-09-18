<?php
/** @var stdClass $formData */
?>
<form class="form-horizontal" action="" method="POST" id="payment-form">
    <fieldset>
        <div id="legend">
                <span class="fullstripe-form-title">
                    <?php MM_WPFSF::echo_translated_label( $formData->formTitle ); ?>
                </span>
        </div>
        <input type="hidden" name="action" value="wp_full_stripe_payment_charge"/>
        <input type="hidden" name="amount" value="<?php echo $formData->amount; ?>"/>
        <input type="hidden" name="formName" value="<?php echo $formData->name; ?>"/>
        <input type="hidden" name="isCustom" value="<?php echo $formData->customAmount; ?>"/>
        <input type="hidden" name="formDoRedirect" value="<?php echo $formData->redirectOnSuccess; ?>"/>
        <input type="hidden" name="formRedirectPostID" value="<?php echo $formData->redirectPostID; ?>"/>
        <input type="hidden" name="formRedirectUrl" value="<?php echo $formData->redirectUrl; ?>"/>
        <input type="hidden" name="formRedirectToPageOrPost" value="<?php echo $formData->redirectToPageOrPost; ?>"/>
        <input type="hidden" name="showAddress" value="<?php echo $formData->showAddress; ?>"/>
        <input type="hidden" name="sendEmailReceipt" value="<?php echo $formData->sendEmailReceipt; ?>"/>
        <p class="payment-errors"></p>
        <!-- Name -->
        <div class="control-group">
            <label class="control-label fullstripe-form-label"><?php _e( 'Card Holder\'s Name', 'wp-full-stripe-free' ); ?></label>
            <div class="controls">
                <input type="text" placeholder="Name" class="input-xlarge fullstripe-form-input" name="fullstripe_name" id="fullstripe_name">
            </div>
        </div>
        <?php if ( $formData->showEmailInput == 1 ): ?>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php _e( 'Email Address', 'wp-full-stripe-free' ); ?></label>
                <div class="controls">
                    <input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_email" id="fullstripe_email">
                </div>
            </div>
        <?php endif; ?>
        <?php if ( $formData->showCustomInput == 1 ): ?>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php MM_WPFSF::echo_translated_label( $formData->customInputTitle ); ?></label>
                <div class="controls">
                    <input type="text" class="input-xlarge fullstripe-form-input" name="fullstripe_custom_input" id="fullstripe_custom_input">
                </div>
            </div>
        <?php endif; ?>
        <?php if ( $formData->customAmount == 1 ): ?>
        <div class="control-group">
            <label class="control-label fullstripe-form-label"><?php _e( 'Payment Amount', 'wp-full-stripe-free' ); ?></label>
            <div class="controls">
                <input type="text" placeholder="10.00" style="width: 60px;" name="fullstripe_custom_amount" id="fullstripe_custom_amount" class="fullstripe-form-input"><br/>
            </div>
        </div>
        <?php endif; ?>
        <?php if ( $formData->showAddress == 1 ): ?>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Street', 'wp-full-stripe-free' ); ?></label>
                <div class="controls">
                    <input type="text"  name="fullstripe_address_line1" id="fullstripe_address_line1" class="fullstripe-form-input"><br/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php _e( 'Billing Address Line 2', 'wp-full-stripe-free' ); ?></label>
                <div class="controls">
                    <input type="text"  name="fullstripe_address_line2" id="fullstripe_address_line2" class="fullstripe-form-input"><br/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php _e( 'City', 'wp-full-stripe-free' ); ?></label>
                <div class="controls">
                    <input type="text"  name="fullstripe_address_city" id="fullstripe_address_city" class="fullstripe-form-input"><br/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php _e('State', 'wp-full-stripe-free'); ?></label>
                <div class="controls">
                    <input type="text" style="width: 60px;"  name="fullstripe_address_state" id="fullstripe_address_state" class="fullstripe-form-input"><br/>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label fullstripe-form-label"><?php _e('Zip', 'wp-full-stripe-free'); ?></label>
                <div class="controls">
                    <input type="text" style="width: 60px;"  name="fullstripe_address_zip" id="fullstripe_address_zip" class="fullstripe-form-input"><br/>
                </div>
            </div>
        <?php endif; ?>
        <!-- Card Number -->
        <div class="control-group">
            <label class="control-label fullstripe-form-label"><?php _e( 'Card Number', 'wp-full-stripe-free' ); ?></label>
            <div class="controls">
                <input type="text" autocomplete="off" placeholder="4242424242424242" class="input-xlarge fullstripe-form-input" size="20" data-stripe="number">
            </div>
        </div>
        <!-- Expiry-->
        <div class="control-group">
            <label class="control-label fullstripe-form-label"><?php _e( 'Card Expiry Date', 'wp-full-stripe-free' ); ?></label>
            <div class="controls">
                <input type="text" style="width: 60px;" size="2" placeholder="10" data-stripe="exp-month" class="fullstripe-form-input"/>
                <span> / </span>
                <input type="text" style="width: 60px;" size="4" placeholder="2016" data-stripe="exp-year" class="fullstripe-form-input"/>
            </div>
        </div>
        <!-- CVV -->
        <div class="control-group">
            <label class="control-label fullstripe-form-label"><?php _e( 'Card CVV', 'wp-full-stripe-free' ); ?></label>
            <div class="controls">
                <input type="password" autocomplete="off" placeholder="123" class="input-mini fullstripe-form-input" size="4" data-stripe="cvc"/>
            </div>
        </div>
        <!-- Submit -->
        <?php if ( $formData->customAmount == 0 ): ?>
            <div class="control-group">
                <div class="controls">
                    <button type="submit"><?php MM_WPFSF::echo_translated_label( $formData->buttonTitle ); ?><?php if ( $formData->showButtonAmount == 1 ) {
                            printf( ' %s%0.2f', $currencySymbol, $formData->amount / 100.0 );
                        } ?></button>
                    <img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe-free' ); ?>" id="showLoading"/>
                </div>
            </div>
        <?php else: ?>
            <div class="control-group">
                <div class="controls">
                    <button type="submit"><?php MM_WPFSF::echo_translated_label( $formData->buttonTitle ); ?></button>
                    <img src="<?php echo plugins_url( '/img/loader.gif', dirname( __FILE__ ) ); ?>" alt="<?php _e( 'Loading...', 'wp-full-stripe-free' ); ?>" id="showLoading"/>
                </div>
            </div>
        <?php endif; ?>
    </fieldset>
</form>
