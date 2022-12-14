<?php

namespace MondayCloneHost\Api;

use MondayCloneHost\Features\PluginBootstrap;
use WC_Order;
use MondayCloneHost\Core\EncryptionService;
use MondayCloneHost\Core\WPCSTenant;
use WP_REST_Request;

class SingleLogin
{
    private EncryptionService $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;

        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_rest_routes()
    {
        register_rest_route(PluginBootstrap::API_V1_NAMESPACE, '/tenant/single_login', array(
            'methods' => 'GET',
            'callback' => [$this, 'generate_single_login_link'],
            'permission_callback' => [$this, 'guard_generate_single_login_link']
        ));
    }

    public function guard_generate_single_login_link(WP_REST_Request $request)
    {

        $subscription_id = sanitize_text_field($request->get_param('subscription_id'));
        $subscription = new \WC_Subscription($subscription_id);
        $login_email = $request->get_param('email');
        $order = $subscription->get_parent();
        $order_email = $order->get_billing_email();

        return $login_email === $order_email;
    }

    public function generate_single_login_link(WP_REST_Request $request)
    {
        $subscription_id = $request->get_param('subscription_id');
        $email = $request->get_param('email');

        $domain = get_post_meta($subscription_id, WPCSTenant::WPCS_DOMAIN_NAME_META, true);
        $base_domain = get_post_meta($subscription_id, WPCSTenant::WPCS_BASE_DOMAIN_NAME_META, true);

        $domain = $domain ?: $base_domain;

        $private_key = get_post_meta($subscription_id, WPCSTenant::WPCS_TENANT_PRIVATE_KEY_META, true);

        $login_data = [
            'email' => $email
        ];

        $token = $this->encryptionService->encrypt($private_key, json_encode($login_data));
        $token_encoded = urlencode(base64_encode($token));
        $loginLink = 'https://' . $domain . "/wp-json/monday-client/v1/single_login/verify?token=" . $token_encoded;

        wp_redirect($loginLink);
        exit();
    }
}