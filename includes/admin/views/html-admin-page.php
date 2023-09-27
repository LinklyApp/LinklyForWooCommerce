<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$buttonStyle   = get_option( 'linkly_button_style' );
$logoStyle     = $buttonStyle === 'primary' ? 'light' : 'dark';
$linklyHelpers = LinklyHelpers::instance();

$sanitizedClientId = isset($_GET['client_id']) ? sanitize_text_field($_GET['client_id']) : '';

?>

<div id="clientSecretModal" class="modal">
    <div class="modal-content">
        <form action="<?php echo esc_url( remove_query_arg( 'client_id' ) ); ?>" method="post">
            <h2><?php esc_html_e( "enter-client-secret", 'linkly-for-woocommerce' ); ?></h2>

            <div class="linkly-form-group">
                <label class="linkly-form-label" for="linkly_modal_client_id">
					<?php esc_html_e( "client.id", 'linkly-for-woocommerce' ); ?>
                </label>
                <input name="linkly_client_id" id="linkly_modal_client_id" class="linkly-form-input" type="text"
                       value="<?php esc_attr_e( $sanitizedClientId ) ?>"
                       readonly/>
            </div>

            <div class="linkly-form-group">
                <label class="linkly-form-label" for="linkly_modal_client_secret">
					<?php esc_html_e( "client.secret", 'linkly-for-woocommerce' ); ?>
                </label>
                <input name="linkly_client_secret" id="linkly_modal_client_secret" class="linkly-form-input"
                       type="text"/>
            </div>

            <input type="hidden" name="linkly_credentials" value="">

            <?php wp_nonce_field( 'linkly_credentials' ); ?>

            <input type="submit" name="submit_client_secret" class="button-primary"
                   value="<?php esc_html_e( "save_changes", 'linkly-for-woocommerce' ); ?>">
            <button onclick="window.location.href = '<?php echo esc_js( esc_url( remove_query_arg( 'client_id' ) ) ); ?>'; return false;"
                    class="button-secondary" type="button">
				<?php esc_html_e( "cancel", 'linkly-for-woocommerce' ); ?>
            </button>
        </form>
    </div>
</div>


