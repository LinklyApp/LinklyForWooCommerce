<?php
defined( 'ABSPATH' ) or exit;
$buttonStyle = get_option( 'linkly_button_style' );
$logoStyle   = get_option( 'linkly_button_style' ) === 'primary' ? 'light' : 'dark';

?>

<div class="linkly-admin-page">
	<h1>Linkly</h1>
	<?php if ( is_plugin_inactive( 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php' ) ) { ?>
		<div class="linkly-warning">
			<img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/package_warning.svg" ?>">
			<div class="linkly-warning-description">
				<?= LinklyLanguageHelper::instance()->get( "warning.pdf-invoice-plugin-not-activated" ) ?>
			</div>
		</div>
	<?php } ?>
	<p>
		<?php if ( ! LinklyHelpers::instance()->isConnected() ) { ?>
		<?= LinklyLanguageHelper::instance()->get( "admin-description-not-linked" ) ?>
        <?php } ?>
        <?php if ( LinklyHelpers::instance()->isConnected() ) { ?>
        <?= LinklyLanguageHelper::instance()->get( "admin-description-linked" ) ?>
        <?php } ?>
	</p>
	<?php if ( ! LinklyHelpers::instance()->isConnected() ) { ?>
		<div class="linkly-form-group">
			<div class="linkly-button <?= $buttonStyle ?>">
				<a href="<?= home_url( "?linkly_request_token=" . urlencode( "/wp-admin/admin.php?page=linkly-for-woocommerce" ) ) ?>">
                    <span><?= LinklyLanguageHelper::instance()->get( "admin-connect-button" ) ?></span>
					<img
						src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ?>"></a>
			</div>
		</div>
	<?php } ?>
	<?php if ( LinklyHelpers::instance()->isConnected() ) { ?>
		<div class="linkly-form-group">
			<div class="linkly-button <?= $buttonStyle ?>">
				<a href="https://web.linkly.me"
				   target="_blank"><span><?= LinklyLanguageHelper::instance()->get( "go-to-linkly-button" ) ?></span>
					<img
						src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ?>"></a>
			</div>
		</div>
	<?php } ?>
	<form method="post">
		<?php wp_nonce_field( 'linkly_credentials' ); ?>
		<div class="linkly-form-group">
			<label class="linkly-form-label" for="linkly_client_id">
				<?= LinklyLanguageHelper::instance()->get( "client.id" ); ?>
			</label>
			<input name="linkly_client_id" id="linkly_client_id" class="linkly-form-input" type="text"
			       value="<?= get_option( 'linkly_settings_app_key' ) ?>" disabled />
		</div>
		<div class="linkly-form-group">
			<label class="linkly-form-label" for="linkly_client_secret">
				<?= LinklyLanguageHelper::instance()->get( "client.secret" ); ?>
			</label>
			<input name="linkly_client_secret" id="linkly_client_secret" class="linkly-form-input" type="password"
			       value="<?= get_option( 'linkly_settings_app_secret' ) ?>" disabled />
            <span class="linkly-secret-eye"><i id="toggler"><img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-open.svg" ?>"></i></span>
        </div>
        <div id="linkly_credentials_button" class="linkly-credentials-button">
            <div id="linkly_credential_edit_button">
                <button class="button-primary" type="button">
                    <?= LinklyLanguageHelper::instance()->get( "edit_credentials" ); ?>
                </button>
            </div>
        </div>
	</form>
	<form method="post">
		<?php wp_nonce_field( 'linkly_button_style' ); ?>
		<strong><?= LinklyLanguageHelper::instance()->get( 'button_style.title' ) ?></strong>
        <p>
	        <?= LinklyLanguageHelper::instance()->get('button_style.change') ?>
        </p>
        <button type="submit" name="linkly_button_style" class="linkly-button primary" value="primary">
            <a target="_blank"><span><?= LinklyLanguageHelper::instance()->get( "button_style.primary" ) ?></span>
                <img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-light.svg" ?>"></a>
        </button>
        <button type="submit" name="linkly_button_style" class="linkly-button secondary" value="secondary">
            <a target="_blank"><span><?= LinklyLanguageHelper::instance()->get( "button_style.secondary" ) ?></span>
                <img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-dark.svg" ?>" alt="Linkly"></a>
        </button>
	</form>

    <script>
        const clientId = document.getElementById('linkly_client_id');
        const clientSecret = document.getElementById('linkly_client_secret');
        const editButton = document.getElementById('linkly_credential_edit_button').getElementsByTagName('button')[0];
        const credentialsButton = document.getElementById('linkly_credentials_button');
        const toggler = document.getElementById('toggler');
        let edit = false;

        if (clientId.value === '' && clientSecret.value === '') {

            clientId.removeAttribute('disabled');
            clientSecret.removeAttribute('disabled');
            editButton.remove();

            const saveButton = document.createElement('button');
            saveButton.setAttribute('class', 'button-primary');
            saveButton.setAttribute('type', 'submit');
            saveButton.innerText = '<?= LinklyLanguageHelper::instance()->get( "save_changes" ) ?>';
            credentialsButton.appendChild(saveButton);
        }

        showHidePassword = () => {
            if (clientSecret.type === 'password') {
                clientSecret.setAttribute('type', 'text');
                toggler.getElementsByTagName('img')[0].src = '<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-closed.svg" ?>';
            } else {
                toggler.getElementsByTagName('img')[0].src = '<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/password-eye-open.svg" ?>';
                clientSecret.setAttribute('type', 'password');
            }

        };

        setCredentialsEditable = () => {
            edit = !edit;

            if (edit) {
                clientId.removeAttribute('disabled');
                clientSecret.removeAttribute('disabled');

                const saveButton = document.createElement('button');
                saveButton.setAttribute('class', 'button-primary');
                saveButton.setAttribute('type', 'submit');
                saveButton.innerHTML = '<?= LinklyLanguageHelper::instance()->get( "save_changes" ); ?>';
                credentialsButton.appendChild(saveButton);

                editButton.innerHTML = '<?= LinklyLanguageHelper::instance()->get( "cancel" ); ?>'
            } else {
                clientId.setAttribute('disabled', 'disabled');
                clientSecret.setAttribute('disabled', 'disabled');

                if (credentialsButton.getElementsByTagName('button').length > 1) {
                    credentialsButton.getElementsByTagName('button')[1].remove();
                }

                editButton.innerHTML = '<?= LinklyLanguageHelper::instance()->get( "edit_credentials" ); ?>';
            }
        }

        toggler.addEventListener('click', showHidePassword);
        editButton.addEventListener('click', setCredentialsEditable);
    </script>
</div>
