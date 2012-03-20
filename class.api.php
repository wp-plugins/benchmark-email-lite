<?php

class benchmarkemaillite_api {
	static $client, $token, $listid, $connected, $campaignid, $timeout = 3;

	// Attempt To Connect To Benchmark Email
	function connect() {
		if (self::$connected) { return; }
		ini_set('default_socket_timeout', self::$timeout);
		require_once(ABSPATH . WPINC . '/class-IXR.php');
		self::$client = new IXR_Client(benchmarkemaillite::$apiurl);
		self::$connected = true;
	}

	// Executes Query with Time Tracking
	function query() {
		$disabled = get_transient('benchmark-email-lite_serverdown');
		if ($disabled) { return; }
		self::connect();
		$args = func_get_args();
		$time = time();
		self::$client->query($args);
		$time = (time() - $time);
		if ($time >= self::$timeout) {
			set_transient('benchmark-email-lite_serverdown', true, 300);
			return false;
		}
		return true;
	}

	// Register Vendor
	function handshake($tokens) {
		if (!$tokens || !is_array($tokens)) { return; }
		foreach ($tokens as $token) {
			if (!$token) { continue; }
			return self::query('UpdatePartner', $token, 'beautomated');
		}
	}

	// Lookup Lists For Account
	function lists() {
		if (self::query('listGet', self::$token, '', 1, 100, 'name', 'asc')) {
			$response = self::$client->getResponse();
			return isset($response['faultCode']) ? $response['faultCode'] : $response;
		}
		return false;
	}

	// Get Existing Subscriber Data
	function find($email) {
		if (self::query(
				'listGetContacts', self::$token, self::$listid, $email, 1, 100, 'name', 'asc'
			)
		) {
			if (self::$client->isError()) {
				return array(false, self::$client->getErrorMessage());
			}
			$data = self::$client->getResponse();
			return (
				is_array($data) && array_key_exists(0, $data)
				&& is_array($data[0]) && array_key_exists('id', $data[0])
			) ? $data[0]['id'] : false;
		}
		return false;
	}

	// Add or Update Subscriber
	function subscribe($data) {

		// Check for Subscription Preexistance
		if (!isset($data['Email'])) { return array(false, '[No Email Address]'); }
		$data['email'] = $data['Email'];
		$contactID = self::find($data['Email']);
		if (!is_numeric($contactID) && $contactID != false) { return $contactID; }

		// Doesn't Pre-Exist, Add New Subscription
		if (!is_numeric($contactID)) {
			if (!self::query('listAddContactsOptin', self::$token, self::$listid, array($data), '1')) {
				return array(false, '');
			}
			if (self::$client->isError()) {
				return array(false, self::$client->getErrorMessage());
			}
			return array(true, __('A verification email has been sent.', 'benchmark-email-lite'));
		}

		// Or Update Preexisting Subscription
		if (!self::query('listUpdateContactDetails', self::$token, self::$listid, $contactID, $data)) {
			return array(false, '');
		}
		if (self::$client->isError()) {
			return array(false, 'Error: [Updt] ' . self::$client->getErrorMessage());
		}
		return array(true, __('Successfully updated subscription.', 'benchmark-email-lite'));
	}

	// Create Email Campaign
	function campaign($title, $from, $subject, $body, $webpageVersion, $permissionMessage) {
		$data = array(
			'emailName' => $title,
			'toListID' => (int)self::$listid,
			'fromName' => $from,
			'subject' => $subject,
			'templateContent' => $body,
			'webpageVersion' => $webpageVersion,
			'permissionReminderMessage' => $permissionMessage,
		);

		// Check For Preexistance
		if (self::query('emailGet', self::$token, $title, '', 1, 1, '', '')) {
			$response = self::$client->getResponse();
			self::$campaignid = isset($response[0]['id']) ? $response[0]['id'] : false;
		}

		// Handle Preexisting And Sent Campaign
		if (self::$campaignid && $response[0]['status'] == 'Sent') {
			self::$campaignid = false;
			return __('preexists', 'benchmark-email-lite');
		}

		// Update Existing Campaign
		if (self::$campaignid) {
			$data['id'] = self::$campaignid;
			if (self::query('emailUpdate', self::$token, $data)) {
				$response = self::$client->getResponse();
				return self::$client->isError() ? false : __('updated', 'benchmark-email-lite');
			}
		}

		// Create New Campaign
		if (self::query('emailCreate', self::$token, $data)) {
			self::$campaignid = self::$client->getResponse();
			return self::$client->isError() ? self::$client->getErrorMessage() : __('created', 'benchmark-email-lite');
		}
		return false;
	}

	// Test Email Campaign
	function campaign_test($to) {
		if (!is_numeric(self::$campaignid) || !$to) { return; }
		if (self::query('emailSendTest', self::$token, self::$campaignid, $to)) {
			return self::$client->isError();
		}
		return false;
	}

	// Send Email Campaign
	function campaign_now() {
		if (!is_numeric(self::$campaignid)) { return; }
		if (self::query('emailSendNow', self::$token, self::$campaignid)) {
			return self::$client->isError();
		}
		return false;
	}

	// Schedule Email Campaign
	function campaign_later($when) {
		if (!is_numeric(self::$campaignid)) { return; }
		if (self::query('emailSchedule', self::$token, self::$campaignid, $when)) {
			return self::$client->isError();
		}
		return false;
	}

	// Get Email Campaigns
	function campaigns() {
		if (self::query('reportGet', self::$token, '', 1, 100, 'date', 'desc')) {
			return self::$client->getResponse();
		}
		return false;
	}

	// Get Email Campaign Report Summary
	function campaign_summary($id) {
		if (self::query('reportGetSummary', self::$token, $id)) {
			return self::$client->getResponse();
		}
		return false;
	}
}

?>