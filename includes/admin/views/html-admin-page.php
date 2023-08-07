<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$buttonStyle = get_option( 'linkly_button_style' );
$logoStyle   = $buttonStyle === 'primary' ? 'light' : 'dark';
$linklyLanguageHelper = LinklyLanguageHelper::instance();
$linklyHelpers = LinklyHelpers::instance();
?>

<div class="linkly-admin-page">
    <h1>Linkly</h1>
	<?php if ( is_plugin_inactive( 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' ) ) : ?>
        <div class="linkly-warning">
            <img src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . 'assets/images/package_warning.svg' ) ?>">
            <div class="linkly-warning-description">
				<?php echo esc_html( $linklyLanguageHelper->get( "warning.pdf-invoice-plugin-not-activated" ) ) ?>
            </div>
        </div>
	<?php endif; ?>
    <p>
		<?php
		if ( ! $linklyHelpers->isConnected() ) {
			echo esc_html( $linklyLanguageHelper->get( "admin-description-not-linked" ) );
		} else {
			echo esc_html( $linklyLanguageHelper->get( "admin-description-linked" ) );
		}
		?>
    </p>
	<?php if ( ! $linklyHelpers->isConnected() ) : ?>
        <div class="linkly-form-group">
            <div class="linkly-button <?php echo esc_attr($buttonStyle) ?>">
                <a href="<?php echo esc_url( home_url( "?linkly_request_token=" .
				                                       urlencode( "/wp-admin/admin.php?page=linkly-for-woocommerce" ) ) ) ?>">
                    <span><?php echo esc_html( $linklyLanguageHelper->get( "admin-connect-button" ) ) ?></span>
                    <img
                            src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ) ?>"></a>
            </div>
        </div>
	<?php endif; ?>
	<?php if ( $linklyHelpers->isConnected() ) : ?>
        <div class="linkly-form-group">
            <div class="linkly-button <?php echo esc_attr($buttonStyle) ?>">
                <a href="https://web.linkly.me"
                   target="_blank"><span><?php echo esc_html( $linklyLanguageHelper->get( "go-to-linkly-button" ) ) ?></span>
                    <img
                            src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ) ?>"></a>
            </div>
        </div>
	<?php endif; ?>
    <form method="post">
		<?php wp_nonce_field( 'linkly_credentials' ); ?>
        <div class="linkly-form-group">
            <label class="linkly-form-label" for="linkly_client_id">
				<?php echo esc_html( $linklyLanguageHelper->get( "client.id" ) ); ?>
            </label>
            <input name="linkly_client_id" id="linkly_client_id" class="linkly-form-input" type="text"
                   value="<?php echo esc_html( get_option( 'linkly_settings_app_key' ) ) ?>" disabled/>
        </div>
        <div class="linkly-form-group">
            <label class="linkly-form-label" for="linkly_client_secret">
				<?php echo esc_html( $linklyLanguageHelper->get( "client.secret" ) ); ?>
            </label>
            <input name="linkly_client_secret" id="linkly_client_secret" class="linkly-form-input" type="password"
                   value="<?php echo esc_attr( get_option( 'linkly_settings_app_secret' ) ) ?>" disabled/>
            <span class="linkly-secret-eye"><i id="passwordToggler"><img
                            src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-open.svg" ) ?>"></i></span>
        </div>
        <div id="linkly_credentials_button" class="linkly-credentials-button">
            <button id="linkly_edit_credential_button" class="button-primary" type="button">
				<?php echo esc_html($linklyLanguageHelper->get( "edit_credentials" )); ?>
            </button>
            <button id="linkly_save_credentials_button" class="button-primary" type="submit">
				<?php echo esc_html($linklyLanguageHelper->get( "save_changes" )); ?>
            </button>
            <button id="linkly_cancel_edit_credential_button" class="button-primary" type="button">
				<?php echo esc_html($linklyLanguageHelper->get( "cancel" )); ?>
            </button>
        </div>
    </form>
    <form method="post">
		<?php wp_nonce_field( 'linkly_button_style' ); ?>
        <strong><?php echo esc_html($linklyLanguageHelper->get( 'button_style.title' )) ?></strong>
        <p>
			<?php echo esc_html($linklyLanguageHelper->get( 'button_style.change' )) ?>
        </p>
        <div class="linkly-form-group">
            <button type="submit" name="linkly_button_style" class="linkly-button primary" value="primary">
                <a target="_blank"><span><?php echo esc_html($linklyLanguageHelper->get( "button_style.primary" )) ?></span>
                    <img src="<?php echo esc_url(LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-light.svg") ?>"
                         alt="Linkly"></a>
            </button>
            <p class="linkly-button-current">
				<?php echo get_option( 'linkly_button_style' ) === 'primary' ? esc_html($linklyLanguageHelper->get( 'button_style.current' )) : '' ?>
            </p>
        </div>
        <div class="linkly-form-group">
            <button type="submit" name="linkly_button_style" class="linkly-button secondary" value="secondary">
                <a target="_blank"><span><?php echo esc_html($linklyLanguageHelper->get( "button_style.secondary" )) ?></span>
                    <img src="<?php echo esc_url(LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-dark.svg") ?>"
                         alt="Linkly"></a>
            </button>
            <p class="linkly-button-current">
				<?php echo get_option( 'linkly_button_style' ) === 'secondary' ? esc_html($linklyLanguageHelper->get( 'button_style.current') ) : '' ?>
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
                passwordToggler.getElementsByTagName('img')[0].src = '<?php echo esc_url(LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-closed.svg") ?>';
            } else {
                passwordToggler.getElementsByTagName('img')[0].src = '<?php echo esc_url(LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-open.svg") ?>';
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
