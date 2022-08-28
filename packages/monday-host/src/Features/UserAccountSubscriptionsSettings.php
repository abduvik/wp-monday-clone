<?php

namespace MondayCloneHost\Features;

use Exception;
use WC_Order;
use MondayCloneHost\Core\WPCSService;
use MondayCloneHost\Core\WPCSTenant;

class UserAccountSubscriptionsSettings
{
    private WPCSService $wpcsService;

    public function __construct(WPCSService $wpcsService)
    {
        $this->wpcsService = $wpcsService;

        add_action('woocommerce_subscription_details_table', [$this, 'render_single_login'], 10, 1);
        add_action('woocommerce_subscription_details_table', [$this, 'render_edit_domain'], 10, 1);
        add_action('remove_tenant_old_domain', [$this, 'remove_tenant_old_domain'], 1, 2);
        add_filter('wcs_view_subscription_actions', [$this, 'remove_subscription_actions'], 10, 1);
    }

    public function render_single_login(\WC_Subscription $subscription)
    {
        $order = $subscription->get_parent();
        $email = $order->get_billing_email();
        $loginLink = '/wp-json/wpcs/v1/tenant/single_login?subscription_id=' . $subscription->get_id() . '&email=' . $email;

        echo "<a href='$loginLink' target='_blank' class='button'>Login as: $email <span class='dashicons dashicons-admin-network'></span></a>";
    }

    public function render_edit_domain(\WC_Subscription $subscription)
    {
        $this->handle_update_subscription_domain($subscription);
        $domain_name = get_post_meta($subscription->get_id(), WPCSTenant::WPCS_DOMAIN_NAME_META, true);
        $base_domain_name = get_post_meta($subscription->get_id(), WPCSTenant::WPCS_BASE_DOMAIN_NAME_META, true);

        $domain_name = $domain_name ?: $base_domain_name;

        echo '<h4>Website Details</h4>';
        echo "<form method='post' action=''>
                <p class='woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide'>
                    <label for='account_email'>Domain Name (optional)</label>
                    <input type='text' placeholder='example.com' class='woocommerce-Input woocommerce-Input--email input-text' name='domain_name' id='domain_name' value='$domain_name'>
	            </p>
	            <button class='button' type='submit'>Update</button>
	           </form><br /><br />";
        echo '<p>Before verifying a domain, make sure that its DNS contains the following settings.</p>
              <p>For the domain apex add A records with the following IPs as their values:</p>
<pre>
54.74.209.56
54.75.81.37
54.216.187.86
</pre>
              <p>If you are verifying a subdomain, create a CNAME record with the value:</p>
              <pre>public.eu1.wpcs.io</pre>';
        echo '<h4>Website Status</h4>';
    }

    public function handle_update_subscription_domain(\WC_Subscription $subscription)
    {
        if (!isset($_POST['domain_name'])) {
            return;
        }

        $domain = sanitize_text_field($_POST['domain_name']);

        $tenant_external_id = get_post_meta($subscription->get_id(), WPCSTenant::WPCS_TENANT_EXTERNAL_ID_META, true);
        $tenant_current_domain_name = get_post_meta($subscription->get_id(), WPCSTenant::WPCS_DOMAIN_NAME_META, true);

        if ($_POST['domain_name'] === $tenant_current_domain_name) {
            return;
        }

        try {
            $this->wpcsService->add_tenant_domain([
                'external_id' => $tenant_external_id,
                'domain_name' => $domain,
            ]);

            update_post_meta($subscription->get_id(), WPCSTenant::WPCS_DOMAIN_NAME_META, $domain);

            if ($tenant_current_domain_name) {
                wp_schedule_single_event(time() + 300, 'remove_tenant_old_domain', [$tenant_external_id, $tenant_current_domain_name]);
            }
        } catch (Exception $e) {
        }
    }

    public function remove_tenant_old_domain($external_id, $old_domain_name)
    {
        $this->wpcsService->delete_tenant_domain([
            'external_id' => $external_id,
            'old_domain_name' => $old_domain_name,
        ]);
    }

    public function remove_subscription_actions($actions)
    {
        if (isset($actions['resubscribe'])) {
            unset($actions['resubscribe']);
        }
        
        return $actions;
    }
}