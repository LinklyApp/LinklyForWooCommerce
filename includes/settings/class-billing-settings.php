<?php

defined('ABSPATH') or exit;

function billingRegisterSetting()
{
    register_setting('billing_options', 'billing_settings_app_key', 1);
    register_setting('billing_options', 'billing_settings_app_secret', 1);
}
