<?php
abstract class Turbosmtp_Api_Base {

	protected $api_url = "https://pro.api.serversmtp.com/api/v2";
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
			throw new \Exception($response->get_error_message());
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
			throw new \Exception($this->get_error_message($body, $code), $code);
		}
		return json_decode($body, true);
	}


	abstract protected function get_api_url();

	abstract protected function get_headers();

	abstract protected function get_error_message($body, $code);

	abstract public function get_user_config();

	abstract public function get_analytics(array $filters);

}
