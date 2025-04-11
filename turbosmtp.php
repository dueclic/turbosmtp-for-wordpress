<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.dueclic.com
 * @since             4.9.0
 * @package           Turbosmtp
 *
 * @wordpress-plugin
 * Plugin Name:       turboSMTP
 * Plugin URI:        https://www.serversmtp.com/en/smtp-wordpress-configure
 * Description:       Easily send emails from your WordPress blog using turboSMTP's services
 * Version:           4.9.2
 * Author:            turbosmtp
 * Author URI:        https://www.serversmtp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       turbosmtp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'common-api.php';

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TURBOSMTP_VERSION', '4.9.2' );
define( 'TURBOSMTP_BASE_PATH', __FILE__ );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-turbosmtp-activator.php
 */
function activate_turbosmtp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-turbosmtp-activator.php';
	Turbosmtp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-turbosmtp-deactivator.php
 */
function deactivate_turbosmtp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-turbosmtp-deactivator.php';
	Turbosmtp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_turbosmtp' );
register_deactivation_hook( __FILE__, 'deactivate_turbosmtp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-turbosmtp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    4.9.0
 */
function run_turbosmtp() {

	$plugin = new Turbosmtp();
	$plugin->run();

}

run_turbosmtp();
