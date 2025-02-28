<?php

class Turbosmtp_Api extends Turbosmtp_Api_Base {

	protected function get_api_url()
	{
		return defined('TURBOSMTP_API_URL') ? TURBOSMTP_API_URL : 'https://pro.api.serversmtp.com/api/v2/';
	}

	protected function get_headers() {
		return [
			'consumerKey' => $this->consumer_key,
			'consumerSecret' => $this->consumer_secret,
			'Content-Type' => 'application/json'
		];
	}

	/**
	 * @throws Exception
	 */
	public function send($data)
	{
		$endpoint = 'https://api.turbo-smtp.com/api/v2/mail/send';
		$payload = [
			'from' => $data['from_email'],
			'subject' => $data['subject'],
			'content' => $data['message'],
			'to' => implode(",", $data['to']),
			'reply_to' => $data['reply_to'] ?? $data['from_email'],
		];

		// Add CC recipients if any
		if (!empty($data['cc'])) {
			$payload['cc'] = implode(",", $data['cc']);
		}

		// Add BCC recipients if any
		if (!empty($data['bcc'])) {
			$payload['bcc'] = implode(",", $data['bcc']);
		}

		// Add attachments if any
		if (!empty($data['attachments'])) {
			$payload['attachments'] = array_map(function ($attachment) {
				return [
					'content' => $attachment['content'],
					'name' => $attachment['name'],
					'type' => $attachment['type']
				];
			}, $data['attachments']);
		}
		$response = $this->request($endpoint, $payload, true, 'POST');

		return [
			'message_id' => 'turboSMTP_' . uniqid(),
			'provider_response' => $response
		];
	}
	protected function get_error_message($body, $code)
	{
		$data = json_decode($body, true);

		if (isset($data['message'])) {
			return "Turbo SMTP API error: {$data['message']}. (HTTP $code)";
		}

		return "Turbo SMTP API error (HTTP $code)";
	}

	/**
	 * @throws Exception
	 */
	public function get_user_config()
	{
		$endpoint = 'user/config';
		return $this->request($endpoint, [], false, 'GET');
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */

	public function create_api_keys(
		$authkey
	){
		$endpoint = 'user/consumerKeys';

		return $this->request($endpoint, [
			'label' => turbosmtp_get_label()
		], false, "POST", [
			'Content-Type' => 'application/json',
			'Authorization' => $authkey
		]);
	}

	/**
	 * @return mixed
	 * @throws Exception
	 */

	public function authorize(
		$email,
		$password
	){
		return $this->request("authorize", [
			'email' => $email,
			'password' => $password
		], false, "POST", [
			'Content-Type' => 'application/json'
		]);
	}

	/**
	 * @throws Exception
	 */
	public function deauthorize(
		$authkey
	){
		return $this->request("deauthorize", [], false, "POST", [
			'Content-Type' => 'application/json',
			'Authorization' => $authkey
		]);
	}

	/**
	 * @throws Exception
	 */
	public function get_analytics($filters)
	{
		$endpoint = 'analytics';

		$data = $this->request($endpoint, [
			'from' => $filters['from'] ?? '',
			'to' => $filters['to'] ?? '',
			'page' => $filters['page'] ?? 1,
			'limit' => $filters['per_page'] ?? 5,
			'status' => ['SUCCESS', 'FAIL']
		], false, 'GET');

		return $data;
	}
}
