<?php

use League\OAuth2\Client\Helpers\MementoAuthHelper;
use League\OAuth2\Client\Provider\MementoProvider;

class MementoAuth
{
    /**
     * @var MementoAuthHelper
     */
    private $authHelper;

    public function __construct(MementoAuthHelper $authHelper)
    {
        $this->authHelper = $authHelper;

        add_action('init', [$this, 'memento_login_action']);
        add_action('init', [$this, 'memento_login_callback']);
    }

    function memento_login_action()
    {
        if (!isset($_GET['memento_login_action'])) {
            return;
        }

        $_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['memento_login_action']);
        $this->authHelper->authorize();
        exit;
    }

    function memento_login_callback()
    {
        if (!isset($_GET['memento-callback'])) {
            return;
        }

        try {
            $this->authHelper->callback();
            $mementoUser = $this->authHelper->getUser();
            $userId = get_user_id_for_memento_guid($this->authHelper->getSubject());

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
    'environment' => 'local' // options are "prod", "beta", "local"
]);

$mementoAuthHelper = new MementoAuthHelper($mementoProvider);
new MementoAuth($mementoAuthHelper);
