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

if ( ! isset( $onlyLinkButton ) ) {
	$onlyLinkButton = false;
}

$sanitizedCurrentUri = sanitize_text_field($_SERVER['REQUEST_URI']);;

if ( is_user_logged_in() && linkly_is_wp_user_linkly_user( get_current_user_id() ) && ! $onlyLinkButton ) {
	$buttonUrl     = '?linkly_change_address_action=' . urlencode( $sanitizedCurrentUri );
	$buttonVariant = 'change-address-button';
} else if ( is_user_logged_in() && ! linkly_is_wp_user_linkly_user( get_current_user_id() ) ) {
	$buttonUrl     = '?linkly_link_account_action=' . urlencode( $sanitizedCurrentUri );
	$buttonVariant = 'link-account-button';
} else if ( ! $onlyLinkButton ) {
	$buttonUrl     = '?linkly_login_action=' . urlencode( $sanitizedCurrentUri );
	$buttonVariant = 'login-button';
} else {
	$showButton = false;
}

?>
<?php if ( $showButton ): ?>
    <div id="linkly-sso-button" class="linkly-sso-button">
        <div class="linkly-button <?php echo esc_attr( $buttonStyle ) ?>">
            <a href="<?php echo esc_url( $buttonUrl ) ?>">
                <span>
                    <?php
                    switch ( $buttonVariant ) {
	                    case 'change-address-button':
		                    esc_html_e( 'Change Address at', 'linkly-for-woocommerce' );
		                    break;
	                    case 'link-account-button':
		                    esc_html_e( 'Link Account with', 'linkly-for-woocommerce' );
		                    break;
	                    case 'login-button':
		                    esc_html_e( 'Login with', 'linkly-for-woocommerce' );
		                    break;
                    }
                    ?>
                </span>
                <img src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL .
				                              'assets/images/logo-horizontal-' . $logoStyle . '.svg' ); ?>"
                     alt="Linkly">
            </a>
        </div>
    </div>
<?php endif; ?>