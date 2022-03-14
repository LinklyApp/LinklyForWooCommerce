<?php

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;
use Memento\OAuth2\Client\Provider\MementoProvider;

class MementoAuth
{
    /**
     * @var MementoSsoHelper
     */
    private $ssoHelper;

    public function __construct(MementoSsoHelper $ssoHelper)
    {
        $this->ssoHelper = $ssoHelper;

        add_action('init', [$this, 'memento_login_action']);
        add_action('init', [$this, 'memento_login_callback']);
    }

    function memento_login_action()
    {
        if (!isset($_GET['memento_login_action'])) {
            return;
        }

        $_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['memento_login_action']);
        $this->ssoHelper->authorize();
        exit;
    }

    function memento_login_callback()
    {
        if (!isset($_GET['memento-callback'])) {
            return;
        }

        try {
            $this->ssoHelper->callback();
            $mementoUser = $this->ssoHelper->getUser();
            $userId = get_user_id_for_memento_guid($this->ssoHelper->getSubject());

            createOrUpdateMementoCustomer($mementoUser, $userId);

            wp_redirect($_SESSION['url_to_return_to']);
            unset($_SESSION['url_to_return_to']);
            exit;
        } catch (Exception $e) {
            wp_clear_auth_cookie();
            var_dump($e);
        }
    }
}

$mementoProvider = new MementoProvider([
    'clientId' => get_option('memento_settings_app_key'), // 'test-wp-plugin'
    'clientSecret' => get_option('memento_settings_app_secret'), // 'secret',
    'redirectUri' => rtrim(get_site_url() . '?memento-callback'),
    'environment' => get_option('memento_settings_environment') // options are "prod", "beta", "local"
]);

$MementoSsoHelper = new MementoSsoHelper($mementoProvider);
new MementoAuth($MementoSsoHelper);
