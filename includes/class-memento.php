<?php

defined('ABSPATH') or exit;


class Memento
{

    /** @var \Memento singleton instance */
    protected static $instance;

    public function __construct()
    {

        add_action('wp_enqueue_scripts', [$this, 'add_styles']);
        add_action('login_enqueue_scripts', [$this, 'add_styles']);
    }

    /**
     * Gets the plugin singleton instance.
     *
     * @return \Memento the plugin singleton instance
     * @since 1.10.0
     *
     * @see \facebook_for_woocommerce()
     *
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    function add_styles()
    {
        if (!wp_style_is('memento-style', 'registered')) {
            wp_register_style("memento-style", MEMENTO_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/style.css");
        }

        wp_enqueue_style("memento-style");
    }

}

function memento()
{
    return Memento::instance();
}


