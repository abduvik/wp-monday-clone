<?php

namespace MondayCloneHost\Api;

use MondayCloneHost\Core\HttpService;
use MondayCloneHost\Features\PluginBootstrap;
use PhpParser\Error;

class RolesController
{
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    public function register_rest_routes()
    {
        register_rest_route(API_V1_NAMESPACE, '/user-role-plan/update', [
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => [$this, 'update_user_roles_list'],
        ]);
    }

    public function update_user_roles_list(): \WP_REST_Response
    {
        try {
            $wpcs_version_server = "http://wordpress-client"; // @todo: to use wpcs version w/ production instead

            $http_service = new HttpService($wpcs_version_server);

            $response = $http_service->get('/wp-content/plugins/monday-client/data/roles.json');

            update_option(PluginBootstrap::ROLES_WP_OPTION, $response);

            return new \WP_REST_Response(true, 200);

        } catch (Error $error) {

        }
    }
}