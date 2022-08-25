<?php

require_once 'vendor/autoload.php';
require_once(ABSPATH . '/wp-admin/includes/plugin.php');

/*
Plugin Name: Monday-Clone Client
Plugin URI: https://github.com/abduvik/wp-monday-clone
Description: This plugin is used to handle secure communication between tenant and Storefront.
Author: Abdu Tawfik
Version: 1.0.1
Author URI: https://www.abdu.dev
*/

use MondayCloneClient\Api\SingleSignOnController;
use MondayCloneClient\Core\DecryptionService;
use MondayCloneClient\Core\HttpService;
use MondayCloneClient\Features\AdminRolesSettings;
use MondayCloneClient\Features\PluginBootstrap;
use MondayCloneClient\Features\RolesManager;
use MondayCloneClient\Features\SecureHostConnectionManager;
use MondayCloneClient\Features\AdminTenantSettings;


const PLUGIN_VERSION = '1.0.0';
define("PLUGIN_DIR_URI", plugin_dir_url(__FILE__));
define("PLUGIN_DIR", plugin_dir_path(__FILE__));

define('MONDAY_MAIN_HOST_URL', get_option('monday_host_website_url'));
define('MONDAY_HOST_PUBLIC_KEYS', get_option('tenant_public_key'));

// Plugin Boostrap
new PluginBootstrap();

// Controllers to list for APIs
$httpService = new HttpService(MONDAY_MAIN_HOST_URL . '/wp-json/wpcs');
$decryptionService = new DecryptionService();
new SingleSignOnController($decryptionService);

// Managers to list for Events
new SecureHostConnectionManager($httpService);
new RolesManager();

// UI
new AdminTenantSettings();
new AdminRolesSettings();
