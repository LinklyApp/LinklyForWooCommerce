<?php

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use function Linkly\OAuth2\Client\Helpers\dd;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class LinklyAdminActions {
	private LinklySsoHelper $ssoHelper;

	public function __construct( LinklySsoHelper $ssoHelper ) {
		$this->ssoHelper = $ssoHelper;

		add_action( 'admin_menu', [ $this, 'register_menu' ], 9999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'linkly_admin_style' ] );
		add_action( 'admin_init', [ $this, 'linkly_admin_handle_save' ] );
		add_action( 'admin_init', [ $this, 'linkly_request_token_action' ] );
		add_action( 'admin_init', [ $this, 'linkly_request_token_callback' ] );
		add_action('linkly_notice_hook', [$this, 'display_client_credentials_saved_notice']);
		add_action('linkly_notice_hook', [$this, 'display_client_credentials_save_error_notice']);
	}

	public function display_client_credentials_saved_notice() {
		if ( ! isset( $_REQUEST['page'] )
		     || $_REQUEST['page'] !== 'linkly-for-woocommerce'
		     || ! get_transient( 'linkly_client_credentials_saved' )
		) {
			return;
		}


		echo '<div class="updated notice is-dismissible" ><p>';
		echo 'Client ID and secret saved successfully!';
		echo '</p></div>';

		// Delete the transient so that the notice doesn't keep showing up on refresh
		delete_transient( 'linkly_client_credentials_saved' );
	}

	public function display_client_credentials_save_error_notice() {
		if ( ! isset( $_REQUEST['page'] )
		     || $_REQUEST['page'] !== 'linkly-for-woocommerce'
		     || ! get_transient( 'display_client_credentials_save_error' )
		) {
			return;
		}

		echo '<div class="error notice is-dismissible" ><p>';
		esc_html_e( "client.connection-error", 'linkly-for-woocommerce' );
		echo ': ' . esc_html(get_transient( 'display_client_credentials_save_error' ));
		echo '</p></div>';

		// Delete the transient so that the notice doesn't keep showing up on refresh
		delete_transient( 'display_client_credentials_save_error' );
	}

	public function linkly_admin_handle_save() {
		if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'linkly-for-woocommerce' || empty( $_POST ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			throw new Exception( 'User is not an admin' );
		}

		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'linkly_credentials' ) ) {
			$this->handle_save_client_credentials();
		} else if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'linkly_button_style' ) ) {
			$this->handle_save_button_style();
		} else {
			throw new Exception( 'Invalid CSRF token' );
		}
	}

	private function handle_save_client_credentials() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'linkly_credentials' ) ) {
			throw new Exception( 'Invalid CSRF token' );
		}
		if ( ! isset( $_POST['linkly_client_id'] ) ) {
			throw new Exception( 'Client ID not set' );
		}
		if ( ! isset( $_POST['linkly_client_secret'] ) ) {
			throw new Exception( 'Client secret not set' );
		}
		$clientId     = sanitize_text_field( $_POST['linkly_client_id'] );
		$clientSecret = sanitize_text_field( $_POST['linkly_client_secret'] );
		update_option( 'linkly_settings_app_key', $clientId );
		update_option( 'linkly_settings_app_secret', $clientSecret );

		try {
			$this->ssoHelper->verifyClientCredentials();
			update_option( 'linkly_settings_app_connected', true );
			set_transient( 'linkly_client_credentials_saved', true, 5 );
		} catch ( IdentityProviderException $e ) {
			update_option( 'linkly_settings_app_connected', false );
			set_transient( 'display_client_credentials_save_error', sanitize_text_field($e->getResponseBody()['error']), 5 );
		} finally {
			$redirect_url = remove_query_arg( 'client_id', $_SERVER['HTTP_REFERER'] );
			wp_redirect( $redirect_url );
			exit;
		}
	}

	private function handle_save_button_style() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'linkly_button_style' ) ) {
			throw new Exception( 'Invalid CSRF token' );
		}
		if ( ! isset( $_POST['linkly_button_style'] ) ) {
			throw new Exception( 'Button style not set' );
		}
		$buttonStyle = sanitize_text_field( $_POST['linkly_button_style'] );
		if ( ! in_array( $buttonStyle, [ 'primary', 'secondary' ] ) ) {
			throw new Exception( 'Invalid button style' );
		}
		update_option( 'linkly_button_style', $buttonStyle );
	}

	/**
	 * The action to redirect to the Linkly SSO server to link the webshop to Linkly
	 *
	 * @return void
	 */
	function linkly_request_token_action() {
		if ( ! isset( $_GET['linkly_request_token'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			throw new Exception( 'User is not an admin' );
		}

		$decodedReturnUrl             = urldecode( $_GET['linkly_request_token'] );
		$sanitizedReturnUrl           = filter_var( $decodedReturnUrl, FILTER_SANITIZE_URL );
		$_SESSION['url_to_return_to'] = esc_url( get_site_url() . $sanitizedReturnUrl );

		// $corsUrl is pure the domain name without the path if there is a port number it is included
		$corsUrl = parse_url( get_site_url(), PHP_URL_SCHEME ) . '://' . parse_url( get_site_url(), PHP_URL_HOST );
		$port    = parse_url( get_site_url(), PHP_URL_PORT );

		if ( $port ) {
			$corsUrl .= ':' . $port;
		}

		$params = [
			'returnUrl'             => get_admin_url() . '?linkly_request_token_callback',
			'clientName'            => get_bloginfo( 'name' ),
			'allowedCorsOrigin'     => $corsUrl,
			'postLogoutRedirectUri' => get_site_url(),
			'redirectUri'           => get_site_url() . '?linkly_callback',
		];

		$this->ssoHelper->linkClientRedirect( $params );
		exit;
	}

	/**
	 * The callback action after the webshop has been linked to Linkly
	 *
	 * @return void
	 */
	function linkly_request_token_callback() {
		if ( ! isset( $_GET['linkly_request_token_callback'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			throw new Exception( 'User is not an admin' );
		}

		$client_options = $this->ssoHelper->linkClientCallback();

		update_option( 'linkly_settings_app_key', $client_options['client_id'] );
		update_option( 'linkly_settings_app_secret', $client_options['client_secret'] );

		wp_redirect( admin_url( 'admin.php?page=linkly-for-woocommerce' ) );
		exit;
	}

	public function register_menu() {
		$parent_slug = 'woocommerce';

		add_submenu_page(
			$parent_slug,
			'Linkly for WooCommerce',
			'Linkly WooCommerce',
			'manage_options',
			'linkly-for-woocommerce',
			[ $this, 'admin_page' ]
		);
	}

	function linkly_admin_style() {
		if ( ! wp_style_is( 'linkly-admin-style', 'registered' ) ) {
			wp_register_style( "linkly-admin-style", LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/admin-style.css" );
		}
		if ( ! wp_style_is( 'linkly-style', 'registered' ) ) {
			wp_register_style( "linkly-style", LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/style.css" );
		}

		wp_enqueue_style( "linkly-admin-style" );
		wp_enqueue_style( "linkly-style" );
	}

	public function admin_page() {
		include_once __DIR__ . '/views/html-admin-page.php';
	}
}

new LinklyAdminActions( LinklyHelpers::instance()->getSsoHelper() );
