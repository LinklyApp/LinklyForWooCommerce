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
		add_action( 'admin_init', [ $this, 'handle_linkly_admin_connect_callback' ] );
		add_action( 'linkly_notice_hook', [ $this, 'display_client_credentials_saved_notice' ] );
		add_action( 'linkly_notice_hook', [ $this, 'display_client_credentials_save_error_notice' ] );
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
		     || ! get_transient( 'linkly_display_client_credentials_save_error' )
		) {
			return;
		}

		echo '<div class="error notice is-dismissible" ><p>';
		esc_html_e( "client.connection-error", 'linkly-for-woocommerce' );
		echo ': ' . esc_html( get_transient( 'linkly_display_client_credentials_save_error' ) );
		echo '</p></div>';

		// Delete the transient so that the notice doesn't keep showing up on refresh
		delete_transient( 'linkly_display_client_credentials_save_error' );
	}

	public function linkly_admin_handle_save() {
		if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] !== 'linkly-for-woocommerce' ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			throw new Exception( 'User is not an admin' );
		}

		if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'linkly_credentials' )) {
			$this->handle_save_client_credentials();
		} else if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'linkly_button_style' ) ) {
			$this->handle_save_button_style();
		} else if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'linkly_admin_connect' ) ) {
			$this->handle_linkly_admin_connect();
		} else {
			throw new Exception( 'Invalid CSRF token' );
		}
	}

	private function handle_save_client_credentials() {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'linkly_credentials' ) ) {
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
			$clientCredentials = [
				'clientId'     => $clientId,
				'clientSecret' => $clientSecret,
			];
			$this->ssoHelper->verifyClientCredentials( $clientCredentials );
			update_option( 'linkly_settings_app_connected', true );
			set_transient( 'linkly_client_credentials_saved', true, 5 );
		} catch ( IdentityProviderException $e ) {
			update_option( 'linkly_settings_app_connected', false );
			set_transient( 'linkly_display_client_credentials_save_error', sanitize_text_field( $e->getResponseBody()['error'] ), 5 );
		} finally {
			$redirect_url = remove_query_arg( 'client_id', sanitize_url($_SERVER['HTTP_REFERER']) );
			wp_redirect( esc_url( $redirect_url ) );
			exit;
		}
	}

	private function handle_save_button_style() {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'linkly_button_style' ) ) {
			throw new Exception( 'Invalid CSRF token' );
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
	public function handle_linkly_admin_connect() {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'linkly_admin_connect' ) ) {
			throw new Exception( 'Invalid CSRF token' );
		}

		$sanitizedReturnUrl = sanitize_url( admin_url( "admin.php?linkly_admin_connect_callback" ) );

		// $corsUrl is pure the domain name without the path if there is a port number it is included
		$corsUrl = parse_url( get_site_url(), PHP_URL_SCHEME ) . '://' . parse_url( get_site_url(), PHP_URL_HOST );
		$port    = parse_url( get_site_url(), PHP_URL_PORT );

		if ( $port ) {
			$corsUrl .= ':' . $port;
		}

		$params = [
			'returnUrl'             => $sanitizedReturnUrl,
			'clientName'            => get_bloginfo( 'name' ),
			'allowedCorsOrigin'     => $corsUrl,
			'postLogoutRedirectUri' => get_site_url(),
			'redirectUri'           => get_site_url() . '?linkly_callback',
		];

		$this->ssoHelper->linkClientRedirect( $params );
		exit;
	}

	public function handle_linkly_admin_connect_callback() {
		if ( ! isset( $_GET['linkly_admin_connect_callback'] ) ) {
			return;
		}

		if ( empty( $_GET['client_id'] ) ) {
			error_log( "Client ID is empty in callback URL for Linkly Admin Connect" );
			throw new Exception( 'Client ID is empty in callback URL for Linkly Admin Connect' );
		}

		$sanitizedClientId = sanitize_text_field( $_GET['client_id'] );

		$query = 'admin.php?page=linkly-for-woocommerce';

		try {
			$this->ssoHelper->linkClientCallback();
			$query .= '&client_id=' . $sanitizedClientId;
		} catch ( Exception $e ) {
			error_log( "Error in Linkly Admin Connect callback: State does not match" );
			set_transient( 'linkly_display_client_credentials_save_error', 'state does not match' );
		} finally {
			wp_redirect( esc_url( admin_url( $query ), null, 'redirect' ) );
			exit;
		}
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

	public function linkly_admin_style() {
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
