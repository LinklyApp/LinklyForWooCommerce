<?php

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;
use Memento\OAuth2\Client\Provider\MementoProvider;

class MementoAuthActions
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

        add_action('wp_logout', [$this, 'memento_logout']);
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

            $user = get_user_by( 'email', $this->ssoHelper->getEmail() );

            createOrUpdateMementoCustomer($mementoUser, $user->id);

            wp_redirect($_SESSION['url_to_return_to']);
            unset($_SESSION['url_to_return_to']);
            exit;
        } catch (Exception $e) {
            wp_clear_auth_cookie();
            var_dump($e);
        }
    }

    function memento_logout()
    {
        $this->ssoHelper->logout();

    }
}

new MementoAuthActions(MementoHelpers::instance()->getSsoHelper());
