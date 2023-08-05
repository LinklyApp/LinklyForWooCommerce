<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LinklyStyleActions
{
    /** @var \LinklyStyleActions singleton instance */
    protected static $instance;

    protected function __construct()
    {

        add_action('wp_enqueue_scripts', [$this, 'add_styles']);
        add_action('login_enqueue_scripts', [$this, 'add_styles']);
    }

    /**
     * Gets the plugin singleton instance.
     *
     * @return \LinklyStyleActions the plugin singleton instance
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
        if (!wp_style_is('linkly-style', 'registered')) {
            wp_register_style("linkly-style", LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/style.css");
        }

        wp_enqueue_style("linkly-style");
    }

}

function linkly()
{
    return LinklyStyleActions::instance();
}


