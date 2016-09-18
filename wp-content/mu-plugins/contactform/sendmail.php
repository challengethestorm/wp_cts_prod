<?php

define('WP_USE_THEMES', true);
require_once realpath(dirname(__FILE__) . '/../../../wp-load.php');

function cp_contact_form_send_mail() {
	if (!function_exists('get_plugin_data_folder')) {
		require_once WPMU_PLUGIN_DIR . '/xtdpluginbase/xtdpluginbase.php';
	}

	$instancesFile = get_plugin_data_folder() . '/instances.php';
	if (!file_exists($instancesFile)) {
		echo '0';
		return;
	}

	$formdata = $_POST["formdata"];
	$message = '';
	$message .= file_get_contents(dirname(__FILE__) . '/html_mail.php');

	$to = "";

	$instancesContent = json_decode(require ($instancesFile), true);

	if (!isset($instancesContent['xtd_contact_form'])) {
		echo '0';
		return;
	} else {
		$instancesContent = $instancesContent['xtd_contact_form'];
	}

	if (isset($instancesContent[$formdata['id']])) {
		if (isset($instancesContent[$formdata['id']]['email'])) {
			$to = $instancesContent[$formdata['id']]['email'];
		} else {
			echo '0';
			return;
		}
	} else {
		echo '0';
		return;
	}

	$formdataString = "";
	$text_message = "";

	foreach ($formdata as $prop => $value) {
		if ($prop == 'message') {
			$message = str_replace("@message", nl2br($value), $message);
		}

		if ($prop != 'captcha' && $prop != 'message' && $prop != 'id') {
			$formdataString .= '<tr><td align="left" style="font-family: arial,sans-serif; font-size: 14px; font-weight:bold; line-height: 20px !important; color: #00C2FF; padding-bottom: 20px;border-top:1px solid #ccc;padding-top:20px;">' . ucfirst($prop) . ':&nbsp; <span style="color: #000000;">' . $value . '</td></tr>';
		}

		if (is_int($prop)) {
			$to = $value;
		}

		$text_message .= $prop . "  :  " . $value . "\n";
	}
	$message = str_replace("@contact_details", $formdataString, $message);

	$result = "";

	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

	$overridden = false;

	// $overridden  after filters should became "0" or "1"
	// as response for mail sending success

	$overridden = apply_filters(
		"override_sendmail_function",
		$overridden,
		array(
			"formdata" => $formdata,
			"to" => "$to",
			"subject" => get_bloginfo('name') . ': Contact Form Message',
			"html" => $message,
			"text" => $text_message,
			"headers" => $headers,
		)
	);
	if ($overridden === false) {
		if (($result = wp_mail($to, get_bloginfo('name') . ': Contact Form Message', $message, $headers))) {
			echo '1';
		} else {
			echo '0';
		}
	} else {
		echo $overridden;
	}
}
