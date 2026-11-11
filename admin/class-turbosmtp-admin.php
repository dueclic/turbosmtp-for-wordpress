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

	public function login_page() {
		require_once plugin_dir_path( TURBOSMTP_BASE_PATH ) . '/admin/partials/login.php';
	}

	public function configuration_page() {

		try {
			$turbosmtp_hosts = turbosmtp_valid_hosts();
			$send_options    = get_option( "ts_send_options" );

			$user_config     = $this->api->get_user_config();

			if (!isset($send_options['host'])){
				$send_options['host'] = 'pro.turbo-smtp.com';
			}

			if (!isset($send_options['smtpsecure'])){
				$send_options['smtpsecure'] = 'ssl';
			}

			if (!isset($send_options['port'])){
				$send_options['port'] = 465;
			}

			if (!isset($send_options['port'])){
				$send_options['port'] = 465;
			}

			$current_user    = wp_get_current_user();
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

		add_action( 'wp_mail_failed', function ( $wp_error ) {
			wp_send_json_error( [
				'message' => __( 'Error sending email', 'turbosmtp' ),
				'error'   => $wp_error->get_error_message(),
				'data'    => $wp_error->get_error_data(),
			] );
		} );

		if ( ! wp_verify_nonce( $_POST['turbosmtp_send_test_email_nonce'], 'turbosmtp_send_test_email' ) ) {
			wp_send_json_error( [
				'message' => __( 'Invalid request', 'turbosmtp' )
			] );
		}

		$current_user = wp_get_current_user();

		$to = isset( $_POST['to'] ) ? sanitize_email( $_POST['to'] ) : $current_user->user_email;

		$subject = esc_html__( "Email sent with WordPress and turboSMTP", "turbosmtp" );
		$message = $this->get_test_email_html();

		$headers = array('Content-Type: text/html; charset=UTF-8');
		$sent_email = wp_mail( $to, $subject, $message, $headers );
		if ( ! $sent_email ) {
			wp_send_json_error( [ 'message' => __( 'Unknown error sending the test email', 'turbosmtp' ) ] );
		} else {
			wp_send_json_success( [ 'message' => __( 'Test email sent succesfully', 'turbosmtp' ) ] );
		}

		wp_die();

	}


	/**
	 * @return string HTML body of the test email.
	 */
	private function get_test_email_html() {
		$send_options = get_option( "ts_send_options", [] );

		if ( ! empty( $send_options['is_smtp'] ) ) {
			$method_label = sprintf(
				/* translators: 1: SMTP host, 2: SMTP port, 3: encryption (SSL or STARTTLS) */
				__( 'SMTP (%1$s, port %2$s, %3$s)', 'turbosmtp' ),
				$send_options['host'] ?? '',
				$send_options['port'] ?? '',
				'ssl' === ( $send_options['smtpsecure'] ?? '' ) ? 'SSL' : __( 'STARTTLS when available', 'turbosmtp' )
			);
		} else {
			$method_label = __( 'HTTP API', 'turbosmtp' );
		}

		$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$site_url  = home_url( '/' );
		$sender_name  = $send_options['fromname'] ?? '';
		$sender_email = $send_options['from'] ?? '';
		$sent_at      = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

		/**
		 * URL of the logo in the test email. It must be reachable by the
		 * recipient's mail client: on local or private sites the alt text
		 * is shown instead. Email clients do not render SVG, hence the PNG.
		 */
		$logo_url = apply_filters(
			'turbosmtp_test_email_logo_url',
			plugins_url( 'admin/img/ts-logo-email.png', TURBOSMTP_BASE_PATH )
		);

		ob_start();
		include plugin_dir_path( TURBOSMTP_BASE_PATH ) . 'admin/partials/test-email.php';

		return ob_get_clean();
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

		$ts_send_options  = array();
		$old_send_options = get_option( "ts_send_options", array() );

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

			if ( ! in_array( $ts_send_options["host"], array_keys( turbosmtp_valid_hosts() ) ) ) {
				wp_redirect(
					add_query_arg( [
						'error'       => 'invalid_smtp_server',
						'send_method' => $send_method
					], admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
				);
				exit;
			}

			$smtp_error = $this->check_smtp_credentials(
				$ts_send_options["host"],
				$port,
				$ts_send_options["smtpsecure"]
			);

			if ( null !== $smtp_error ) {
				wp_redirect(
					add_query_arg( [
						'error'       => $smtp_error,
						'send_method' => $send_method
					], admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
				);
				exit;
			}

			$ts_send_options["port"]     = $port;
			$ts_send_options["smtpauth"] = 'yes';
			$ts_send_options["is_smtp"]  = true;

		}

		if ( ! $ts_send_options["is_smtp"] ) {
			// Keep the SMTP settings, so switching back from the API does not reset them.
			foreach ( array( 'host', 'port', 'smtpsecure', 'smtpauth' ) as $key ) {
				if ( isset( $old_send_options[ $key ] ) ) {
					$ts_send_options[ $key ] = $old_send_options[ $key ];
				}
			}
		}

		update_option( "ts_send_options", $ts_send_options );

		wp_redirect(
			add_query_arg( 'success', 'config_saved', admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
		);
		exit;

	}


	/**
	 * Logs in to the turboSMTP SMTP server with the connected API key, the
	 * same way PHPMailer does when sending. This is the only way to check
	 * that the key has the SEND_SMTP permission: the REST API accepts a key
	 * with the APIS permission alone and does not expose a key's permissions.
	 *
	 * @param string $host
	 * @param int|string $port
	 * @param string $secure 'ssl' or '' (STARTTLS is used when offered, like PHPMailer's SMTPAutoTLS).
	 *
	 * @return string|null Error code for the configuration page, or null on success.
	 */
	private function check_smtp_credentials( $host, $port, $secure ) {

		if ( ! class_exists( '\PHPMailer\PHPMailer\SMTP' ) ) {
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
		}

		$credentials = turbosmtp_get_smtp_credentials();
		$timeout     = (int) apply_filters( 'turbosmtp_smtp_check_timeout', 15 );
		$helo        = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'localhost';

		$smtp          = new \PHPMailer\PHPMailer\SMTP();
		$smtp->Timeout = $timeout;

		try {
			if (
				! $smtp->connect( ( 'ssl' === $secure ? 'ssl://' : '' ) . $host, (int) $port, $timeout ) ||
				! $smtp->hello( $helo )
			) {
				return 'smtp_connection_failed';
			}

			if ( 'ssl' !== $secure && $smtp->getServerExt( 'STARTTLS' ) ) {
				if ( ! $smtp->startTLS() || ! $smtp->hello( $helo ) ) {
					return 'smtp_connection_failed';
				}
			}

			if ( ! $smtp->authenticate( $credentials['username'], $credentials['password'] ) ) {
				return 'invalid_smtp_credentials';
			}

			$smtp->quit();

			return null;
		} finally {
			$smtp->close();
		}

	}

	/**
	 * Register the shortcut for the settings area.
	 *
	 * @since    1.0.0
	 */
	public function settings_link( $links, $file ) {
		if ( is_network_admin() ) {
			$settings_url = network_admin_url( 'admin.php?page=turbosmtp_config' );
		} else {
			$settings_url = admin_url( 'admin.php?page=turbosmtp_config' );
		}

		$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'turbosmtp' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	public function save_api_keys() {

		if ( ! wp_verify_nonce( $_POST['turbosmtp_nonce'], 'turbosmtp_save_api_keys' ) ) {
			wp_redirect(
				add_query_arg( 'error', 'invalid_request', admin_url( 'admin.php?page=' . $this->plugin_name . '_config' ) )
			);
			wp_die();
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
				$user = $this->api->get_user_config();
			} catch ( Exception $e ) {

				$error_messages = apply_filters( 'turbosmtp_api_error_messages', [
					401 => 'invalid_api_keys'
				] );

				wp_redirect(
					add_query_arg( 'error', $error_messages[ $e->getCode() ] ?? 'retry_request', $referer_url )
				);
				exit;
			}

			$send_options = get_option( "ts_send_options", [] );

			update_option( "ts_send_options", array_merge(
				$send_options,
				[
					"is_smtp"  => apply_filters('turbosmtp_default_is_smtp', false),
					"fromname" => get_bloginfo(),
					"from" => get_bloginfo('admin_email')
				]
			) );

			update_option( "ts_auth_options", [
				"consumer_key"    => $consumer_key,
				"consumer_secret" => $consumer_secret,
				"valid_api"       => true
			] );

		}

		wp_redirect(
			admin_url( 'admin.php?page=' . $this->plugin_name . '_config' )
		);

		exit;

	}

	public function turbosmtp_menu() {

		$configuration_page_callback = [ $this, 'configuration_page' ];
		$configuration_page_slug     = "turbosmtp_config";

		if ( ! turbosmtp_validapi() ) {
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

		if ( turbosmtp_validapi() ) {
			add_submenu_page( "turbosmtp_config",
				__( "Report", "turbosmtp" ),
				__( "Report", "turbosmtp" ),
				"manage_options",
				"turbosmtp_stats",
				[ $this, 'stats_page' ]
			);
		}

		/*if (turbosmtp_validapi()) {

			add_submenu_page("ts-dash", __("Report", "turbosmtp"), __("Report", "turbosmtp"), "manage_options", "ts-stats", "TSStats");
			add_submenu_page("ts-dash", __("Logout", "turbosmtp"), __("Logout", "turbosmtp"), "manage_options", "ts-logout", "TSLogout");

		}*/


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

	public function disconnect_account() {
		check_ajax_referer( 'turbosmtp_disconnect_account', 'turbosmtp_disconnect_account_nonce' );

		delete_option( "ts_send_options" );

		delete_option( "ts_auth_options" );

		wp_send_json_success( [
			'message'      => __( 'Account was succesfully disconnected', 'turbosmtp' ),
			'redirect_url' => admin_url( 'admin.php?page=' . $this->plugin_name . '_config' )
		] );
	}

	public function get_stats_chart() {

		check_ajax_referer( 'turbosmtp_get_stats_history', 'turbosmtp_get_stats_history_nonce' );

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
				'admin' => $turbosmtp_debug_js ? 'admin/js/turbosmtp-admin.js' : 'admin/bundle/turbosmtp/turbosmtp-admin.min.js',
				'stats' => $turbosmtp_debug_js ? 'admin/js/turbosmtp-stats.js' : 'admin/bundle/turbosmtp/turbosmtp-stats.min.js'
			];


			wp_enqueue_script( $this->plugin_name . '-admin', plugins_url( $plugin_js['admin'], TURBOSMTP_BASE_PATH ), array(
				'jquery',
				'jquery-ui-core'
			), $this->version, true );

			wp_localize_script( $this->plugin_name . '-admin', 'ts', array(
				'i18n' => array(
					'test_email_send_loading'        => __( 'Send in progress...', 'turbosmtp' ),
					'connection_request_error'       => __( 'Connection request failed, please retry later.', 'turbosmtp' ),
					'disconnect_confirm'             => __( 'Are you sure you want disconnect?', 'turbosmtp' ),
					'show_chart'                     => __( 'Show chart', 'turbosmtp' ),
					'hide_chart'                     => __( 'Hide chart', 'turbosmtp' ),
					"queued"                         => __( "Queue", "turbosmtp" ),
					"delivered"                      => __( "Delivered", "turbosmtp" ),
					"bounce"                         => __( "Bounced", "turbosmtp" ),
					"opens"                          => __( "Opened", "turbosmtp" ),
					"clicks"                         => __( "Click", "turbosmtp" ),
					"unsubscribes"                   => __( "Unsubscribes", "turbosmtp" ),
					"drop"                           => __( "Dropped", "turbosmtp" ),
					"spam"                           => __( "Spam", "turbosmtp" ),
					"all"                            => __( "Total", "turbosmtp" ),
					"no_results"                     => __( "No results to show", "turbosmtp" ),
					"subject"                        => __( "Subject", "turbosmtp" ),
					"description_error"              => __( "Error description", "turbosmtp" ),
					"drp_preset"                     => array(
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
