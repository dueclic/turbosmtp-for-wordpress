<?php

class Turbosmtp_Exception extends Exception {

	/**
	 * @var array
	 */
	private $additionalData;

	protected function get_error_message($data, $code)
	{


		if (isset($data['message'])) {
			return "Turbo SMTP API error: {$data['message']}. (HTTP $code)";
		}

		return "Turbo SMTP API error (HTTP $code)";
	}

	public function __construct(
		$body = [],
		$code = 0,
		Exception $previous = null
	){
		$data = json_decode($body, true);
		$message = $this->get_error_message($data, $code);
		$this->additionalData = $data;
		parent::__construct($message, $code, $previous);
	}

	public function getAdditionalData() {
		return $this->additionalData;
	}

}

abstract class Turbosmtp_Api_Base {

	protected $api_url = "https://pro.api.serversmtp.com/api/v2";
	protected $api_smtp_endpoint_url = "https://api.turbo-smtp.com/api/v2/mail/send";
	/**
	 * @var string | null
	 */
	protected $consumer_key = null;
	/**
	 * @var string | null
	 */
	protected $consumer_secret = null;

	public function set($consumer_key, $consumer_secret){
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
	}

	public function __construct( $consumer_key, $consumer_secret ) {
		$this->set($consumer_key, $consumer_secret);
	}

	/**
	 * @throws Turbosmtp_Exception
	 * @throws Exception
	 */
	protected function request($endpoint, $data = [], $override_base_api_url = false, $method = 'POST', $override_headers = null) {
		$args = [
			'method' => $method,
			'user-agent' => turbosmtp_get_label(),
			'headers' => $override_headers ?: $this->get_headers(),
			'timeout' => 30,
		];

		if ($method === 'GET' && !empty($data)) {
			$endpoint .= '?' . http_build_query($data);
		} else if ($method === 'POST' && !empty($data)) {
			$args['body'] = json_encode($data);
		}
		if($override_base_api_url){
			$response = wp_remote_request($endpoint, $args);
		}else{
			$response = wp_remote_request($this->get_api_url() . $endpoint, $args);
		}

		if (is_wp_error($response)) {
			throw new \Exception($response->get_error_message(), 0);
		}

		$body = wp_remote_retrieve_body($response);
		$code = wp_remote_retrieve_response_code($response);

		do_action( "turbosmtp_api_response", $response, [
			'route'  => $endpoint,
			'args'   => $args,
			'method' => $method,
			'code' => $code
		] );

		if ($code < 200 || $code >= 300) {
			throw new \Turbosmtp_Exception(
				$body,
				$code
			);
		}
		return json_decode($body, true);
	}


	abstract protected function get_api_url();

	abstract protected function get_api_smtp_endpoint_url();


	abstract protected function get_headers();

	abstract public function get_user_config();

	abstract public function get_analytics($filters);

}
