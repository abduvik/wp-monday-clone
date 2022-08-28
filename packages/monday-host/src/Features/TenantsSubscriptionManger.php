<?php

namespace MondayCloneHost\Features;

use Exception;
use MondayCloneHost\Core\EncryptionService;
use MondayCloneHost\Core\WPCSService;
use MondayCloneHost\Core\WPCSTenant;
use WC_Order;

class TenantsSubscriptionManger
{
    private WPCSService $wpcsService;
    private EncryptionService $encryptionService;

    public function __construct(WPCSService $wpcsService, EncryptionService $encryptionService)
    {
        $this->wpcsService = $wpcsService;
        $this->encryptionService = $encryptionService;

        add_action('woocommerce_checkout_subscription_created', [$this, 'create_tenant_when_subscription_created'], 10, 2);

        // @todo: This can be exchanged for the demo
        add_action('woocommerce_subscription_status_cancelled', [$this, 'remove_tenant_when_subscription_expired']);
//        add_action('woocommerce_subscription_status_expired', [$this, 'remove_tenant_when_subscription_expired']);
    }

    /**
     * @throws Exception
     */
    public function create_tenant_when_subscription_created(\WC_Subscription $subscription, WC_Order $order)
    {
        try {
            $order_items = $order->get_items();
            $product = reset($order_items);
            $product_role = get_post_meta($product->get_product_id(), WPCSTenant::WPCS_PRODUCT_ROLE_META, true);
            $website_name = sanitize_text_field(get_post_meta($order->get_id(), WPCSTenant::WPCS_WEBSITE_NAME_META, true));
            $domain_name = sanitize_text_field(get_post_meta($order->get_id(), WPCSTenant::WPCS_DOMAIN_NAME_META, true));
            $password = wp_generate_password();

            $args = [
                'name' => $website_name,
                'tenant_name' => $order->get_formatted_billing_full_name(),
                'tenant_email' => $order->get_billing_email(),
                'tenant_password' => $password,
                'tenant_user_role' => 'administrator',
            ];

            if ($domain_name !== '') {
                $args['custom_domain_name'] = $domain_name;
            }

            $new_tenant = $this->wpcsService->create_tenant($args);
            $keys = $this->encryptionService->generate_key_pair();

            update_post_meta($subscription->get_id(), WPCSTenant::WPCS_TENANT_EXTERNAL_ID_META, $new_tenant->externalId);
            update_post_meta($subscription->get_id(), WPCSTenant::WPCS_TENANT_PUBLIC_KEY_META, $keys['public_key']);
            update_post_meta($subscription->get_id(), WPCSTenant::WPCS_DOMAIN_NAME_META, $domain_name);
            update_post_meta($subscription->get_id(), WPCSTenant::WPCS_BASE_DOMAIN_NAME_META, $new_tenant->baseDomain);
            update_post_meta($subscription->get_id(), WPCSTenant::WPCS_TENANT_PRIVATE_KEY_META, $keys['private_key']);
            update_post_meta($subscription->get_id(), WPCSTenant::WPCS_SUBSCRIPTION_USER_ROLES, [$product_role]);

            $this->send_created_email([
                'email' => $order->get_billing_email(),
                'password' => $password,
                'domain' => $domain_name !== '' ? $domain_name : $new_tenant->baseDomain
            ]);

        } catch (Exception $e) {
            throw new Exception('Failed to create tenant');
        }
    }

    public function send_created_email($args)
    {
        wp_mail($args['email'], 'Your website is being created', "
        <!doctype html>
        <html lang='en'>
        <body>
            <p>Hello,</p>
            <p>You can now login here to your new website</p>
            <p><strong>Admin Url</strong>: <a href='https://{$args['domain']}/wp-admin'>https://{$args['domain']}/wp-admin</a></p>
            <p><strong>Email</strong> : {$args['email']}</p>
            <p><strong>Password</strong> : {$args['password']}</p>
        </body>
        </html>
        ", ['Content-Type: text/html; charset=UTF-8']);
    }

    public function remove_tenant_when_subscription_expired(\WC_Subscription $subscription)
    {
        $tenant_external_id = get_post_meta($subscription->get_id(), WPCSTenant::WPCS_TENANT_EXTERNAL_ID_META, true);
        $this->wpcsService->delete_tenant([
            'external_id' => $tenant_external_id
        ]);
    }
}