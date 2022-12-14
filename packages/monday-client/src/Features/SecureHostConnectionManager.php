<?php

namespace MondayCloneClient\Features;

use MondayCloneClient\Core\HttpService;

class SecureHostConnectionManager
{
    public const TENANT_PUBLIC_KEY = 'TENANT_PUBLIC_KEY';

    public HttpService $httpService;

    public function __construct(HttpService $httpService)
    {
        $this->httpService = $httpService;
        add_action('wpcs_tenant_created', [$this, 'get_tenant_public_id']);
    }

    public function get_tenant_public_id($external_id)
    {
        $response = $this->httpService->get('/tenant/public_keys?external_id=' . $external_id);

        $public_key = $response->public_key;

        update_option(static::TENANT_PUBLIC_KEY, $public_key);
    }
}
