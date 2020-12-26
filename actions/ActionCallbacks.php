<?php

function updateUserMetaSSO($customer, $updated_props)
{
    $sso_props = ['sso_version' => 'sso_version'];
    $changed_props = $customer->get_changes();

    foreach ( $sso_props as $meta_key => $prop ) {
        if ( ! isset( $changed_props['sso_version'] )) {
            continue;
        }

        if ( update_user_meta( $customer->get_id(), $meta_key, $customer->{"get_$prop"}( 'edit' ) ) ) {
            $updated_props[] = $prop;
        }
    }
}
