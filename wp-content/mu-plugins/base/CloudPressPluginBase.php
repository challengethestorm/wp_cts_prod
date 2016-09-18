<?php
class CloudPressPluginBase
{

    // static vars used also with import / export
    public $instanceKey       = "";
    public $instanceNameStart = "";
    public $actionParameter   = '';
    public $instances         = array();

    private static $pluginsInstances = array();

    // protected vars
    protected $defaultAtts = array();

    protected $defaultStyles  = array();
    protected $defaultScripts = array();

    public function __construct($instanceKey = null, $instanceNameStart = null, $actionParameter = null)
    {

        if (!$instanceKey) {
            $instanceKey = get_class($this);
        }

        $this->instanceKey       = strtoupper($instanceKey);
        $this->actionParameter   = $actionParameter ? $actionParameter : strtolower($instanceKey);
        $this->instanceNameStart = $instanceNameStart ? $instanceNameStart : strtolower($instanceKey);
        $this->add_custom_actions('new', 'new_instance');

        if (method_exists($this, 'init')) {
            $this->init();
        }
        $this->do_actions();
        self::$pluginsInstances[get_class($this)] = $this;

    }
    

    public function register_shortcode($shortcode, $handler = null)
    {
        $pluginClass = get_called_class();
        CPPluginBase::add($shortcode, $this);

        $handler = $handler ? $handler : 'do_shortcode_' . $shortcode;
        $defaultAtts = $this->defaultAtts;
        $self = $this;
        add_shortcode($shortcode, function ($atts) use ($shortcode, $handler, $defaultAtts, $self) {

            foreach ($defaultAtts as $key => $value) {
                if (!isset($atts[$key])) {
                    $atts[$key] = $value;
                }
            }

            if (!isset($atts['id'])) {
                $atts['id'] = strtolower($shortcode) . "_" . time();
            }
            $self->load_assets();
            return $self->$handler($atts);
        });
    }

    public function get_styles_preview()
    {
        if ($this->is_in_editor()) {
            if (count($this->defaultStyles)) {
                return xtd_create_preview_styles(array_keys($this->defaultStyles));
            }
        }

        return "";
    }

    public function load_assets()
    {
        foreach ($this->defaultStyles as $key => $value) {
            $this->use_style($key, $value);
        }

        foreach ($this->defaultScripts as $key => $value) {
            $this->use_script($key, $value['rel'], @$value['deps']);
        }
    }

    public function use_style($name, $rel)
    {
        $mu_plugins_dir = site_url() . "/wp-content/mu-plugins";
        $rel            = $mu_plugins_dir . "/" . $rel;
        wp_register_style($name, $rel, $deps, get_bloginfo('version'), false);
        xtd_add_styles(array($name));
    }

    public function use_script($name, $rel, $deps = array())
    {
        $mu_plugins_dir = site_url() . "/wp-content/mu-plugins";
        $rel            = $mu_plugins_dir . "/" . $rel;
        wp_register_script($name, $rel, $deps, get_bloginfo('version'), true);
        xtd_add_scripts(array($name));
    }

    public function get_plugin_relative_url($path = "")
    {
        $reflector     = new ReflectionClass(get_class($this));
        $fn            = $reflector->getFileName();
        $fn            = dirname($fn);
        $r_abs         = realpath(ABSPATH);
        $r_fn          = realpath($fn);
        $relative_path = explode($r_abs, $r_fn);
        $relative_path = array_pop($relative_path);
        $result        = str_replace(DIRECTORY_SEPARATOR, "/", $relative_path) . "/" . $path;
        $result = preg_replace("/\/\/?/", "/", $result);
        return site_url() . $result ;
    }

    private function do_actions()
    {
        $self = $this;
        add_action('init', function () use ($self) {
            if (isset($_GET[$self->actionParameter]) || isset($_POST[$self->actionParameter])) {
                $action = $_REQUEST[$self->actionParameter];
                do_action($self->actionParameter . '_' . $action);
            }
        });
    }

