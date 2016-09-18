<?php

class CPAccordions extends CloudPressPluginBase {

	protected $defaultAtts = array(
		"ids" => "",
		'order' => '',
		'orderby' => '',
		'size' => 'full',
		'ease' => 'swing',
	);

	protected $defaultStyles = array();

	protected $defaultScripts = array(
		"cp-accordions" => array(
			"rel" => "cp-accordions/assets/cp-accordions.js",
			"deps" => array("jquery"),
		),
	);

	function __construct() {
		parent::__construct(get_called_class());
	}

	function init() {
		$this->register_shortcode("cp_accordions");
	}

	function do_shortcode_cp_accordions($atts) {
		if ($this->is_in_editor()) {
			return '<style></style>';
		}
		$result = "";

		if (!$this->is_in_editor()) {
			ob_start();
			?>
				<style type="text/css">
					#<?php echo $atts['id'] ?> [data-accordion-title]{
						-webkit-touch-callout: none;
						-webkit-user-select: none;
						-khtml-user-select: none;
						-moz-user-select: none;
						-ms-user-select: none;
						user-select: none;
					}
				</style>
			<?php
			$result = ob_get_contents();
			ob_end_clean();
		}

		return $result;
	}

}

new CPAccordions();
