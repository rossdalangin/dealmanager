<?php
/**
 * Plugin Name:       Deals Manager
 * Plugin URI:        https://wordpresstitans.com/plugins/deals-manager/
 * Description:       A complete Deals & Lead Management System + Sales CRM built to help businesses organize and track their sales pipeline, contacts, and tasks seamlessly inside WordPress.
 * Version:           1.0.0
 * Author:            Ross Dalangin
 * Author URI:        https://wordpresstitans.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       deals-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This function is responsible for setting up the initial state of the plugin,
 * including creating custom user roles and scheduling cron events.
 *
 * @since 1.0.0
 */
function activate_deals_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deals-manager-roles.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deals-manager-cron.php';
	Deals_Manager_Roles::add_roles();
	Deals_Manager_Cron::schedule_events();
}

/**
 * The code that runs during plugin deactivation.
 * This function is responsible for cleaning up the plugin's state,
 * including removing custom user roles and unscheduling cron events.
 *
 * @since 1.0.0
 */
function deactivate_deals_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deals-manager-roles.php';
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deals-manager-cron.php';
	Deals_Manager_Roles::remove_roles();
	Deals_Manager_Cron::unschedule_events();
}

register_activation_hook( __FILE__, 'activate_deals_manager' );
register_deactivation_hook( __FILE__, 'deactivate_deals_manager' );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-deals-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_deals_manager() {

	$plugin = new Deals_Manager();
	$plugin->run();

}
run_deals_manager();
