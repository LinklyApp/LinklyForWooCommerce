<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$buttonStyle   = get_option( 'linkly_button_style' );
$logoStyle     = $buttonStyle === 'primary' ? 'light' : 'dark';
$linklyHelpers = LinklyHelpers::instance();

$textDomain = 'linkly-for-woocommerce';

?>

<div class="linkly-admin-page">
    <h1>Linkly</h1>
	<?php if ( is_plugin_inactive( 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' ) ) : ?>
        <div class="linkly-warning">
            <img src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . 'assets/images/package_warning.svg' ) ?>">
            <div class="linkly-warning-description">
				<?php esc_html_e( "warning.pdf-invoice-plugin-not-activated", $textDomain ) ?>
            </div>
        </div>
	<?php endif; ?>
    <p>
		<?php
		if ( ! $linklyHelpers->isConnected() ) {
			esc_html_e( "admin-description-not-linked", $textDomain );
		} else {
			esc_html_e( "admin-description-linked", $textDomain );
		}
		?>
    </p>
	<?php if ( ! $linklyHelpers->isConnected() ) : ?>
        <div class="linkly-form-group">
            <div class="linkly-button <?php echo esc_attr( $buttonStyle ) ?>">
                <a href="<?php echo esc_url( home_url( "?linkly_request_token=" .
				                                       urlencode( "/wp-admin/admin.php?page=linkly-for-woocommerce" ) ) ) ?>">
                    <span><?php esc_html_e( "admin-connect-button", $textDomain ) ?></span>
                    <img
                            src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ) ?>"></a>
            </div>
        </div>
	<?php endif; ?>
	<?php if ( $linklyHelpers->isConnected() ) : ?>
        <div class="linkly-form-group">
            <div class="linkly-button <?php echo esc_attr( $buttonStyle ) ?>">
                <a href="https://web.linkly.me"
                   target="_blank"><span><?php esc_html_e( "go-to-linkly-button", $textDomain ) ?></span>
                    <img
                            src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ) ?>"></a>
            </div>
        </div>
	<?php endif; ?>
    <form method="post">
		<?php wp_nonce_field( 'linkly_credentials' ); ?>
        <div class="linkly-form-group">
            <label class="linkly-form-label" for="linkly_client_id">
				<?php esc_html_e( "client.id", $textDomain ); ?>
            </label>
            <input name="linkly_client_id" id="linkly_client_id" class="linkly-form-input" type="text"
                   value="<?php echo esc_html( get_option( 'linkly_settings_app_key' ) ) ?>" disabled/>
        </div>
        <div class="linkly-form-group">
            <label class="linkly-form-label" for="linkly_client_secret">
				<?php esc_html_e( "client.secret", $textDomain ); ?>
            </label>
            <input name="linkly_client_secret" id="linkly_client_secret" class="linkly-form-input" type="password"
                   value="<?php echo esc_attr( get_option( 'linkly_settings_app_secret' ) ) ?>" disabled/>
            <span class="linkly-secret-eye"><i id="passwordToggler"><img
                            src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-open.svg" ) ?>"></i></span>
        </div>
        <div id="linkly_credentials_button" class="linkly-credentials-button">
            <button id="linkly_edit_credential_button" class="button-primary" type="button">
				<?php esc_html_e( "edit_credentials", $textDomain ); ?>
            </button>
            <button id="linkly_save_credentials_button" class="button-primary" type="submit">
				<?php esc_html_e( "save_changes", $textDomain ); ?>
            </button>
            <button id="linkly_cancel_edit_credential_button" class="button-primary" type="button">
				<?php esc_html_e( "cancel", $textDomain ); ?>
            </button>
        </div>
    </form>
    <form method="post">
		<?php wp_nonce_field( 'linkly_button_style' ); ?>
        <strong><?php esc_html_e( 'button_style.title', $textDomain ) ?></strong>
        <p>
			<?php esc_html_e( 'button_style.change', $textDomain ) ?>
        </p>
        <div class="linkly-form-group">
            <button type="submit" name="linkly_button_style" class="linkly-button primary" value="primary">
                <span><?php esc_html_e( "button_style.primary", $textDomain ) ?></span>
                <img src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-light.svg" ) ?>"
                     alt="Linkly">
            </button>
            <p class="linkly-button-current">
				<?php get_option( 'linkly_button_style' ) === 'primary' ? esc_html_e( 'button_style.current', $textDomain ) : '' ?>
            </p>
        </div>
        <div class="linkly-form-group">
            <button type="submit" name="linkly_button_style" class="linkly-button secondary" value="secondary">
                <span><?php esc_html_e( "button_style.secondary", $textDomain ) ?></span>
                <img src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-dark.svg" ) ?>"
                     alt="Linkly">
            </button>
            <p class="linkly-button-current">
				<?php get_option( 'linkly_button_style' ) === 'secondary' ? esc_html_e( 'button_style.current', $textDomain ) : '' ?>
            </p>
        </div>
    </form>


    <script>
        var clientId = document.getElementById('linkly_client_id');
        var clientSecret = document.getElementById('linkly_client_secret');
        var editButton = document.getElementById('linkly_edit_credential_button');
        var saveButton = document.getElementById('linkly_save_credentials_button');
        var cancelButton = document.getElementById('linkly_cancel_edit_credential_button');
        var passwordToggler = document.getElementById('passwordToggler');
        var edit = false;

        saveButton.style.display = 'none';
        cancelButton.style.display = 'none';

        if (clientId.value === '' || clientSecret.value === '') {

            clientId.removeAttribute('disabled');
            clientSecret.removeAttribute('disabled');

            editButton.style.display = 'none';
            saveButton.style.display = 'inline-block';
            cancelButton.style.display = 'none';
        }

        function showHidePassword() {
            if (clientSecret.type === 'password') {
                clientSecret.setAttribute('type', 'text');
                passwordToggler.getElementsByTagName('img')[0].src = '<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-closed.svg" ) ?>';
            } else {
                passwordToggler.getElementsByTagName('img')[0].src = '<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-open.svg" ) ?>';
                clientSecret.setAttribute('type', 'password');
            }

        }

        function setCredentialsEditable() {
            edit = !edit;

            if (edit) {
                clientId.removeAttribute('disabled');
                clientSecret.removeAttribute('disabled');

                editButton.style.display = 'none';
                saveButton.style.display = 'inline-block';
                cancelButton.style.display = 'inline-block';

            } else {
                clientId.setAttribute('disabled', 'disabled');
                clientSecret.setAttribute('disabled', 'disabled');

                editButton.style.display = 'inline-block';
                saveButton.style.display = 'none';
                cancelButton.style.display = 'none';
            }
        }

        passwordToggler.addEventListener('click', showHidePassword);
        editButton.addEventListener('click', setCredentialsEditable);
        cancelButton.addEventListener('click', setCredentialsEditable);
    </script>
</div>
