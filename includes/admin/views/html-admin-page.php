<?php
defined('ABSPATH') or exit;

?>

<div class="memento-admin-page">
    <h1>Linkly</h1>
    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
        magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
        consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
        pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est
        laborum.
    </p>
    <form method="post" action="<?= home_url("/wp-admin/options-general.php?page=linkly-for-woocommerce") ?>">
        <?php wp_nonce_field('memento_credentials'); ?>
        <?php if (!LinklyHelpers::instance()->isConnected()) { ?>
            <div class="memento-form-group">
                <div class="linkly-button">
                    <a href="<?= home_url("?linkly_request_token=" . urlencode("/wp-admin/options-general.php?page=linkly-for-woocommerce")) ?>"><span><?= LinklyLanguageHelper::instance()->get("admin-connect-button") ?></span>
                        <img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal.svg" ?>"></a>
                </div>
            </div>
        <?php } ?>
        <div class="memento-form-group">
            <label class="memento-form-label"><?= LinklyLanguageHelper::instance()->get("language"); ?></label>
            <select name="memento_language" class="memento-form-input">
                <?= LinklyLanguageHelper::instance()->getLanguageSelectOptions() ?>
            </select>
        </div>
        <div class="memento-form-group">
            <label class="memento-form-label">Client ID</label>
            <input name="memento_client_id" class="memento-form-input" type="text"
                   value="<?= get_option('memento_settings_app_key') ?>"/>
        </div>
        <div class="memento-form-group">
            <label class="memento-form-label">Client Secret</label>
            <input name="memento_client_secret" class="memento-form-input" type="text"
                   value="<?= get_option('memento_settings_app_secret') ?>">
        </div>
        <div class="memento-form-group">
            <label class="memento-form-label"><?= LinklyLanguageHelper::instance()->get("environment.title"); ?></label>
            <select name="memento_environment" class="memento-form-input">
                <option value="prod" <?= get_option('memento_settings_environment') === 'prod' ? 'selected' : '' ?>>
                    <?= LinklyLanguageHelper::instance()->get("environment.production"); ?>
                </option>
                <option value="beta" <?= get_option('memento_settings_environment') === 'beta' ? 'selected' : '' ?>>
                    <?= LinklyLanguageHelper::instance()->get("environment.beta"); ?>
                </option>
                <option value="local" <?= get_option('memento_settings_environment') === 'local' ? 'selected' : '' ?>>
                    <?= LinklyLanguageHelper::instance()->get("environment.local"); ?>
                </option>
            </select>
        </div>
        <button class="button-primary" type="submit">Save Changes</button>
    </form>
</div>
