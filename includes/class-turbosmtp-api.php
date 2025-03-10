<?php

class Turbosmtp_Api extends Turbosmtp_Api_Base {

	protected function get_api_url() {
		return defined( 'TURBOSMTP_API_URL' ) ? TURBOSMTP_API_URL : $this->api_url;
	}

	protected function get_api_smtp_endpoint_url() {
		return defined( 'TURBOSMTP_SMTP_API_URL' ) ? TURBOSMTP_SMTP_API_URL : $this->api_smtp_endpoint_url;
	}


	protected function get_headers() {
		return [
			'consumerKey'    => $this->consumer_key,
			'consumerSecret' => $this->consumer_secret,
			'Content-Type'   => 'application/json'
		];
	}

	/**
	 * @throws Exception
	 */
	public function send( $data ) {

		$payload = [
			'from'           => $data['fromname'] . ' <' . $data['from'] . '>',
			'subject'        => $data['subject'],
			'content'        => $data['message'],
			'to'             => turbosmtp_implode( ",", $data['to'] ),
			'reply_to'       => $data['reply_to'] ?? $data['from'],
			'custom_headers' => $data['headers']
		];

		if (isset($data['html'])){
			$payload['html_content'] = $data['html'];
		}

		// Add CC recipients if any
		if ( ! empty( $data['cc'] ) ) {
			$payload['cc'] = implode( ",", $data['cc'] );
		}

		// Add BCC recipients if any
		if ( ! empty( $data['bcc'] ) ) {
			$payload['bcc'] = implode( ",", $data['bcc'] );
		}

		// Add attachments if any
		if ( ! empty( $data['attachments'] ) ) {
			$payload['attachments'] = array_map( function ( $attachment ) {
				return [
					'content' => base64_encode( file_get_contents( $attachment ) ),
					'name'    => basename( $attachment ),
					'type'    => mime_content_type( $attachment )
				];
			}, $data['attachments'] );
		}

		apply_filters('turbosmtp_before_send_http_payload', $payload);

		$response = $this->request(
			$this->get_api_smtp_endpoint_url(),
			$payload,
			true,
			'POST' );

		return [
			'message_id'        => 'turboSMTP_' . uniqid(),
			'provider_response' => $response
		];
	}

	/**
	 * @throws Exception
	 */
	public function get_user_config() {
		$endpoint = '/user/config';

		return $this->request( $endpoint, [], false, 'GET' );
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */

	public function create_api_keys(
		$authkey
	) {
		$endpoint = '/user/consumerKeys';

		return $this->request( $endpoint, [
			'label' => turbosmtp_get_label()
		], false, "POST", [
			'Content-Type'  => 'application/json',
			'Authorization' => $authkey
		] );
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */

	public function authorize(
		$email,
		$password
	) {
		return $this->request( "/authorize", [
			'email'    => $email,
			'password' => $password
		], false, "POST", [
			'Content-Type' => 'application/json'
		] );
	}

	/**
	 * @throws Exception
	 */
	public function deauthorize(
		$authkey
	) {
		return $this->request( "/deauthorize", [], false, "POST", [
			'Content-Type'  => 'application/json',
			'Authorization' => $authkey
		] );
	}

	/**
	 * @throws Exception
	 */
	public function get_analytics( $filters ) {
		$endpoint = '/analytics';

		$status = "";

		if ( isset( $filters['status'] ) ) {
			if ( is_array( $filters['status'] ) ) {
				$status = implode( ",", $filters['status'] );
			} else {
				if ( $filters['status'] != "all" ) {
					$status = $filters['status'];
				}
			}
		}

		$params = [
			'int'       => 1,
			'orderby'   => $filters['orderby'] ?? 'send_time',
			'ordertype' => $filters['ordertype'] ?? 'desc',
			'grp'       => $filters['grp'] ?? 'day',
			'from'      => $filters['from'] ?? '',
			'to'        => $filters['to'] ?? '',
			'page'      => $filters['page'] ?? 1,
			'limit'     => $filters['limit'] ?? 5,
			'tz'        => wp_timezone_string(),
			'status'    => $status
		];

		return $this->request( $endpoint, $params, false, 'GET' );
	}
}
