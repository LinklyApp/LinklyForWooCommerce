<?php

class MementoOrder
{
    public function __construct()
    {
        add_action('woocommerce_order_status_processing', [$this, 'memento_get_order']);
    }

    function memento_get_order($order_id)
    {
        $order = wc_get_order($order_id);
        $bInvoice = WCOrderToMementoInvoiceMapper::map($order);
    }
}

new MementoOrder();
