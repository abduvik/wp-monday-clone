<?php

namespace MondayCloneHost\Features;

class PluginBootstrap
{
    const ROLES_WP_OPTION = 'monday-host-roles-options';

    public function __construct()
    {
        add_action('woocommerce_shop_loop', [$this, 'hide_addon_products_from_shop_page'], 10);
    }

    public function hide_addon_products_from_shop_page(): void
    {
        if (!is_shop()) return;

        // @todo: Implement code to hide and check it's visible on add-on subscriptions
    }

}