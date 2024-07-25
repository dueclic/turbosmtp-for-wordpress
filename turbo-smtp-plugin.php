<?php
/**
 * Plugin Name: turboSMTP
 * Plugin URI: http://www.serversmtp.com/en/smtp-wordpress-configure
 * Description: Easily send emails from your WordPress blog using turboSMTP's services
 * Author: dueclic
 * Author URI: https://www.dueclic.com
 * Version: 4.5
 * Tested up to: 6.6
 * Text Domain: turbosmtp
 * Domain Path: /languages/
 * License: GPL v3
 */

__( 'Easily send emails from your WordPress blog using turboSMTP\'s services', 'turbosmtp' );

if ( function_exists( 'xdebug_disable' ) ) {
	xdebug_disable();
}

require_once( "class/TurboApiClient.php" );
require_once( "class/TurboApiStats.php" );
require_once( "class/TSAjaxListTable.php" );
require_once( "turbo-admin-sections.php" );
require_once( "ts_post.php" );

add_action( 'wp_ajax_nopriv_get_stats_chart', 'get_stats_chart' );
add_action( 'wp_ajax_get_stats_chart', 'get_stats_chart' );

add_action( 'wp_ajax_nopriv_get_opensclicks', 'get_opensclicks' );
add_action( 'wp_ajax_get_opensclicks', 'get_opensclicks' );

add_action( 'phpmailer_init', 'TSPHPMailer' );

function turbosmtp_load_plugin_textdomain() {

	$domain = 'turbosmtp';
	load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


}

add_action( 'plugins_loaded', 'turbosmtp_load_plugin_textdomain' );


add_action( 'init', 'post_admin_tsconfig' );

register_activation_hook( __FILE__, 'TSactivate' );
register_deactivation_hook( __FILE__, 'TSdisactivate' );

$ts_options      = get_option( "ts_auth_options" );
$ts_send_options = get_option( "ts_send_options" );

function turbosmtp_validapi() {
	global $ts_options;

	return isset( $ts_options['op_ts_validapi'] ) && $ts_options['op_ts_validapi'];
}

function turbosmtp_valid_hosts() {
	$hosts = array(
		"pro.eu.turbo-smtp.com" => __( "European", "turbostmp" ),
		"pro.turbo-smtp.com"    => __( "Not european", "turbostmp" ),
	);

	return $hosts;
}

/**
 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer
 *
 * @throws \PHPMailer\PHPMailer\Exception
 */

function TSPHPMailer( $phpmailer ) {
	global $ts_send_options;
	global $ts_options;
	if ( ! is_email( $ts_send_options["from"] ) || empty( $ts_send_options["host"] ) ) {
		return;
	}
	$phpmailer->isSMTP();
	$phpmailer->setFrom( $ts_send_options["from"], $ts_send_options["fromname"] );
	$phpmailer->Host       = $ts_send_options["host"];
	$phpmailer->SMTPAuth   = $ts_send_options["smtpauth"] == "yes";
	$phpmailer->SMTPSecure = $ts_send_options["smtpsecure"];
	//$phpmailer->SMTPDebug  = 2;
	$phpmailer->Port = $ts_send_options["port"];
	if ( $phpmailer->SMTPAuth ) {
		$phpmailer->Username = $ts_options["op_ts_email"];
		$phpmailer->Password = $ts_options["op_ts_password"];
	}
}

function action_wp_mail_failed( $wp_error ) {


	add_action( "admin_notices", function () use ( $wp_error ) {
		?>
        <div class="notice notice-error is-dismissible">
            <p>Error details</p>
            <code>
				<?php
				print json_encode( $wp_error, JSON_PRETTY_PRINT );
				?>
            </code>
        </div>
		<?php
	} );

	return error_log( print_r( $wp_error, true ), 3, dirname( __FILE__ ) . "/logs/debug.log" );
}

// add the action
add_action( 'wp_mail_failed', 'action_wp_mail_failed', 10, 1 );

