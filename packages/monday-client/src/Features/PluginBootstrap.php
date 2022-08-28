<?php

namespace MondayCloneClient\Features;

class PluginBootstrap
{
    const TENANT_ROLES = 'WPCS_TENANT_ROLES';
    const EXTERNAL_ID = 'WPCS_TENANT_EXTERNAL_ID';
    const API_V1_NAMESPACE = 'monday-client/v1';
    const PLUGIN_NAME = 'monday-client/index.php';
    const PLUGIN_VERSION = '1.0.0';

    public function __construct()
    {
        add_action('wpcs_tenant_created', [$this, 'remove_access_to_plugins']);
    }

    public function remove_access_to_plugins($external_id): void
    {
        $role = get_role('administrator');
        $plugins_capabilities = ['activate_plugins', 'delete_plugins', 'install_plugins', 'update_plugins', 'edit_plugins', 'upload_plugins'];
        foreach ($plugins_capabilities as $capability) {
            $role->remove_cap($capability);
        }

        update_option(self::EXTERNAL_ID, $external_id);
    }
}
