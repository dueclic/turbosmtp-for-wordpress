<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.dueclic.com
 * @since      4.9.0
 *
 * @package    Turbosmtp
 * @subpackage Turbosmtp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Turbosmtp
 * @subpackage Turbosmtp/admin
 * @author     dueclic <info@dueclic.com>
 */
class Turbosmtp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.9.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    4.9.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * @var Turbosmtp_Api
	 */
	private $api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param Turbosmtp_Api $api
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    4.9.0
	 */
	public function __construct( $api, $plugin_name, $version ) {

		$this->api         = $api;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	public function migration_page() {

		$auth_options = get_option( "ts_auth_options" );

		require_once plugin_dir_path( TURBOSMTP_BASE_PATH ) . '/admin/partials/migration.php';
	}

	public function login_page() {
		require_once plugin_dir_path( TURBOSMTP_BASE_PATH ) . '/admin/partials/login.php';
	}

	public function configuration_page() {
		$auth_options = get_option( "ts_auth_options" );

		var_dump($auth_options);
	}

	public function stats_page() {
		$end   = date( 'Y-m-d' );
		$begin = strtotime( '-7 days', strtotime( $end ) );
		$begin = date( 'Y-m-d', $begin );

		$wp_list_table = new Turbosmtp_Messages_List_Table( $this->api, $begin, $end, 10, "all" );

		require_once plugin_dir_path( TURBOSMTP_BASE_PATH ) . '/admin/partials/stats.php';
	}


	public function show_credentials_page() {
		update_option("ts_show_credentials", false);
		$consumer_key = sanitize_text_field($_GET['consumer_key']);
		$consumer_secret = sanitize_text_field($_GET['consumer_secret']);
		if (empty($consumer_key) || empty($consumer_secret)) {
			wp_die(
				__('Invalid request', 'turbosmtp')
			);
		}
		require_once plugin_dir_path( TURBOSMTP_BASE_PATH ) . '/admin/partials/credentials.php';
	}

	public function save_api_keys() {

		if ( ! wp_verify_nonce( $_POST['turbosmtp_nonce'], 'turbosmtp_save_api_keys' ) ) {
			wp_redirect(
				add_query_arg( 'error', 'invalid_request', admin_url( 'admin.php?page=' . $this->plugin_name . '_migration' ) )
			);
			exit;
		}

		if (isset($_POST['save_api_keys'])) {

			$consumer_key    = sanitize_text_field( $_POST['consumer_key'] );
			$consumer_secret = sanitize_text_field( $_POST['consumer_secret'] );
			$referer_url = wp_get_referer();

			if ( empty( $consumer_key ) || empty( $consumer_secret ) ) {
				wp_redirect(
					add_query_arg( 'error', 'provide_api_keys', $referer_url )
				);
				exit;
			}

			try {
				$this->api->set( $consumer_key, $consumer_secret );
				$this->api->get_user_config();
			} catch ( Exception $e ) {

				$error_messages = apply_filters( 'turbosmtp_api_error_messages', [
					401 => 'invalid_api_keys'
				] );

				wp_redirect(
					add_query_arg( 'error', $error_messages[ $e->getCode() ] ?? 'retry_request',$referer_url )
				);
				exit;
			}

			$auth_options = get_option( "ts_auth_options" );
			$send_options = get_option( "ts_send_options" );

			update_option( "ts_send_options", array_merge(
				$send_options,
				[
					"email"    => $auth_options['op_ts_email'],
					"password" => $auth_options['op_ts_password'],
					"is_smtp"  => true
				]
			) );

			update_option( "ts_auth_options", [
				"consumer_key"    => $consumer_key,
				"consumer_secret" => $consumer_secret,
				"valid_api"       => true
			] );

			update_option( "ts_migration_done", true );

		} else if (isset($_POST['skip_setup'])) {
			update_option( "ts_auth_options", [] );
			update_option( "ts_send_options", [] );
			update_option( "ts_migration_done", true );
		}


		wp_redirect(
			admin_url( 'admin.php?page=' . $this->plugin_name . '_config' )
		);

		exit;

	}

	public function turbosmtp_menu() {

		$configuration_page_callback = [ $this, 'configuration_page' ];
		$configuration_page_slug     = "turbosmtp_config";

		if ( ! turbosmtp_migration_has_done() ) {
			$configuration_page_callback = [ $this, 'migration_page' ];
			$configuration_page_slug     = "turbosmtp_migration";
		} else if (!turbosmtp_validapi()){
			$configuration_page_callback = [ $this, 'login_page' ];
		}

		add_menu_page(
			__( "turboSMTP integration and configuration", "turbosmtp" ),
			"turboSMTP",
			"manage_options",
			$configuration_page_slug,
			$configuration_page_callback,
			plugins_url( 'admin/img/icon.png', TURBOSMTP_BASE_PATH ),
			80
		);

		add_submenu_page(
			"turbosmtp_config",
			__( "turboSMTP integration and configuration", "turbosmtp" ),
			__( "Configuration", "turbosmtp" ), "manage_options",
			$configuration_page_slug,
			$configuration_page_callback
		);

		if (
			turbosmtp_migration_has_done() &&
			turbosmtp_validapi()
		) {
			add_submenu_page( "turbosmtp_config",
				__( "Report", "turbosmtp" ),
				__( "Report", "turbosmtp" ),
				"manage_options",
				"turbosmtp_stats",
				[ $this, 'stats_page' ]
			);

			if (get_option("ts_show_credentials", false)) {
				add_submenu_page( null,
					__( "turboSMTP API Keys generated", "turbosmtp" ),
					__( "APIKeys", "turbosmtp" ),
					"manage_options",
					"turbosmtp_api_keys",
					[ $this, 'show_credentials_page' ]
				);
			}
		}

		/*if (turbosmtp_validapi()) {

			add_submenu_page("ts-dash", __("Report", "turbosmtp"), __("Report", "turbosmtp"), "manage_options", "ts-stats", "TSStats");
			add_submenu_page("ts-dash", __("Logout", "turbosmtp"), __("Logout", "turbosmtp"), "manage_options", "ts-logout", "TSLogout");

		}*/


	}

	public function generate_api_keys() {
		if ( ! wp_verify_nonce( $_POST['turbosmtp_nonce'], 'turbosmtp_generate_api_keys' ) ) {
			wp_send_json_error( [
				'message' => __( 'This request is not allowed.', 'turbosmtp' )
			] );
		}

		$auth_options = get_option( "ts_auth_options" );
		$send_options = get_option( "ts_send_options" );

		try {

			$authorize_body = $this->api->authorize(
				$auth_options['op_ts_email'],
				$auth_options['op_ts_password']
			);

			$authkey = $authorize_body['auth'];

			$api_keys = $this->api->create_api_keys(
				$authkey
			);

			update_option( "ts_send_options", array_merge(
				$send_options,
				[
					"email"    => $auth_options['op_ts_email'],
					"password" => $auth_options['op_ts_password'],
					"is_smtp"  => true
				]
			) );

			update_option( "ts_auth_options", [
				"consumer_key"    => $api_keys["consumerKey"],
				"consumer_secret" => $api_keys["consumerSecret"],
				"valid_api"       => true
			] );

			update_option( "ts_migration_done", true );

			update_option( "ts_show_credentials", true );

			$this->api->deauthorize( $authkey );

			wp_send_json_success( [
				"message"         => __( 'Migration was completed succesfully' ),
				"consumer_key"    => $api_keys['consumerKey'],
				"consumer_secret" => $api_keys['consumerSecret'],
				"next_url" => add_query_arg([
					"consumer_key"    => $api_keys["consumerKey"],
					"consumer_secret" => $api_keys["consumerSecret"]
				], admin_url( 'admin.php?page=' . $this->plugin_name . '_api_keys' ))
			] );

		} catch ( Exception $e ) {
			wp_send_json_error( [
				"message" => __( 'Request was wrong', 'turbosmtp' ),
				"details" => $e->getMessage()
			] );
		}

	}

	public function switch_to_api_keys_notice() {

		if ( isset( $_GET['page'] ) && $_GET['page'] === $this->plugin_name . '_migration' ) {
			return;
		}

		$message = sprintf(
			'<strong>%s</strong> <a href="%s">%s</a>.',
			esc_html__( 'turboSMTP requires a new configuration.', 'turbosmtp' ),
			esc_url( admin_url( 'admin.php?page=' . $this->plugin_name . '_migration' ) ),
			esc_html__( 'Click here to generate API Keys', 'turbosmtp' )
		);

		echo '<div class="notice notice-warning is-dismissible">
                <p>' . $message . '</p>
              </div>';


	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    4.9.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Turbosmtp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Turbosmtp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/turbosmtp-admin.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    4.9.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Turbosmtp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Turbosmtp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/turbosmtp.min.js', array( 'jquery' ), $this->version, false );

	}

}
