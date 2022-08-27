<?php

namespace MondayCloneClient\Features;

class PluginBootstrap
{
    public function __construct()
    {
        add_action('wpcs_tenant_created', [$this, 'remove_access_to_plugins']);
    }

    public function remove_access_to_plugins(): void
    {
        $role = get_role('administrator');
        $plugins_capabilities = ['activate_plugins', 'delete_plugins', 'install_plugins', 'update_plugins', 'edit_plugins', 'upload_plugins'];
        foreach ($plugins_capabilities as $capability) {
            $role->remove_cap($capability);
        }
    }
}
