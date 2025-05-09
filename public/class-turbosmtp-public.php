<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.dueclic.com
 * @since      4.9.0
 *
 * @package    Turbosmtp
 * @subpackage Turbosmtp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Turbosmtp
 * @subpackage Turbosmtp/public
 * @author     dueclic <info@dueclic.com>
 */
class Turbosmtp_Public {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    4.9.0
	 */
	public function __construct( $api, $plugin_name, $version ) {

		$this->api         = $api;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}


	/**
	 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer
	 *
	 * @throws \PHPMailer\PHPMailer\Exception
	 */

	function maybe_send_via_phpmailer(
		$phpmailer
	) {
		$send_options = get_option( "ts_send_options" );

		if (
			isset( $send_options['is_smtp'] ) &&
			(bool) $send_options['is_smtp']
		) {

			if ( ! is_email( $send_options["from"] ) || empty( $send_options["host"] ) ) {
				return;
			}

			$from = $phpmailer->From ?: $send_options['from'];
			$fromname = $phpmailer->FromName ?: $send_options['fromname'];

			$phpmailer->isSMTP();
			$phpmailer->setFrom( $from, $fromname );
			$phpmailer->Host       = $send_options["host"];
			$phpmailer->SMTPAuth   = 'yes';
			$phpmailer->SMTPSecure = $send_options["smtpsecure"];
			$phpmailer->Port       = $send_options["port"];
			$phpmailer->Username   = $send_options["email"];
			$phpmailer->Password   = defined( 'TURBOSMTP_SMTP_PASSWORD' ) ? TURBOSMTP_SMTP_PASSWORD : $send_options["password"];

		}
	}

	function maybe_send_via_http(
		$retval,
		$atts
	) {
		$send_options = get_option( "ts_send_options" );
		if ( ! isset( $send_options['is_smtp'] ) || $send_options['is_smtp'] ) {
			return $retval;
		}

		$data = turbosmtp_get_headers_data(
			$atts['headers']
		);

		$content_type = $data['content-type'];
		$from         = $data['from'] ?: $send_options['from'];
		$fromname     = $data['fromname'] ?: $send_options['fromname'];

		$mail_atts = [
			'to'          => $atts['to'],
			'from'        => $from,
			'fromname'    => $fromname,
			'subject'     => $atts['subject'],
			'message'     => $atts['message'],
			"headers"     => $data["headers"],
			"attachments" => $atts["attachments"],
		];

		if (!empty($data['bcc'])){
			$mail_atts['bcc'] = $data['bcc'];
		}

		if (!empty($data['cc'])){
			$mail_atts['cc'] = $data['cc'];
		}

		if ( 'text/html' == $content_type ) {
			$mail_atts['html']    = $mail_atts['message'];
			$mail_atts['message'] = wp_strip_all_tags( $mail_atts['message'] );
		}

		try {
			$mail_sent_response = $this->api->send( $mail_atts );
		} catch ( \Exception $e ) {
			$mail_atts['api_exception_code'] = $e->getCode();
			if ( $e instanceof Turbosmtp_Exception ) {
				$mail_atts['api_error_infos'] = $e->getAdditionalData();
			}
			do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_atts ) );

			return false;
		}

		do_action( 'wp_mail_succeeded', array_merge(
			$mail_atts,
			array(
				'api_response' => $mail_sent_response
			)
		) );

		return true;

	}

	public function turbosmtp_api_response( $response, $args ) {
		$code = (int) $args['code'];
		if ( $code === 401 && apply_filters( 'turbosmtp_disconnect_if_api_response_401', true ) ) {
			$auth_options              = get_option( "ts_auth_options" );
			$auth_options['valid_api'] = false;
			update_option( "ts_auth_options", $auth_options );
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/turbosmtp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/turbosmtp-public.js', array( 'jquery' ), $this->version, false );

	}

}
