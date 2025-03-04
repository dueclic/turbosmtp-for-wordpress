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

		try {
			$turbosmtp_hosts        = turbosmtp_valid_hosts();
			$send_options = get_option( "ts_send_options" );
			$user_config  = $this->api->get_user_config();
			$current_user = wp_get_current_user();
			require_once plugin_dir_path( TURBOSMTP_BASE_PATH ) . '/admin/partials/configuration.php';
		} catch ( Exception $e ) {
			wp_die(
				__( 'There was an error connecting to the API. Please retry later.', 'turbosmtp' )
			);
		}

	}

	public function stats_page() {

		try {

			$user_config = $this->api->get_user_config();

			if ( 'paid' === $user_config['account_type'] ) {

				$end   = date( 'Y-m-d' );
				$begin = strtotime( '-6 days', strtotime( $end ) );
				$begin = date( 'Y-m-d', $begin );

				$wp_list_table = new Turbosmtp_Messages_List_Table(
					$this->api,
					$begin,
					$end,
					apply_filters( 'turbosmtp_stats_per_page', 10 ),
					"all"
				);
				require_once plugin_dir_path( TURBOSMTP_BASE_PATH ) . '/admin/partials/stats.php';

			} else {
				require_once plugin_dir_path( TURBOSMTP_BASE_PATH ) . '/admin/partials/stats-free.php';
			}

		} catch ( Exception $e ) {
			wp_die(
				__( 'There was an error connecting to the API. Please retry later.', 'turbosmtp' )
			);
		}

	}

	public function send_test_email() {

		add_action('wp_mail_failed', function($wp_error) {
			wp_send_json_error([
				'message' => __('Error sending email', 'turbosmtp'),
				'error' => $wp_error->get_error_message(),
				'data' => $wp_error->get_error_data(),
			]);
		});

		if ( ! wp_verify_nonce( $_POST['turbosmtp_send_test_email_nonce'], 'turbosmtp_send_test_email' ) ) {
			wp_send_json_error([
				'message' => __('Invalid request', 'turbosmtp')
			]);
		}

		$current_user = wp_get_current_user();

		$to      = isset($_POST['ts_mail_to'] ) ? sanitize_email( $_POST['ts_mail_to'] ) : $current_user->user_email;

		$subject = esc_html__( "Email sent with WordPress and turboSMTP", "turbosmtp" );
		$message = esc_html__(  "If you read this email means that turboSMTP plugin is working properly.", "turbosmtp" );


        $sent_email = wp_mail( $to, $subject, $message );
        if (!$sent_email) {
	        wp_send_json_error(['message' =>  __('Unknown error sending the test email', 'turbosmtp')]);
        } else {
	        wp_send_json_success(['message' => __('Test email sent succesfully', 'turbosmtp')]);
        }

		wp_die();

	}


	public function save_send_options() {

		$send_method = sanitize_text_field( $_POST['ts_send_method'] );

		if ( ! wp_verify_nonce( $_POST['turbosmtp_nonce'], 'turbosmtp_save_send_options' ) ) {
			wp_redirect(
				add_query_arg( [
					'error'       => 'invalid_request',
					'send_method' => $send_method
				], admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
			);
			exit;
		}

		$ts_send_options = array();
        $old_send_options = get_option("ts_send_options");

		$ts_send_options["from"]     = sanitize_email( $_POST['ts_auth_email'] );
		$ts_send_options["fromname"] = sanitize_text_field( $_POST['ts_auth_email_from'] );

		$ts_send_options["is_smtp"] = 'smtp' === $send_method;

		if ( ! is_email( $ts_send_options['from'] ) ) {

			wp_redirect(
				add_query_arg( [
					'error'       => 'invalid_sender_email',
					'send_method' => $send_method
				], admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
			);
			exit;

		}

		if ( empty( $ts_send_options['fromname'] ) ) {

			wp_redirect(
				add_query_arg( [
					'error'       => 'sender_name_empty',
					'send_method' => $send_method
				], admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
			);
			exit;
		}

		if ( $ts_send_options["is_smtp"] ) {

			$port = sanitize_text_field( $_POST['ts_smtp_mailport'] );

			if ( $port == "" ) {
				$port = 25;
			}

			$ts_send_options["host"]       = sanitize_text_field( $_POST['ts_smtp_host'] );
			$ts_send_options["smtpsecure"] = sanitize_text_field( $_POST['ts_smtp_smtpsecure'] );

			$ts_send_options["email"]      = sanitize_text_field( $_POST['ts_smtp_email'] );

			if ( ! is_email( $ts_send_options['email'] ) ) {
				wp_redirect(
					add_query_arg( [
						'error'       => 'invalid_smtp_email',
						'send_method' => $send_method
					], admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
				);
				exit;
			}

			if ( isset( $_POST['ts_smtp_password'] ) ) {
				$ts_send_options["password"] = sanitize_text_field( $_POST['ts_smtp_password'] );
			} else if (isset($old_send_options['password'])) {
                $ts_send_options["password"] = $old_send_options['password'];
            }

			$ts_send_options["port"]     = $port;
			$ts_send_options["smtpauth"] = 'yes';
			$ts_send_options["is_smtp"]  = true;


			if ( ! in_array( $ts_send_options["host"], array_keys( turbosmtp_valid_hosts() ) ) ) {
				wp_redirect(
					add_query_arg( [
						'error'       => 'invalid_smtp_server',
						'send_method' => $send_method
					], admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
				);
				exit;
			}

		}

		update_option( "ts_send_options", $ts_send_options );

		wp_redirect(
			add_query_arg( 'success', 'config_saved', admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
		);

	}


	public function show_credentials_page() {
		update_option( "ts_show_credentials", false );
		$consumer_key    = sanitize_text_field( $_GET['consumer_key'] );
		$consumer_secret = sanitize_text_field( $_GET['consumer_secret'] );
		if ( empty( $consumer_key ) || empty( $consumer_secret ) ) {
			wp_die(
				__( 'Invalid request', 'turbosmtp' )
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

		if ( isset( $_POST['save_api_keys'] ) ) {

			$consumer_key    = sanitize_text_field( $_POST['consumer_key'] );
			$consumer_secret = sanitize_text_field( $_POST['consumer_secret'] );
			$referer_url     = wp_get_referer();

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
					add_query_arg( 'error', $error_messages[ $e->getCode() ] ?? 'retry_request', $referer_url )
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

		} else if ( isset( $_POST['skip_setup'] ) ) {
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
		} else if ( ! turbosmtp_validapi() ) {
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

			if ( get_option( "ts_show_credentials", false ) ) {
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
				"next_url"        => add_query_arg( [
					"consumer_key"    => $api_keys["consumerKey"],
					"consumer_secret" => $api_keys["consumerSecret"]
				], admin_url( 'admin.php?page=' . $this->plugin_name . '_api_keys' ) )
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

		if ( ! is_null( $screen = turbosmtp_is_admin_page() ) ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( TURBOSMTP_BASE_PATH ) . 'admin/css/turbosmtp-admin.min.css', array(), $this->version, 'all' );
			if ( $screen->id === "turbosmtp_page_turbosmtp_stats" ) {

				wp_enqueue_style( $this->plugin_name . '-chart-css', plugins_url( 'admin/bundle/chart.js/Chart.min.css', TURBOSMTP_BASE_PATH ), array(), '2.9.3' );

				wp_enqueue_style( $this->plugin_name . '-drange-css', plugins_url( 'admin/bundle/daterangepicker/daterangepicker.css', TURBOSMTP_BASE_PATH ), array(), '3.0.5' );

			}
		}
	}

	public function get_stats_history() {
		$start_date = isset( $_REQUEST['begin'] ) ? sanitize_text_field( $_REQUEST['begin'] ) : null;
		$end_date   = isset( $_REQUEST['end'] ) ? sanitize_text_field( $_REQUEST['end'] ) : null;
		$filter     = isset( $_REQUEST['filter'] ) ? sanitize_text_field( $_REQUEST['filter'] ) : 'all';


		$wp_list_table = new Turbosmtp_Messages_List_Table(
			$this->api,
			$start_date,
			$end_date,
			apply_filters( 'turbosmtp_stats_per_page', 10 ),
			$filter == 'all' ? '' : turbosmtp_get_status_by_filter( $filter )
		);

		$wp_list_table->ajax_response();
	}

	public function get_stats_chart() {

		$start_date = sanitize_text_field( $_POST['start_date'] );
		$end_date   = sanitize_text_field( $_POST['end_date'] );

		$analytics = [];

		try {
			$analytics = $this->api->get_analytics(
				[
					'from' => $start_date,
					'to'   => $end_date
				]
			);;
		} catch ( Exception $e ) {

			wp_send_json_error( [
				'message' => $e->getMessage()
			] );

		}
		wp_send_json_success( [
			'stats' => $analytics
		] );
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

		if ( ! is_null( $screen = turbosmtp_is_admin_page() ) ) {

			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'moment' );

			$turbosmtp_debug_js = defined( 'TURBOSMTP_DEBUG_JS' ) && (bool) TURBOSMTP_DEBUG_JS;

			$plugin_js = [
				'admin' => $turbosmtp_debug_js ? 'admin/js/turbosmtp-admin.js' : 'admin/bundle/turbosmtp-admin.min.js',
				'stats' => $turbosmtp_debug_js ? 'admin/js/turbosmtp-stats.js' : 'admin/bundle/turbosmtp-stats.min.js'
			];


			wp_enqueue_script( $this->plugin_name . '-admin', plugins_url( $plugin_js['admin'], TURBOSMTP_BASE_PATH ), array(
				'jquery',
				'jquery-ui-core'
			), $this->version, true );

			wp_localize_script( $this->plugin_name . '-admin', 'ts', array(
				'chart_ajax_url' => admin_url( 'admin-ajax.php?action=turbosmtp_get_stats_chart' ),
				'i18n'           => array(
					'api_key_generate_loading' => __( 'Generating...', 'turbosmtp' ),
					'api_key_generate_button' => __( 'Generating API Key', 'turbosmtp' ),
					'api_key_generate_unknown_error' => __('Unknown error', 'turbosmtp'),
					'api_key_copied_text' => __('Copied', 'turbosmtp'),
					'test_email_send_loading' => __('Send in progress...', 'turbosmtp'),
					'connection_request_error' => __('Connection request failed, please retry later.', 'turbosmtp'),
					'disconnect_confirm' => __('Are you sure you want disconnect?', 'turbosmtp'),
					"queued"            => __( "Queue", "turbosmtp" ),
					"delivered"         => __( "Delivered", "turbosmtp" ),
					"bounce"            => __( "Bounced", "turbosmtp" ),
					"opens"             => __( "Opened", "turbosmtp" ),
					"clicks"            => __( "Click", "turbosmtp" ),
					"unsubscribes"      => __( "Unsubscribes", "turbosmtp" ),
					"drop"              => __( "Dropped", "turbosmtp" ),
					"spam"              => __( "Spam", "turbosmtp" ),
					"all"               => __( "Total", "turbosmtp" ),
					"no_results"        => __( "No results to show", "turbosmtp" ),
					"subject"           => __( "Subject", "turbosmtp" ),
					"description_error" => __( "Error description", "turbosmtp" ),
					"drp_preset"        => array(
						'today'       => __( "Today", "turbosmtp" ),
						'yesterday'   => __( "Yesterday", "turbosmtp" ),
						'lastweek'    => __( "Last week", "turbosmtp" ),
						'lastmonth'   => __( "Last month", "turbosmtp" ),
						'thismonth'   => __( "This month", "turbosmtp" ),
						'last30days'  => __( "Last 30 days", "turbosmtp" ),
						'last7days'   => __( "Last 7 days", "turbosmtp" ),
						'customrange' => __( "Custom range", "turbosmtp" ),
						'prevmonth'   => __( "Previous month", "turbosmtp" ),
						'thisyear'    => __( "Current year", "turbosmtp" ),
						'prevyear'    => __( "Last year", "turbosmtp" ),
						'apply'       => __( "Confirm", "turbosmtp" ),
						'clear'       => __( "Clear", "turbosmtp" ),
						'cancel'      => __( "Cancel", "turbosmtp" ),
					),
				),
			) );

			if ( $screen->id === "turbosmtp_page_turbosmtp_stats" ) {

				wp_enqueue_script( $this->plugin_name . '-summarizer', plugins_url( 'admin/bundle/turbosmtp/turbosmtp-summarizer.min.js', TURBOSMTP_BASE_PATH ), array(
					'moment'
				), '1.0', true );

				wp_register_script( $this->plugin_name . '-stats', plugins_url( $plugin_js['stats'], TURBOSMTP_BASE_PATH ), array(
					$this->plugin_name . '-summarizer',
					'jquery',
					'jquery-ui-core'
				), $this->version, true );

				wp_enqueue_script( $this->plugin_name . '-stats' );

				wp_enqueue_script( $this->plugin_name . '-chart', plugins_url( 'admin/bundle/chart.js/Chart.bundle.min.js', TURBOSMTP_BASE_PATH ), array( 'jquery' ), '2.9.3', true );
				wp_enqueue_script( $this->plugin_name . '-drange-js', plugins_url( 'admin/bundle/daterangepicker/daterangepicker.js', TURBOSMTP_BASE_PATH ), array(
					'jquery',
					'jquery-ui-core',
				), '3.0.5', true );

			}

		}

	}

}
