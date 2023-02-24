<?php

use Linkly\OAuth2\Client\Helpers\LinklyInvoiceHelper;
use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\LinklyProvider;

class LinklyHelpers {
    /** @var LinklyHelpers singleton instance */
    protected static $instance;

    /**
     * @var LinklyProvider
     */
    private $linklyProvider;
    /**
     * @var LinklySsoHelper
     */
    private $linklySsoHelper;
    /**
     * @var LinklyInvoiceHelper
     */
    private $linklyInvoiceHelper;

    protected function __construct()
    {
        $this->linklyProvider = new LinklyProvider([
            'clientId' => get_option('linkly_settings_app_key'), // 'test-wp-plugin'
            'clientSecret' => get_option('linkly_settings_app_secret'), // 'secret',
            'redirectUri' => rtrim(get_site_url() . '?linkly-callback'),
            'environment' => get_option('linkly_settings_environment') // options are "prod", "beta", "local"
        ]);

        $this->linklySsoHelper = new LinklySsoHelper($this->linklyProvider);
        $this->linklyInvoiceHelper = new LinklyInvoiceHelper($this->linklyProvider);
    }

    public static function instance(): LinklyHelpers {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return LinklySsoHelper
     */
    public function getSsoHelper(): LinklySsoHelper
    {
        return $this->linklySsoHelper;
    }

    /**
     * @return LinklyInvoiceHelper
     */
    public function getInvoiceHelper(): LinklyInvoiceHelper
    {
        return $this->linklyInvoiceHelper;
    }

    public function getLinklyProvider(): LinklyProvider
    {
        return $this->linklyProvider;
    }

	public function isConnected() : bool {
		return !empty(get_option('linkly_settings_app_key')) && !empty(get_option('linkly_settings_app_secret'));
	}
}