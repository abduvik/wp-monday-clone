<?php

use MondayCloneHost\Api\RolesController;
use MondayCloneHost\Api\SingleLogin;
use MondayCloneHost\Api\TenantsAuthKeys;
use MondayCloneHost\Core\EncryptionService;
use MondayCloneHost\Core\HttpService;
use MondayCloneHost\Core\WPCSService;
use MondayCloneHost\Features\TenantsSubscription;
use MondayCloneHost\Features\UiAccountSubscriptionsSettings;
use MondayCloneHost\Features\UiWcTenantsCheckout;
use MondayCloneHost\Features\AdminWcProductRole;
use MondayCloneHost\Features\AdminWpcsSettings;

require_once 'vendor/autoload.php';

/**
 * @package MondayCloneHost
 * @version 1.0.0
 */
/*
Plugin Name: Monday-Clone Host
Plugin URI: https://github.com/abduvik/wp-monday-clone
Description: This plugin is used to create tenants on WPCS.io with support of WordPress, WooCommerce, WooCommerce Subscriptions and Self-service Dashboard for WooCommerce Subscriptions
Author: Abdu Tawfik
Version: 1.0.0
Author URI: https://www.abdu.dev
*/

// @todo: put constants under a class or something

const API_V1_NAMESPACE = 'monday-host/v1';

define('WPCS_API_REGION', get_option('wpcs_credentials_region_setting')); // Or eu1, depending on your region.
define('WPCS_API_KEY', get_option('wpcs_credentials_api_key_setting')); // The API Key you retrieved from the console
define('WPCS_API_SECRET', get_option('wpcs_credentials_api_secret_setting')); // The API Secret you retrieved from the console


// Controllers to list for APIs
$wpcs_http_service = new HttpService('https://api.' . WPCS_API_REGION . '.wpcs.io', WPCS_API_KEY . ":" . WPCS_API_SECRET);
$wpcsService = new WPCSService($wpcs_http_service);
$encryptionService = new EncryptionService();
new RolesController();

// Managers to list for Events

// UI
new TenantsAuthKeys();
new SingleLogin($encryptionService);
new TenantsSubscription($wpcsService, $encryptionService);
new AdminWpcsSettings();
new UiAccountSubscriptionsSettings($wpcsService);
new AdminWcProductRole();
new UiWcTenantsCheckout();
