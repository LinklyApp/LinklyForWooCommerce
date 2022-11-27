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
        add_action('init', [$this, 'linkly_request_token_action']);
        add_action('init', [$this, 'linkly_request_token_callback']);

        add_action('wp_logout', [$this, 'memento_logout']);
    }

    function linkly_request_token_action()
    {
        if (!isset($_GET['linkly_request_token'])) {
            return;
        }
        $_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['linkly_request_token']);
        // $corsUrl is pure the domain name without the path if there is a port number it is included

        $url = $this->getBaseUrl();
        $corsUrl = parse_url(get_site_url(), PHP_URL_SCHEME) . '://' . parse_url(get_site_url(), PHP_URL_HOST);
        $port = parse_url(get_site_url(), PHP_URL_PORT);

        if ($port) {
            $corsUrl .= ':' . $port;
        }

        $params = [
            'redirect_uri' => get_site_url() . '?linkly_request_token_callback',
            'clientName' => get_bloginfo('name'),
            'oauth_cors_uri' => $corsUrl,
            'oauth_post_logout_redirect_uri' => get_site_url(),
            'oauth_redirect_uri' => get_site_url() . '?memento-callback',
        ];
        $url .= '/request-token?' . http_build_query($params);
        wp_redirect($url);
        exit;
    }

    function linkly_request_token_callback(){
        if(!isset($_GET["linkly_request_token_callback"])){
            return;
        }
        update_option('memento_settings_app_key', sanitize_text_field($_GET["client_id"]));
        update_option('memento_settings_app_secret', sanitize_text_field($_GET["client_secret"]));


        wp_redirect(admin_url('options-general.php?page=linkly-for-woocommerce'));
        exit;

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

    private function getBaseUrl()
    {
        $env = get_option('memento_settings_environment');
        if ($env === 'local') {
            return LinklyHelpers::instance()->getMementoProvider()->localDomain;
        }
        if ($env === 'beta') {
            return LinklyHelpers::instance()->getMementoProvider()->betaDomain;
        }

        return LinklyHelpers::instance()->getMementoProvider()->domain;

    }
}

new MementoAuthActions(LinklyHelpers::instance()->getSsoHelper());