<div class="linkly-admin-page">
    <h1>Linkly settings</h1>
    <div class="linkly-admin-page__content">
		<?php if ( is_plugin_inactive( 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' ) ) : ?>
            <div class="linkly-warning">
                <img src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . 'assets/images/package_warning.svg' ) ?>">
                <div class="linkly-warning-description">
					<?php esc_html_e( "warning.pdf-invoice-plugin-not-activated", 'linkly-for-woocommerce' ) ?>
                </div>
            </div>
		<?php endif; ?>
		<?php do_action( 'linkly_notice_hook' ); ?>
        <div class="linkly-admin-page__row">
			<?php if ( $linklyHelpers->isConnectedWithVerification() ) : ?>
                <h3><?php esc_html_e( "client.status", 'linkly-for-woocommerce' ) ?>:
                    <span class="client-status client-status__connected">
                        <?php esc_html_e( "client.connected", 'linkly-for-woocommerce' ) ?>
                    </span></h3>
                <a class="button"
                   href="<?php echo esc_url( $linklyHelpers->getLinklyProvider()->getWebDomainUrl() ) ?>"
                   target="_blank"><?php esc_html_e( "go-to-linkly-button", 'linkly-for-woocommerce' ) ?>

                </a>
			<?php endif; ?>
			<?php if ( ! $linklyHelpers->isConnectedWithVerification() ) : ?>
                <h3><?php esc_html_e( "client.status", 'linkly-for-woocommerce' ) ?>:
                    <span class="client-status client-status__disconnected">
                        <?php esc_html_e( "client.disconnected", 'linkly-for-woocommerce' ) ?>
                    </span></h3>
                <form method="post" style="margin: 0">
					<?php wp_nonce_field( 'linkly_admin_connect' ); ?>
                    <input type="hidden" name="linkly_admin_connect" value="">
                    <button class="button-primary" type="submit">
						<?php esc_html_e( "admin-connect-button", 'linkly-for-woocommerce' ) ?>
                    </button>
                </form>
			<?php endif; ?>
        </div>
        <form method="post" class="linkly-admin-page__row">

			<?php wp_nonce_field( 'linkly_credentials' ); ?>
            <input type="hidden" name="linkly_credentials" value="">
            <div style="flex-basis: 100%">
                <h3><?php esc_html_e( "client.connection-settings", 'linkly-for-woocommerce' ); ?>
                </h3>

                <div class="linkly-form-group">
                    <label class="linkly-form-label" for="linkly_client_id">
						<?php esc_html_e( "client.id", 'linkly-for-woocommerce' ); ?>
                    </label>
                    <input name="linkly_client_id" id="linkly_client_id" class="linkly-form-input" type="text"
                           value="<?php echo esc_html( get_option( 'linkly_settings_app_key' ) ) ?>" disabled/>
                </div>
                <div class="linkly-form-group">
                    <label class="linkly-form-label" for="linkly_client_secret">
						<?php esc_html_e( "client.secret", 'linkly-for-woocommerce' ); ?>
                    </label>
                    <input name="linkly_client_secret" id="linkly_client_secret" class="linkly-form-input"
                           type="password"
                           value="<?php echo esc_attr( get_option( 'linkly_settings_app_secret' ) ) ?>" disabled/>
                    <span class="linkly-secret-eye"><i id="passwordToggler"><img
                                    src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-open.svg" ) ?>"></i></span>
                </div>

                <div id="linkly_credentials_button" class="linkly-credentials-button">
                    <button id="linkly_edit_credential_button" class="button-primary" type="button">
			            <?php esc_html_e( "edit_credentials", 'linkly-for-woocommerce' ); ?>
                    </button>
                    <button id="linkly_save_credentials_button" class="button-primary" type="submit">
			            <?php esc_html_e( "save_changes", 'linkly-for-woocommerce' ); ?>
                    </button>
                    <button id="linkly_cancel_edit_credential_button" class="button-secondary" type="button">
			            <?php esc_html_e( "cancel", 'linkly-for-woocommerce' ); ?>
                    </button>
                </div>
            </div>

        </form>
        <form method="post" id="linklyButtonForm" class="linkly-admin-page__row">
			<?php wp_nonce_field( 'linkly_button_style' ); ?>
            <div style="display: flex; justify-content: start; align-items: start">
                <h3><?php esc_html_e( 'button_style.title', 'linkly-for-woocommerce' ) ?></h3></div>
            <div class="linkly-button__wrapper <?php get_option( 'linkly_button_style' ) === 'primary' ? esc_attr_e( 'selected' ) : esc_attr_e( '' ) ?>">
                <div class="linkly-button  primary">

                    <a href="javascript:void(0);">
                        <span><?php esc_html_e( "button_style.primary", 'linkly-for-woocommerce' ) ?></span>
                        <img src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-light.svg" ) ?>"
                             alt="Linkly">
                    </a>
                </div>
            </div>
            <div class="linkly-button__wrapper <?php get_option( 'linkly_button_style' ) === 'secondary' ? esc_attr_e( 'selected' ) : esc_attr_e( '' ) ?>">
                <div class="linkly-button secondary">
                    <a href="javascript:void(0);">
                        <span><?php esc_html_e( "button_style.secondary", 'linkly-for-woocommerce' ) ?></span>
                        <img src="<?php echo esc_url( LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-dark.svg" ) ?>"
                             alt="Linkly">
                    </a>
                </div>
            </div>

            <!-- Hidden input field for button style -->
            <input type="hidden" name="linkly_button_style" id="buttonStyleInput">
            <!-- Invisible submit button -->
            <input type="submit" style="display: none;" id="hiddenSubmit">
        </form>

    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // For the modal
        let urlParams = new URLSearchParams(window.location.search);
        let clientId = urlParams.get('client_id');

        if (clientId) {
            document.getElementById('clientSecretModal').style.display = "block";
        }

        // For the button form
        const form = document.getElementById('linklyButtonForm');
        const primaryButton = document.querySelector('.linkly-button.primary');
        const secondaryButton = document.querySelector('.linkly-button.secondary');
        const buttonStyleInput = document.getElementById('buttonStyleInput');

        // Add click event listener to the primary button div
        primaryButton.addEventListener('click', function () {
            buttonStyleInput.value = 'primary'; // Set the hidden input value
            form.submit();
        });

        // Add click event listener to the secondary button div
        secondaryButton.addEventListener('click', function () {
            buttonStyleInput.value = 'secondary'; // Set the hidden input value
            form.submit();
        });
    });


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
