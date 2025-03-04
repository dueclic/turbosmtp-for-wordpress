<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.dueclic.com
 * @since      4.9.0
 *
 * @package    Turbosmtp
 * @subpackage Turbosmtp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      4.9.0
 * @package    Turbosmtp
 * @subpackage Turbosmtp/includes
 * @author     dueclic <info@dueclic.com>
 */
class Turbosmtp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    4.9.0
	 * @access   protected
	 * @var      Turbosmtp_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    4.9.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    4.9.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * @var false|mixed|null
	 */
	private $auth_options;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    4.9.0
	 */
	public function __construct() {
		if ( defined( 'TURBOSMTP_VERSION' ) ) {
			$this->version = TURBOSMTP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'turbosmtp';

		$this->auth_options = get_option( 'ts_auth_options' );

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Turbosmtp_Loader. Orchestrates the hooks of the plugin.
	 * - Turbosmtp_i18n. Defines internationalization functionality.
	 * - Turbosmtp_Admin. Defines all hooks for the admin area.
	 * - Turbosmtp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    4.9.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Classes for turboSMTP APIs
		 */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-turbosmtp-api-base.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-turbosmtp-api.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-turbosmtp-messages-list-table.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-turbosmtp-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-turbosmtp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-turbosmtp-public.php';


		$this->loader = new Turbosmtp_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    4.9.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$auth_options = get_option( 'ts_auth_options' );

		$plugin_admin = new Turbosmtp_Admin(
			new Turbosmtp_Api(
				$this->auth_options['consumer_key'] ?? '',
				$this->auth_options['consumer_secret'] ?? ''
			),
			$this->get_plugin_name(),
			$this->get_version()
		);

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'turbosmtp_menu' );

		if ( ! turbosmtp_migration_has_done() ) {
			$this->loader->add_action( 'admin_notices', $plugin_admin, 'switch_to_api_keys_notice' );
			$this->loader->add_action( 'wp_ajax_turbosmtp_generate_api_keys', $plugin_admin, 'generate_api_keys' );
		} else {
			if ( turbosmtp_validapi() ) {
				$this->loader->add_action( 'wp_ajax_turbosmtp_get_stats_chart', $plugin_admin, 'get_stats_chart' );
				$this->loader->add_action('wp_ajax_turbosmtp_get_stats_history', $plugin_admin, 'get_stats_history');
				$this->loader->add_action('admin_post_turbosmtp_save_send_options', $plugin_admin, 'save_send_options');
				$this->loader->add_action( 'wp_ajax_turbosmtp_send_test_email', $plugin_admin,'send_test_email');
			}
		}

		$this->loader->add_action( 'admin_post_save_api_keys', $plugin_admin, 'save_api_keys' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    4.9.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Turbosmtp_Public(
			new Turbosmtp_Api(
				$this->auth_options['consumer_key'] ?? '',
				$this->auth_options['consumer_secret'] ?? ''
			),
			$this->get_plugin_name(), $this->get_version()
		);

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'turbosmtp_api_response', $plugin_public, 'turbosmtp_api_response', 10, 2 );

		if ( turbosmtp_validapi() ) {
			$this->loader->add_action('pre_wp_mail', $plugin_public, 'maybe_send_via_http', 10, 2 );
			$this->loader->add_action( 'phpmailer_init', $plugin_public,'phpmailer_init' );
		}

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    4.9.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     4.9.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Turbosmtp_Loader    Orchestrates the hooks of the plugin.
	 * @since     4.9.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     4.9.0
	 */
	public function get_version() {
		return $this->version;
	}

}
