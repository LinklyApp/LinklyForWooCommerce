<?php
/*
Plugin Name: Memento for Woocommerce
Plugin URI: https://thullner.nl/
Description: Plugin to couple woocommerce to Memento SSO
Version: 1.0
Author: Mischa Thullner
Author URI: https://thullner.nl/
License: GPLv2 or later
Text Domain: memento
*/

defined('ABSPATH') or exit;

defined('MEMENTO_FOR_WOOCOMMERCE_ABS_PATH')
|| define('MEMENTO_FOR_WOOCOMMERCE_ABS_PATH', plugin_dir_path(__FILE__));

defined('MEMENTO_FOR_WOOCOMMERCE_PLUGIN_URL')
|| define('MEMENTO_FOR_WOOCOMMERCE_PLUGIN_URL', plugin_dir_url(__FILE__));


/**
 * The plugin loader class.
 *
 * @since 1.0.0
 */
class Memento_For_WC_Loader
{
    /** minimum PHP version required by this plugin */
    const MINIMUM_PHP_VERSION = '7.1.0';

    /** minimum WordPress version required by this plugin */
    const MINIMUM_WP_VERSION = '5.3';

    /** minimum WooCommerce version required by this plugin */
    const MINIMUM_WC_VERSION = '4.8.0';

    /** the plugin name, for displaying notices */
    const PLUGIN_NAME = 'Memento for WooCommerce';

    /** @var Memento_For_WC_Loader single instance of this class */
    private static $instance;

    /** @var array the admin notices to add */
    private $notices = [];


    /**
     * Constructs the class.
     *
     * @since 1.10.0
     */
    protected function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activation_check']);


        add_action('admin_init', [$this, 'check_environment']);
        add_action('admin_init', [$this, 'add_plugin_notices']);


        add_action('admin_notices', [$this, 'admin_notices'], 15);

