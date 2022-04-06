<?php

use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Helpers\MementoSsoHelper;
use Memento\OAuth2\Client\Provider\MementoProvider;

class MementoHelpers {
    /** @var MementoHelpers singleton instance */
    protected static $instance;

    /**
     * @var MementoProvider
     */
    private $mementoProvider;
    /**
     * @var MementoSsoHelper
     */
    private $mementoSsoHelper;
    /**
     * @var MementoInvoiceHelper
     */
    private $mementoInvoiceHelper;

    protected function __construct()
    {
        $this->mementoProvider = new MementoProvider([
            'clientId' => get_option('memento_settings_app_key'), // 'test-wp-plugin'
            'clientSecret' => get_option('memento_settings_app_secret'), // 'secret',
            'redirectUri' => rtrim(get_site_url() . '?memento-callback'),
            'environment' => get_option('memento_settings_environment') // options are "prod", "beta", "local"
        ]);

        $this->mementoSsoHelper = new MementoSsoHelper($this->mementoProvider);
        $this->mementoInvoiceHelper = new MementoInvoiceHelper($this->mementoProvider);
    }

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return MementoSsoHelper
     */
    public function getSsoHelper(): MementoSsoHelper
    {
        return $this->mementoSsoHelper;
    }

    /**
     * @return MementoInvoiceHelper
     */
    public function getInvoiceHelper(): MementoInvoiceHelper
    {
        return $this->mementoInvoiceHelper;
    }
}