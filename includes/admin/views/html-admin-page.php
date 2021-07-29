<?php
defined('ABSPATH') or exit;

?>

<div class="memento-admin-page">
    <h1>Memento</h1>
    <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
        magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
        consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla
        pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est
        laborum.
    </p>
    <form method="post" action="/wp-admin/options-general.php?page=memento-for-woocommerce">
        <?php wp_nonce_field( 'memento_credentials' ); ?>
        <div class="memento-form__group">
            <label class="memento-form__label">Client ID</label>
            <input name="memento_client_id" class="memento-form__input" type="text" value="<?= get_option('memento_settings_app_key') ?>"/>
        </div>
        <div class="memento-form__group">
            <label class="memento-form__label">Client Secret</label>
            <input name="memento_client_secret" class="memento-form__input" type="text" value="<?= get_option('memento_settings_app_secret') ?>">
        </div>
        <button class="button-primary" type="submit">Save changes</button>
    </form>
</div>
