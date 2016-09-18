<?php

class CPTabs extends CloudPressPluginBase
{

    protected $defaultAtts = array(
        "ids"     => "",
        'order'   => '',
        'orderby' => '',
        'size'    => 'full',
        'ease'    => 'swing',
    );

    protected $defaultStyles = array(
        "owl_carousel_css"            => "/owl-carousel/owl.carousel.css",
        "owl_carousel_theme_css"      => "/owl-carousel/owl.theme.css",
        "owl_carousel_transition_css" => "/owl-carousel/owl.transitions.css",
        "cp_cp_tabs_css"              => '/cp-tabs/assets/cp-tabs.css',
    );

    protected $defaultScripts = array(
        "jquery-visible"      => array(
            "rel"  => "/owl-carousel/jquery.visible.js",
            "deps" => array('jquery'),
        ),

        "owl_carousel_min_js" => array(
            "rel"  => "/owl-carousel/owl.carousel.js",
            "deps" => array('jquery', 'jquery-visible'),
        ),
        "cp-tabs"             => array(
            "rel"  => "cp-tabs/assets/cp-tabs.js",
            "deps" => array("owl_carousel_min_js"),
        ),
    );

    public function __construct()
    {
        parent::__construct(get_called_class());
    }

    public function init()
    {
        $this->register_shortcode("cp_tabs");
    }

    public function do_shortcode_cp_tabs($atts)
    {
        return '<div>' . $this->get_styles_preview() . "</div>";
    }

}

new CPTabs();
