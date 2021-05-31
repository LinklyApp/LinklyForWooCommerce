<?php

class MementoAuth
{
    public function __construct()
    {
        add_action('init', [$this, 'memento_login_action']);
        add_action('init', [$this, 'memento_login_callback']);

        session_start_if_none();
    }

    function memento_login_action()
    {
        if (!isset($_GET['memento_login_action'])) {
            return;
        }

        if (isset($_GET['code'])) {
            return;
        }

        $provider = getMementoProvider();

        $authUrl = $provider->getAuthorizationUrl();

        $_SESSION['oauth2state'] = $provider->getState();
        $_SESSION['url_to_return_to'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';


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

            if ($userId == null) {
                $mementoUser = $provider->getResourceOwner($token);
                newCustomer($mementoUser);
            } else {
                $mementoUser = $provider->getResourceOwner($token);
                login_memento_user($userId);
            }

            wp_redirect($_SESSION['url_to_return_to']);
            unset($_SESSION['url_to_return_to']);
            exit;

        } catch (Exception $e) {
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
