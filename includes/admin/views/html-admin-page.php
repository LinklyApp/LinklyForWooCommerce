<?php
defined('ABSPATH') or exit;
$buttonStyle = get_option('linkly_button_style');
$logoStyle = get_option('linkly_button_style') === 'purple' ? 'light' : 'dark';

?>

<div class="linkly-admin-page">
    <h1>Linkly</h1>
    <?php if (is_plugin_inactive('woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php')) { ?>
	    <div class="linkly-warning">
            <img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/package_warning.svg" ?>">
		    <div class="linkly-warning-description">
			    <?= LinklyLanguageHelper::instance()->get("warning.pdf-invoice-plugin-not-activated") ?>
		    </div>
	    </div>
    <?php } ?>

    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
        magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
        consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
        pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est
        laborum.
    </p>
    <?php if (!LinklyHelpers::instance()->isConnected()) { ?>
        <div class="linkly-form-group">
            <div class="linkly-button <?= $buttonStyle ?>">
                <a href="<?= home_url("?linkly_request_token=" . urlencode("/wp-admin/admin.php?page=linkly-for-woocommerce")) ?>"><span><?= LinklyLanguageHelper::instance()->get("admin-connect-button") ?></span>
                    <img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ?>"></a>
            </div>
        </div>
    <?php } ?>
    <?php if (LinklyHelpers::instance()->isConnected()) { ?>
        <div class="linkly-form-group">
            <div class="linkly-button <?= $buttonStyle ?>">
                <a href="https://web.linkly.me" target="_blank"><span><?= LinklyLanguageHelper::instance()->get("go-to-linkly-button") ?></span>
                    <img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ?>"></a>
            </div>
        </div>
    <?php } ?>
    <form method="post" action="">
        <?php wp_nonce_field('linkly_credentials'); ?>
        <div class="linkly-form-group">
            <label class="linkly-form-label">
                <?= LinklyLanguageHelper::instance()->get("client.id"); ?>
            </label>
            <input name="linkly_client_id" class="linkly-form-input" type="text"
                   value="<?= get_option('linkly_settings_app_key') ?>"/>
        </div>
        <div class="linkly-form-group">
            <label class="linkly-form-label">
                <?= LinklyLanguageHelper::instance()->get("client.secret"); ?>
            </label>
            <input name="linkly_client_secret" class="linkly-form-input" type="text"
                   value="<?= get_option('linkly_settings_app_secret') ?>">
        </div>
        <div class="linkly-form-group">
            <label class="linkly-form-label"><?= LinklyLanguageHelper::instance()->get("environment.title"); ?></label>
            <select name="linkly_environment" class="linkly-form-input">
                <option value="prod" <?= get_option('linkly_settings_environment') === 'prod' ? 'selected' : '' ?>>
                    <?= LinklyLanguageHelper::instance()->get("environment.production"); ?>
                </option>
                <option value="beta" <?= get_option('linkly_settings_environment') === 'beta' ? 'selected' : '' ?>>
                    <?= LinklyLanguageHelper::instance()->get("environment.beta"); ?>
                </option>
                <option value="local" <?= get_option('linkly_settings_environment') === 'local' ? 'selected' : '' ?>>
                    <?= LinklyLanguageHelper::instance()->get("environment.local"); ?>
                </option>
            </select>
        </div>
        <button class="button-primary" type="submit">
            <?= LinklyLanguageHelper::instance()->get("save_changes"); ?>
        </button>
    </form>

    <form method="post" action="">
        <?php wp_nonce_field('linkly_button_style'); ?>
        <div class="linkly-form-group">
            <label class="linkly-form-label">
                <?= LinklyLanguageHelper::instance()->get("button_style.title"); ?>
            </label>
            <select name="linkly_button_style" class="linkly-form-input">
                <option value="purple" <?= get_option('linkly_button_style') === 'purple' ? 'selected' : '' ?>>
                    <?= LinklyLanguageHelper::instance()->get("button_style.purple"); ?>
                </option>
                <option value="white" <?= get_option('linkly_button_style') === 'white' ? 'selected' : '' ?>>
                    <?= LinklyLanguageHelper::instance()->get("button_style.white"); ?>
                </option>
            </select>
        </div>
        <button class="button-primary" type="submit">
            <?= LinklyLanguageHelper::instance()->get("save_changes"); ?>
        </button>
    </form>

</div>
