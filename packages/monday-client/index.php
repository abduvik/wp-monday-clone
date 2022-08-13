<?php

require_once 'vendor/autoload.php';

/*
Plugin Name: Monday-Clone Client
Plugin URI: https://github.com/abduvik/wp-monday-clone
Description: This plugin is used to handle secure communication between tenant and Storefront.
Author: Abdu Tawfik
Version: 1.0.1
Author URI: https://www.abdu.dev
*/

use MondayCloneClient\Api\SingleLogin;
use MondayCloneClient\Core\DecryptionService;
use MondayCloneClient\Core\HttpService;
use MondayCloneClient\Features\SecureHostConnection;
use MondayCloneClient\Features\UiWPCSAdminTenantSettings;

define('MONDAY_MAIN_HOST_URL', get_option('monday_host_website_url'));
define('MONDAY_HOST_PUBLIC_KEYS', get_option('tenant_public_key'));


$httpService = new HttpService(MONDAY_MAIN_HOST_URL . '/wp-json/wpcs');
new SecureHostConnection($httpService);

$decryptionService = new DecryptionService();
new SingleLogin($decryptionService);
new UiWPCSAdminTenantSettings();