        // if the environment check fails, initialize the plugin
        if ($this->is_environment_compatible()) {
            add_action('plugins_loaded', [$this, 'init_plugin']);
        }
    }


    /**
     * Cloning instances is forbidden due to singleton pattern.
     *
     * @since 1.10.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, sprintf('You cannot clone instances of %s.', get_class($this)), '1.10.0');
    }


    /**
     * Unserializing instances is forbidden due to singleton pattern.
     *
     * @since 1.10.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, sprintf('You cannot unserialize instances of %s.', get_class($this)), '1.10.0');
    }


    /**
     * Initializes the plugin.
     *
     * @since 1.10.0
     */
    public function init_plugin()
    {
        if (!$this->plugins_compatible()) {
            return;
        }

        require MEMENTO_FOR_WOOCOMMERCE_ABS_PATH . '/vendor/autoload.php';

        // Helpers
        include_once plugin_dir_path(__FILE__) . 'includes/helpers/functions-helper.php';

        include_once plugin_dir_path(__FILE__) . 'includes/class-memento.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-memento-auth.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-memento-customer.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-memento-customer-data-store.php';
        include_once plugin_dir_path(__FILE__) . 'includes/class-memento-order.php';
        include_once plugin_dir_path(__FILE__) . 'includes/functions-memento.php';

        // Admin
        include_once plugin_dir_path(__FILE__) . 'includes/admin/class-memento-admin.php';



        // Mappers
        include_once plugin_dir_path(__FILE__) . 'includes/mappers/class-memento-to-wc-customer-mapper.php';
        include_once plugin_dir_path(__FILE__) . 'includes/mappers/class-wc-order-to-memento-invoice-mapper.php';

        // Parts
        include_once plugin_dir_path(__FILE__) . 'includes/parts/class-memento-parts.php';

        // Settings
        include_once plugin_dir_path(__FILE__) . 'includes/mappers/class-wc-order-to-memento-invoice-mapper.php';

//         fire it up!
        if (function_exists('memento')) {
            memento();
        }
    }


    /**
     * Checks the server environment and other factors and deactivates plugins as necessary.
     *
     * Based on http://wptavern.com/how-to-prevent-wordpress-plugins-from-activating-on-sites-with-incompatible-hosting-environments
     *
     * @internal
     *
     * @since 1.10.0
     */
    public function activation_check()
    {

        if (!$this->is_environment_compatible()) {

            $this->deactivate_plugin();

            wp_die(self::PLUGIN_NAME . ' could not be activated. ' . $this->get_environment_message());
        }
    }


    /**
     * Checks the environment on loading WordPress, just in case the environment changes after activation.
     *
     * @internal
     *
     * @since 1.10.0
     */
    public function check_environment()
    {

        if (!$this->is_environment_compatible() && is_plugin_active(plugin_basename(__FILE__))) {

            $this->deactivate_plugin();

            $this->add_admin_notice('bad_environment', 'error', self::PLUGIN_NAME . ' has been deactivated. ' . $this->get_environment_message());
        }
    }


    /**
     * Adds notices for out-of-date WordPress and/or WooCommerce versions.
     *
     * @internal
     *
     * @since 1.10.0
     */
    public function add_plugin_notices()
    {

        if (!$this->is_wp_compatible()) {

            $this->add_admin_notice('update_wordpress', 'error', sprintf(
                '%s requires WordPress version %s or higher. Please %supdate WordPress &raquo;%s',
                '<strong>' . self::PLUGIN_NAME . '</strong>',
                self::MINIMUM_WP_VERSION,
                '<a href="' . esc_url(admin_url('update-core.php')) . '">', '</a>'
            ));
        }

        if (!$this->is_wc_compatible()) {

            $this->add_admin_notice('update_woocommerce', 'error', sprintf(
                '%1$s requires WooCommerce version %2$s or higher. Please %3$supdate WooCommerce%4$s to the latest version, or %5$sdownload the minimum required version &raquo;%6$s',
                '<strong>' . self::PLUGIN_NAME . '</strong>',
                self::MINIMUM_WC_VERSION,
                '<a href="' . esc_url(admin_url('update-core.php')) . '">', '</a>',
                '<a href="' . esc_url('https://downloads.wordpress.org/plugin/woocommerce.' . self::MINIMUM_WC_VERSION . '.zip') . '">', '</a>'
            ));
        }
    }


    /**
     * Determines if the required plugins are compatible.
     *
     * @return bool
     * @since 1.10.0
     *
     */
    private function plugins_compatible()
    {

        return $this->is_wp_compatible() && $this->is_wc_compatible();
    }


    /**
     * Determines if the WordPress compatible.
     *
     * @return bool
     * @since 1.10.0
     *
     */
    private function is_wp_compatible()
    {

        if (!self::MINIMUM_WP_VERSION) {
            return true;
        }

        return version_compare(get_bloginfo('version'), self::MINIMUM_WP_VERSION, '>=');
    }


    /**
     * Determines if the WooCommerce compatible.
     *
     * @return bool
     * @since 1.10.0
     *
     */
    private function is_wc_compatible()
    {

        if (!self::MINIMUM_WC_VERSION) {
            return true;
        }

        return defined('WC_VERSION') && version_compare(WC_VERSION, self::MINIMUM_WC_VERSION, '>=');
    }


    /**
     * Deactivates the plugin.
     *
     * @internal
     *
     * @since 1.10.0
     */
    protected function deactivate_plugin()
    {

        deactivate_plugins(plugin_basename(__FILE__));

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }


    /**
     * Adds an admin notice to be displayed.
     *
     * @param string $slug the slug for the notice
     * @param string $class the css class for the notice
     * @param string $message the notice message
     * @since 1.10.0
     *
     */
    private function add_admin_notice($slug, $class, $message)
    {

        $this->notices[$slug] = array(
            'class' => $class,
            'message' => $message
        );
    }


    /**
     * Displays any admin notices added with \Memento_For_WC_Loader::add_admin_notice()
     *
     * @internal
     *
     * @since 1.10.0
     */
    public function admin_notices()
    {

        foreach ((array)$this->notices as $notice_key => $notice) {

            ?>
            <div class="<?php echo esc_attr($notice['class']); ?>">
                <p><?php echo wp_kses($notice['message'], array('a' => array('href' => array()))); ?></p>
            </div>
            <?php
        }
    }


    /**
     * Determines if the server environment is compatible with this plugin.
     *
     * Override this method to add checks for more than just the PHP version.
     *
     * @return bool
     * @since 1.10.0
     *
     */
    private function is_environment_compatible()
    {

        return version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '>=');
    }


    /**
     * Gets the message for display when the environment is incompatible with this plugin.
     *
     * @return string
     * @since 1.10.0
     *
     */
    private function get_environment_message()
    {

        return sprintf('The minimum PHP version required for this plugin is %1$s. You are running %2$s.', self::MINIMUM_PHP_VERSION, PHP_VERSION);
    }


    /**
     * Gets the main \Memento_For_WC_Loader instance.
     *
     * Ensures only one instance can be loaded.
     *
     * @return \Memento_For_WC_Loader
     * @since 1.10.0
     *
     */
    public static function instance()
    {

        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }


}

// fire it up!
Memento_For_WC_Loader::instance();

