<?php

namespace MondayCloneClient\Features;

class PluginBootstrap
{
    const TENANT_ROLES = 'WPCS_TENANT_ROLES';
    const EXTERNAL_ID = 'WPCS_TENANT_EXTERNAL_ID';

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
