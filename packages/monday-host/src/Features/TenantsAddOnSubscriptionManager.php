<?php

namespace MondayCloneHost\Features;

use MondayCloneHost\Core\WPCSTenant;

class TenantsAddOnSubscriptionManager
{
    public function __construct()
    {
        add_action('ssd_add_simple_product_before_calculate_totals', [$this, 'send_update_user_role_when_add_on_added'], 10, 2);

        //@todo: implement the logic for remove item
    }

    public function send_update_user_role_when_add_on_added(\WC_Subscription $subscription, \WC_Product_Subscription $product)
    {
        $product_user_role = $product->get_meta(WPCSTenant::WPCS_PRODUCT_ROLE_META);
        $subscription_roles = $subscription->get_meta(WPCSTenant::WPCS_SUBSCRIPTION_USER_ROLES);

        if ($subscription_roles === '') {
            $subscription_roles = [];
        }

        $subscription_roles[] = $product_user_role;
        $subscription->update_meta_data(WPCSTenant::WPCS_SUBSCRIPTION_USER_ROLES, array_unique($subscription_roles));

        // @todo: Call endpoint on tenant to request update roles
    }
}