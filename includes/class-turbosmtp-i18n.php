<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.dueclic.com
 * @since      4.9.0
 *
 * @package    Turbosmtp
 * @subpackage Turbosmtp/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      4.9.0
 * @package    Turbosmtp
 * @subpackage Turbosmtp/includes
 * @author     dueclic <info@dueclic.com>
 */
class Turbosmtp_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    4.9.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'turbosmtp',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
