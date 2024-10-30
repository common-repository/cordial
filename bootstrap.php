<?php
/**
 * Plugin Name: Cordial
 * Version: 0.2.2
 * Plugin URI: http://cordial-api.com
 * Description: Bring civility to your online discussions
 * Author: Lost Coast Web Solutions
 * Author URI: http://lostcoastweb.com
 * License: Apache 2.0
 * License URI: http://www.apache.org/licenses/
*/

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'CordialConstants.php';
require_once plugin_dir_path( __FILE__ ) . 'CordialWp.php';
require_once plugin_dir_path( __FILE__ ) . 'CordialSettings.php';
require_once plugin_dir_path( __FILE__ ) . 'CordialWpAdminManageComments.php';

/**
 * This file bootstraps the rest of the Coridal plugin.  Important files are:
 * 
 * CordialWp.php - Handles initial Cordial setup, manages REST endpoints, 
 *                 communication with Cordial-Api.com, and manages comment metadata
 * CordialSettings.php - Provides an interface for WP admins to manage Cordial on
 *                 their site.
 * CordialWpAdminManageComments.php - Adds additional functionality to the WP Admin
 *                  dashboard "Comments" page.  
 */

use Cordial\CordialConstants;
use Cordial\CordialWp;
use Cordial\CordialSettings;
use Cordial\CordialWpAdminManageComments;

//load plugin
$cordial_wp = CordialWp::get_instance();
$cordial_wp_settings = CordialSettings::get_instance();
$cordial_wp_admin_manage_comments = CordialWpAdminManageComments::get_instance();