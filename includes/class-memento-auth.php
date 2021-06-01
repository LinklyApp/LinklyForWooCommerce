<?php

class MementoAuth
{
    public function __construct()
    {
        add_action('init', [$this, 'memento_login_action']);
        add_action('init', [$this, 'memento_login_callback']);

        session_start_if_none();
    }

    function memento_renew_token()
    {
        try {
            $currentToken = $_SESSION['token'];
            if (!$currentToken->hasExpired()) {
                return;
            }

            $provider = getMementoProvider();
            $newAccessToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $currentToken->getRefreshToken()
            ]);
            $_SESSION['token'] = $newAccessToken;
        } catch (Exception $exception) {
            unset($_SESSION['token']);

            // Try to reauthorize the user
            wp_redirect(get_site_url() . '?memento_login_action=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    function memento_login_action()
    {
        if (!isset($_GET['memento_login_action'])) {
            return;
        }

        if (isset($_GET['code'])) {
            return;
        }


        $_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['memento_login_action']);
        $provider = getMementoProvider();

        $authUrl = $provider->getAuthorizationUrl();

        $_SESSION['oauth2state'] = $provider->getState();

        header('Location: ' . $authUrl);

        exit;
    }

    function memento_login_callback()
    {
        if (!isset($_GET['memento-callback'])) {
            return;
        }

        // CSRF protection
        $this->check_if_valid_state();

        $provider = getMementoProvider();

        // Try to get an access token (using the authorization code grant)
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);

        $_SESSION['token'] = $token;
        try {
            $tokenPayload = get_payload_from_token($token->getToken());
            $userId = get_user_id_for_memento_guid($tokenPayload->sub);

            $mementoUser = $provider->getResourceOwner($token);
            createOrUpdateMementoCustomer($mementoUser, $userId);


            wp_redirect($_SESSION['url_to_return_to']);
            unset($_SESSION['url_to_return_to']);
            exit;

        } catch (Exception $e) {
            wp_clear_auth_cookie();
            var_dump($e);
        }
    }

    private function check_if_valid_state()
    {
        if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            echo 'Session state: ' . $_SESSION['oauth2state'];
            echo '<br>';
            echo 'Redirected state: ' . $_GET['state'];
            echo '<br>';

            unset($_SESSION['oauth2state']);
            exit('State not equal');
        }
    }
}

new MementoAuth();
