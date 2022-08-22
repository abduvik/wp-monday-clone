<?php

namespace MondayCloneClient\Features;

use function PHPUnit\Framework\fileExists;

class AdminRolesSettings
{
    const ROLES_FILE_PATH = PLUGIN_DIR . 'data/roles.json';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_roles_page'], 11);
        add_action('admin_enqueue_scripts', [$this, 'add_admin_settings_styles']);
        add_action('admin_post_monday_add_new_role', [$this, 'add_new_role'], 10, 3);
        add_action('admin_post_save_roles', [$this, 'save_roles'], 10, 3);
    }

    public function add_admin_settings_styles()
    {
        wp_enqueue_style('wpcs-admin-styles', PLUGIN_DIR_URI . '/assets/style.css', null, PLUGIN_VERSION);
        wp_enqueue_script('wpcs-admin-scripts', PLUGIN_DIR_URI . '/assets/scripts.js', null, PLUGIN_VERSION);
    }

    public function add_roles_page()
    {
        add_submenu_page(
            'wpcs-admin-tenant',
            'Roles',
            'Roles',
            'manage_options',
            'wpcs-plugins-roles',
            [$this, 'render_roles_page'],
            10
        );
    }

    public function render_roles_page()
    {
        global $wp_roles;
        $wp_roles = $wp_roles->roles;
        $wp_plugins = get_plugins();
        ?>
        <h1>Roles</h1>

        <form action="<?= admin_url('admin-post.php') ?>" method="post">
            <? wp_nonce_field('monday_add_new_role', 'add_new_role_nonce'); ?>
            <input type="hidden" name="action" value="monday_add_new_role">

            <input name="role" type='text' placeholder='New role name'/>
            <button type="submit" class='button-secondary'>Add new role</button>
        </form>

        <form action="<?= admin_url('admin-post.php') ?>" method="post" id="manage_plugins_roles">
            <? wp_nonce_field('save_roles', 'save_roles_nonce'); ?>
            <input type="hidden" name="action" value="save_roles">

            <div class="wpcs roles-container">
                <?php foreach ($wp_roles as $role_name => $role): ?>
                    <div id='wpcs-role-<?= $role_name ?>' class='wpcs single-role-container'>
                        <input type='hidden' name='delete_roles[<?= $role_name ?>]' value="0"/>
                        <h2><?= $role['name'] ?></h2>
                        <ul>
                            <?php foreach ($wp_plugins as $plugin_name => $plugin) : ?>
                                <li>
                                    <input
                                            type='checkbox'
                                            name='roles[<?= $role_name ?>][<?= $plugin_name ?>]'/>
                                    <?= $plugin['Name'] ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class='button-secondary wpcs-mark-role-delete'
                                data-target-role='<?= $role_name ?>'>Mark
                            Delete Role
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type='submit' class='button-primary'>Save</button>
        </form>

        <?php
    }

    public function add_new_role()
    {
        if (!wp_verify_nonce($_POST['add_new_role_nonce'], 'monday_add_new_role')) return;

        $data = [];

        if (!file_exists(PLUGIN_DIR . 'data')) {
            mkdir(PLUGIN_DIR . 'data', 0755, true);
        }

        if (fileExists(PLUGIN_DIR . 'data/roles.json')) {

        }

        // Check if role exists

        $data[] = [
            'role' => sanitize_text_field($_POST['role']),
            'plugins' => []
        ];

        $encoded_data = json_encode($data);

        file_put_contents(self::ROLES_FILE_PATH, $encoded_data);

        echo '<pre>';
        print_r($encoded_data);
        echo '</pre>';

        exit();
    }

    public function save_roles()
    {
        if (!wp_verify_nonce($_POST['save_roles_nonce'], 'save_roles')) return;


//        if (fileExists(PLUGIN_DIR . 'data/roles.json')) {
//        }

//        echo PLUGIN_DIR;

//        $role_file_content =
//
        echo '<pre>';
        print_r($_POST);
        echo '</pre>';

//        exit();
    }
}