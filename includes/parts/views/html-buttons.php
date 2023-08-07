<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$linklyHelpers = LinklyHelpers::instance();
$buttonUrl     = "";
$buttonVariant = "";
$showButton    = true;
$buttonStyle   = get_option( 'linkly_button_style' );
$logoStyle     = $buttonStyle === 'primary' ? 'light' : 'dark';
$textDomain    = 'linkly-for-woocommerce';

if ( ! isset( $onlyLinkButton ) ) {
	$onlyLinkButton = false;
}

$rawCurrentUri = $_SERVER['REQUEST_URI'];
$sanitizedCurrentUri  = filter_var($rawCurrentUri, FILTER_SANITIZE_URL);

if ( is_user_logged_in() && linkly_is_wp_user_linkly_user( get_current_user_id() ) && ! $onlyLinkButton ) {
	$buttonUrl  = '?linkly_change_address_action=' . urlencode( $sanitizedCurrentUri );
	$buttonVariant = 'changeAddressButton';
} else if ( is_user_logged_in() && ! linkly_is_wp_user_linkly_user( get_current_user_id() ) ) {
	$buttonUrl  = '?linkly_link_account_action=' . urlencode( $sanitizedCurrentUri );
	$buttonVariant = 'linkAccountButton';
} else if ( ! $onlyLinkButton ) {
	$buttonUrl  = '?linkly_login_action=' . urlencode( $sanitizedCurrentUri );
	$buttonVariant = 'loginButton';
} else {
	$showButton = false;
}

?>
<?php if ( $showButton ): ?>
    <div id="linkly-sso-button" class="linkly-sso-button">
        <div class="linkly-button <?php echo esc_attr( $buttonStyle ) ?>">
            <a href="<?php echo esc_url( $buttonUrl ) ?>">
                <span><?php esc_html_e( $buttonVariant, $textDomain ) ?></span>
                <img src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL .
				                              'assets/images/logo-horizontal-' . $logoStyle . '.svg' ); ?>"
                     alt="Linkly">
            </a>
        </div>
    </div>
<?php endif; ?>