function TSactivate() {
	$ts_send_options               = array();
	$ts_send_options["from"]       = "";
	$ts_send_options["fromname"]   = "";
	$ts_send_options["host"]       = "pro.turbo-smtp.com";
	$ts_send_options["smtpsecure"] = "ssl";
	$ts_send_options["port"]       = "465";
	$ts_send_options["smtpauth"]   = "yes";
	$ts_send_options["deactivate"] = "";

	/*
	 *
	 * For recover old plugin data
	 *
	 */

	if ( get_option( "turboSMTP_options" ) !== false ) {

		$ts_old_send_options = get_option( "turboSMTP_options" );
		$ts_old_auth_options = array(
			'op_ts_email'    => $ts_old_send_options['username'],
			'op_ts_password' => $ts_old_send_options['password'],
		);

		unset( $ts_old_send_options['username'] );
		unset( $ts_old_send_options['password'] );
		unset( $ts_old_send_options['deactivate'] );

		/**
		 *
		 * Test turboSMTP connection
		 *
		 */

		$api = new TurboApiStats( $ts_old_auth_options['op_ts_email'], $ts_old_auth_options['op_ts_password'] );

		if ( $api->isValid() ) {
			$ts_auth_options['op_ts_email']    = $ts_old_auth_options['op_ts_email'];
			$ts_auth_options['op_ts_password'] = $ts_old_auth_options['op_ts_password'];
			$ts_auth_options['op_ts_validapi'] = true;
			update_option( "ts_auth_options", $ts_auth_options );
		}

		add_option( "ts_send_options", $ts_old_send_options );

	} else {
		add_option( "ts_send_options", $ts_send_options );
	}

	delete_option( "turboSMTP_options" );
}

function TSdisactivate() {
	delete_option( "ts_auth_options" );
	delete_option( "ts_send_options" );
}


function _ajax_fetch_ts_history_callback() {

	$start_date = isset( $_REQUEST['begin'] ) ? sanitize_text_field( $_REQUEST['begin'] ) : null;
	$end_date   = isset( $_REQUEST['end'] ) ? sanitize_text_field( $_REQUEST['end'] ) : null;

	$wp_list_table = new TS_Ajax_List_Table( $start_date, $end_date, sanitize_text_field( $_REQUEST['filter'] ) );
	$wp_list_table->ajax_response();
}

add_action( 'wp_ajax__ajax_fetch_ts_history', '_ajax_fetch_ts_history_callback' );

function ts_footer_admin_text() {
	return __( 'This plugin is powered by',
			'turbosmtp' ) . ' <a href="https://www.dueclic.com/" target="_blank">dueclic</a>. <a class="social-foot" href="https://www.facebook.com/dueclic/"><span class="dashicons dashicons-facebook bg-fb"></span></a>';
}

function ts_footer_version() {
	return "";
}

function TSChange_Copyright() {
	add_filter( 'admin_footer_text', 'ts_footer_admin_text', 11 );
	add_filter( 'update_footer', 'ts_footer_version', 11 );
}

add_action( "ts_change_footer_copyright", "TSChange_Copyright" );


function ts_enqueue_scripts() {

	$screen            = get_current_screen();
	$turbo_admin_pages = array(
		"toplevel_page_ts",
		"toplevel_page_ts-dash",
		"turbosmtp_page_ts-stats",
		"turbosmtp_page_ts-logout"
	);

	if ( $screen != null && in_array( $screen->id, $turbo_admin_pages ) ) {
		wp_enqueue_style( 'ts-style-css', plugins_url( 'dist/css/turbosmtp.min.css', __FILE__ ), array(), '4.3' );

		if ( $screen->id === "turbosmtp_page_ts-stats" ) {

			wp_enqueue_style( 'ts-chart-css', plugins_url( 'dist/lib/chart.js/Chart.min.css', __FILE__ ), array(), '2.9.3' );

			wp_enqueue_style( 'ts-drange-css', plugins_url( 'dist/lib/daterangepicker/daterangepicker.css', __FILE__ ), array(), '3.0.5' );


			wp_enqueue_script( 'ts-chart', plugins_url( 'dist/lib/chart.js/Chart.bundle.min.js', __FILE__ ), array( 'jquery' ), '2.9.3', true );
			wp_enqueue_script( 'ts-drange-js', plugins_url( 'dist/lib/daterangepicker/daterangepicker.js', __FILE__ ), array(
				'jquery',
				'jquery-ui-core',
			), '3.0.5', true );

			wp_register_script( 'ts-stat-js', plugins_url( 'dist/js/turbosmtp.min.js', __FILE__ ), array(
				'jquery',
				'jquery-ui-core'
			), '4.3', true );
			wp_localize_script( 'ts-stat-js', 'ts', array(
				'chart_ajax_url' => admin_url( 'admin-ajax.php?action=get_stats_chart' ),
				'i18n'           => array(
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
			wp_enqueue_script( 'ts-stat-js' );
		}
	}
}

function ts_wordpress_libraries() {
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'moment' );
}

add_action( "admin_enqueue_scripts", "ts_wordpress_libraries", 5 );

add_action( "admin_enqueue_scripts", "ts_enqueue_scripts", 10 );

add_action( "admin_menu", "TurboSMTP_Menu" );
