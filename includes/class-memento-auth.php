<?php

class MementoAuth
{
    public function __construct()
    {
        add_action('init', [$this, 'memento_login_action']);
        add_action('init', [$this, 'memento_login_callback']);
    }

    function memento_login_action()
    {
        if (isset($_GET['memento_login_action'])) {
            require MEMENTO_FOR_WOOCOMMERCE_ABS_PATH . '/vendor/autoload.php';

            $provider = getMementoProvider();

            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            if (!isset($_GET['code'])) {

                // If we don't have an authorization code then get one
                $authUrl = $provider->getAuthorizationUrl();
                $_SESSION['oauth2state'] = $provider->getState();
                $prev_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                $_SESSION['url_to_return_to'] = $prev_url;
                header('Location: ' . $authUrl);
                exit;
// Check given state against previously stored one to mitigate CSRF attack
            }
        }
    }

    function memento_login_callback()
    {
        if (isset($_GET['memento-callback'])) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            $provider = getMementoProvider();

            if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

                var_dump($_SESSION);
                echo 'Session state: ' . $_SESSION['oauth2state'];
                echo 'Redirected state: ' . $_GET['state'];

                echo 'test';

//            unset($_SESSION['oauth2state']);
                exit('Invalid state');
            }

            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code'],
            ]);

            $_SESSION['token'] = $token;
            try {
                $token = $_SESSION['token'];

                $tokenPayload = get_payload_from_token($token);
                $userId = get_user_id_for_memento_guid($tokenPayload->sub);

                if ($userId == null) {
                    $mementoUser = $provider->getResourceOwner($token);
                    newCustomer($mementoUser);
                } else {
                    $mementoUser = $provider->getResourceOwner($token);

                    login_memento_user($userId);
                }

//            echo '<pre>' . var_export($mementoUser, true) . '</pre>';
//            exit();

                wp_redirect($_SESSION['url_to_return_to']);
                exit;

            } catch (Exception $e) {

                var_dump($e);
                // Failed to get user details
                exit('Oh dear...');
            }
        }
    }
}

new MementoAuth();
