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
				<a href="<?= home_url( "?linkly_request_token=" . urlencode( "/wp-admin/admin.php?page=linkly-for-woocommerce" ) ) ?>"><span><?= LinklyLanguageHelper::instance()->get( "admin-connect-button" ) ?></span>
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
	<form method="post" action="">
		<?php wp_nonce_field( 'linkly_credentials' ); ?>
		<div class="linkly-form-group">
			<label class="linkly-form-label">
				<?= LinklyLanguageHelper::instance()->get( "client.id" ); ?>
			</label>
			<input name="linkly_client_id" class="linkly-form-input" type="text"
			       value="<?= get_option( 'linkly_settings_app_key' ) ?>"/>
		</div>
		<div class="linkly-form-group">
			<label class="linkly-form-label">
				<?= LinklyLanguageHelper::instance()->get( "client.secret" ); ?>
			</label>
			<input name="linkly_client_secret" class="linkly-form-input" type="text"
			       value="<?= get_option( 'linkly_settings_app_secret' ) ?>">
		</div>
		<button class="button-primary" type="submit">
			<?= LinklyLanguageHelper::instance()->get( "save_changes" ); ?>
		</button>
	</form>

	<?php if ( LinklyHelpers::instance()->isConnected() ) { ?>
	<form method="post" action="">
		<?php wp_nonce_field( 'linkly_button_style' ); ?>
		<p>
			<strong><?= LinklyLanguageHelper::instance()->get( 'button_style.title' ) ?></strong>
		</p>
		<div class="linkly-form-group linkly-form-radio">
			<input type="radio" name="linkly_button_style" id="button_style_primary" value="primary"
				<?= get_option( 'linkly_button_style' ) === 'primary' ? 'checked' : '' ?>>
			<label for="button_style_primary">
				<div class="linkly-button primary">
					<a target="_blank"><span><?= LinklyLanguageHelper::instance()->get( "button_style.primary" ) ?></span>
						<img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-light.svg" ?>"></a>
				</div>
			</label>
		</div>
		<div class="linkly-form-group linkly-form-radio">
			<input type="radio" name="linkly_button_style" id="button_style_secondary" value="secondary"
				<?= get_option( 'linkly_button_style' ) === 'secondary' ? 'checked' : '' ?>>
			<label for="button_style_secondary">
				<div class="linkly-button secondary">
					<a target="_blank"><span><?= LinklyLanguageHelper::instance()->get( "button_style.secondary" ) ?></span>
						<img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-dark.svg" ?>"></a>
				</div>
			</label>
		</div>
		<button class="button-primary" type="submit">
			<?= LinklyLanguageHelper::instance()->get( "save_changes" ); ?>
		</button>
	</form>
    <?php } ?>

</div>
