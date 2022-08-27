<?php

namespace MondayCloneClient\Api;

use MondayCloneClient\Core\DecryptionService;

class RolesController
{
    private DecryptionService $decryptionService;

    public function __construct(DecryptionService $decryptionService)
    {
        $this->decryptionService = $decryptionService;

        add_action('rest_api_init', [$this, 'register_rest_routes']);

        register_rest_route(API_V1_NAMESPACE, '/user-role-plan', [
            'methods' => 'POST',
            'callback' => [$this, 'verify_single_login'],
        ]);

        register_rest_route(API_V1_NAMESPACE, '/user-role-plan', array(
            'methods' => 'POST',
            'callback' => [$this, 'verify_single_login'],
        ));

    }

    public function add_user_role()
    {
    }

    public function remove_user_role()
    {

    }
}