<?php

namespace MondayCloneClient\Features;

class RolesManager
{
    public function __construct()
    {
        add_action('init', [$this, 'activate_enabled_plugins']);
    }

    public function activate_enabled_plugins(): void
    {
        // @todo: we need to differ between tenant and version websites because owner can define plugins and so on but it shouldn't be disabled
        $roles_plugins = json_decode(file_get_contents(AdminRolesSettings::ROLES_FILE_PATH), true);
        $user_roles = ['level-2', 'level-3', 'level-10'];

        $enabled_plugins = [];
        foreach ($user_roles as $user_role) {
            if (!isset($roles_plugins[$user_role])) continue;
            $enabled_plugins = array_merge($enabled_plugins, $roles_plugins[$user_role]['plugins']);
        }

        $all_plugins = array_keys(get_plugins());
        $enabled_plugins = array_unique($enabled_plugins);
        $disabled_plugins = array_diff($all_plugins, $enabled_plugins);

        activate_plugins($enabled_plugins);

        $disabled_plugins = array_filter($disabled_plugins, fn($item) => $item !== PLUGIN_NAME);
        deactivate_plugins($disabled_plugins);
    }
}