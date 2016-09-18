<?php

class CPVideoBackground extends CLoudPressPluginBase
{

    protected $defaultAtts = array(
        "selector" => "body",
        "poster"   => "demo.png",
        "webm"     => "demo.webm",
        "mp4"      => "demo.mp4",
        'muted'    => 'true',
        'loop'     => 'true',
        'overlay'  => 'false',
        'fixed'    => 'false',
        'dynamic'  => 'false',
    );

    protected $defaultScripts = array(
        "vidbg" => array(
            "rel"  => "cp_video_background/assets/vidbg.js",
            "deps" => array("jquery"),
        ),
    );

    public function __construct()
    {
        $this->defaultAtts['poster'] = $this->get_plugin_relative_url("/demo/" . $this->defaultAtts['poster']);
        $this->defaultAtts['webm']   = $this->get_plugin_relative_url("/demo/" . $this->defaultAtts['webm']);
        $this->defaultAtts['mp4']    = $this->get_plugin_relative_url("/demo/" . $this->defaultAtts['mp4']);
        parent::__construct('cpvideobg');
    }

    public function init()
    {
        $this->register_shortcode("cp_vidbg");
    }

    public function do_shortcode_cp_vidbg($atts)
    {

        $atts['poster'] = preg_replace("/(.*)wp-content\//", site_url() . "/wp-content/", $atts['poster']);
        $atts['webm']   = preg_replace("/(.*)wp-content\//", site_url() . "/wp-content/", $atts['webm']);
        $atts['mp4']    = preg_replace("/(.*)wp-content\//", site_url() . "/wp-content/", $atts['mp4']);

        return $this->ob_wrap_execute('get_video_js_script', array($atts));
    }

    public function get_video_js_script($atts)
    {

        if ($atts['selector'] !== 'body') {
            if ($atts['dynamic'] !== 'true') {
                $atts['selector'] = 'div[data-cpvideobg="' . $atts["selector"] . '"]';
            }
        }

        ?>
         <?php if (!$this->is_in_editor()): ?>
           <script type="text/javascript">
                  jQuery(function($){
                    $('<?php echo $atts["selector"]; ?>').vidbg({

                        'mp4': '<?php echo $atts["mp4"]; ?>',
                        'webm': '<?php echo $atts["webm"]; ?>',
                        'poster': '<?php echo $atts["poster"]; ?>',
                    }, {
                      id:     '<?php echo $atts["id"]; ?>',
                      muted:   <?php echo $atts['muted']; ?>,
                      loop:    <?php echo $atts['loop']; ?>,
                      overlay: <?php echo $atts['overlay']; ?>,
                    });
                });
            </script>
          <?php endif;?>
          <style readonly="true" type="text/css">
              <?php if ($atts['fixed'] === "true" && !$this->is_in_editor()): ?>
                  video#<?php echo $atts["id"]; ?>_video{
                    position: fixed!important;
                  }
              <?php else: ?>
                  <?php echo $atts["selector"]; ?>{
                    background-image:url("<?php echo $atts["poster"]; ?>")!important;
                    background-repeat: no-repeat!important;
                    background-position: center!important;
                    background-size: cover!important;
                  }
                  
                  video#<?php echo $atts["id"]; ?>_video{
                      width: 100% !important;
                      height: auto !important;
                  }
              <?php endif;?>
          </style>
      <?php

    }
}

new CPVideoBackground();