<?php

use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Provider\Invoice\MementoInvoice;
use Memento\OAuth2\Client\Provider\MementoProvider;

class MementoOrder
{


    /**
     * @var MementoInvoiceHelper
     */
    private $mementoInvoiceHelper;

    public function __construct(MementoInvoiceHelper $mementoInvoiceHelper)
    {
        $this->mementoInvoiceHelper = $mementoInvoiceHelper;
        add_action('woocommerce_order_status_processing', [$this, 'memento_get_order']);
    }

    function memento_get_order($order_id)
    {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        $customer = new WC_Customer($user_id);

        $memento_user_id = $customer->get_meta('memento_user_guid');

        $invoiceData = WCOrderToMementoInvoiceMapper::map($order, $memento_user_id);
        $mementoInvoice = new MementoInvoice($invoiceData);

        $this->mementoInvoiceHelper->sendInvoice($mementoInvoice);
    }
}

$mementoProvider = new MementoProvider([
    'clientId' => get_option('memento_settings_app_key'), // 'test-wp-plugin'
    'clientSecret' => get_option('memento_settings_app_secret'), // 'secret',
    'redirectUri' => rtrim(get_site_url() . '?memento-callback'),
    'environment' => get_option('memento_settings_environment') // options are "prod", "beta", "local"
]);

$mementoInvoiceHelper = new MementoInvoiceHelper($mementoProvider);

new MementoOrder($mementoInvoiceHelper);