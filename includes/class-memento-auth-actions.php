<?php

use Memento\OAuth2\Client\Helpers\MementoSsoHelper;

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
        add_action('init', [$this, 'memento_link_account_action']);
        add_action('init', [$this, 'memento_login_callback']);

        add_action('wp_logout', [$this, 'memento_logout']);
    }

    function memento_link_account_action()
    {
        if (!isset($_GET['memento_link_account_action'])) {
            return;
        }
        $_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['memento_login_action']);
        $_SESSION['memento_link_account'] = true;
        $this->ssoHelper->authorize();
        exit;
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
            if (!isset($_SESSION['memento_link_account'])) {
                $user = get_user_by('email', $this->ssoHelper->getEmail());
                createOrUpdateMementoCustomer($mementoUser, $user->id);
            } else {
                linkLinklyCustomer($mementoUser, wp_get_current_user());
            }


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

new MementoAuthActions(LinklyHelpers::instance()->getSsoHelper());
