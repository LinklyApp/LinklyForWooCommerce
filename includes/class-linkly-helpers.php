<?php

use Linkly\OAuth2\Client\Helpers\LinklyButtonHelper;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\LinklyProvider;

class LinklyHelpers {
    /**
     * @var LinklyHelpers singleton instance
     */
    protected static $instance;

    /**
     * @var LinklyProvider
     */
    private LinklyProvider $linklyProvider;

    /**
     * @var LinklySsoHelper
     */
    private LinklySsoHelper $linklySsoHelper;

    /**
     * @var LinklyOrderHelper
     */
    private LinklyOrderHelper $linklyOrderHelper;

	/**
	 * @var LinklyButtonHelper
	 */
	private LinklyButtonHelper $linklyButtonHelper;

    protected function __construct()
    {
        $this->linklyProvider = new LinklyProvider([
            'clientId' => get_option('linkly_settings_app_key'), // 'test-wp-plugin'
            'clientSecret' => get_option('linkly_settings_app_secret'), // 'secret',
            'redirectUri' => rtrim(get_site_url() . '?linkly-callback'),
            'environment' => get_option('linkly_settings_environment') // options are "prod", "beta", "local"
        ]);

        $this->linklySsoHelper = new LinklySsoHelper($this->linklyProvider);
        $this->linklyOrderHelper = new LinklyOrderHelper($this->linklyProvider);
	    $this->linklyButtonHelper = new LinklyButtonHelper();
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
     * @return LinklyOrderHelper
     */
    public function getInvoiceHelper(): LinklyOrderHelper
    {
        return $this->linklyOrderHelper;
    }

	/**
	 * @return LinklyProvider
	 */
    public function getLinklyProvider(): LinklyProvider
    {
        return $this->linklyProvider;
    }

	/**
	 * @return LinklyButtonHelper
	 */
	public function getLinklyButtonHelper(): LinklyButtonHelper
	{
		return $this->linklyButtonHelper;
	}

	/**
	 * @return bool
	 */
	public function isConnected() : bool {
		return !empty(get_option('linkly_settings_app_key')) && !empty(get_option('linkly_settings_app_secret'));
	}
}