    final protected function is_in_editor()
    {

        return (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'themebuilder') !== false && !isset($_REQUEST['previd'])) ||
            ((function_exists('xtd_in_editor') && xtd_in_editor()));
    }

    public function add_custom_actions($action, $callback)
    {
        add_action($this->actionParameter . '_' . $action, array($this, $callback));
    }

    public function new_instance()
    {
        $klass        = get_called_class();
        $pluginClass  = $klass::get_instance();
        $instanceName = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
        $instanceName = $pluginClass::look_for_instance($instanceName, $this->defaultAtts);

        // prepare_new_instance

        if (method_exists($this, 'prepare_new_instance')) {
            $this->prepare_new_instance($instanceName);
        }

        die($instanceName);
        return;
    }

    public function ob_wrap_execute($function, $params = null)
    {
        $result = "";
        try {
            ob_start();
            if ($params) {
                call_user_func_array(array($this, $function), $params);
            } else {
                $this->$function();
            }
            $result = ob_get_contents();
            ob_end_clean();
        } catch (Exception $e) {

        }

        return $result;

    }

    // import / export / instances plugin functions [ used in editor to manage instances ]

    public static function import($plugin_data)
    {
        $klass        = get_called_class();
        $pluginClass  = $klass::get_instance();
        $instanceData = array();

        $instanceData = array();
        if (isset($plugin_data["instanceData"])) {
            $instanceData = $plugin_data["instanceData"] ? $plugin_data["instanceData"] : array();
        }

        $instanceName = $pluginClass::look_for_instance("", $instanceData);

        $data                                                    = array();
        $data['replace']                                         = array();
        $data['variables'][$plugin_data['source']['instanceID']] = $instanceName;
        $data['variables']['%instance%']                         = $instanceName;

        if (method_exists($this, 'do_custom_import')) {
            $data = $this->do_custom_import($plugin_data, $data);
        }
        return $data;

    }

    public static function export($plugin_data)
    {
        $klass        = get_called_class();
        $pluginClass  = $klass::get_instance();
        $instanceName = $plugin_data['instanceID'];
        $data         = array();
        $instances    = get_instances_as_array($pluginClass->instanceKey);
        $data         = $instances[$instanceName];

        $styles = array();
        $files  = array();
        $assets = array();

        $result = array(
            "data"   => array(
                "assets"       => $assets,
                "instanceData" => $data,
            ),
            "files"  => $files,
            "styles" => $styles,
        );

        if (method_exists($this, 'do_custom_export')) {
            $result = $this->do_custom_export($plugin_data, $result);
        }

        return $result;
    }

    public static function look_for_instance($instanceName, $instanceData = array())
    {
        $klass       = get_called_class();
        $pluginClass = $klass::get_instance();
        $pluginClass::get_instances_as_array($pluginClass->instanceKey);

        if (!$instanceName) {

            $instanceName = $pluginClass->instanceNameStart . get_plugin_next_instance($pluginClass->instances, $pluginClass->instanceNameStart);
        }

        // try to get current instance settings or set defaults
        if (!isset($pluginClass->instances[$instanceName])) {
            $pluginClass::put_instance_in_file($pluginClass->instanceKey, $instanceName, $instanceData);
        }

        return $instanceName;
    }

    public static function get_instances_as_array($pluginName = "")
    {
        $klass                  = get_called_class();
        $pluginClass            = $klass::get_instance();
        $pluginClass->instances = get_instances_as_array($pluginName);
    }

    public static function put_instance_in_file($pluginName, $instanceName, $instanceData)
    {
        $klass       = get_called_class();
        $pluginClass = $klass::get_instance();
        if (!empty($instanceData)) {
            $pluginClass->instances[$instanceName] = $instanceData;
        } else {
            $pluginClass->instances[$instanceName] = array();
        }
        put_instance_in_file($pluginName, $instanceName, $pluginClass->instances[$instanceName]);
    }

    public static function get_instance()
    {
        $pluginClass = get_called_class();
        return self::$pluginsInstances[$pluginClass];
    }

}
