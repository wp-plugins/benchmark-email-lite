<?php

class benchmarkemaillite_api {
	static $client, $token, $listid, $connected, $campaignid;

	// Attempt To Connect To Benchmark Email
	function connect() {
		if (self::$connected) { return; }
		require_once(ABSPATH . WPINC . '/class-IXR.php');
		self::$client = new IXR_Client(benchmarkemaillite::$apiurl);
		self::$connected = true;
	}

	// Register Vendor
	function handshake($tokens) {
		if (!$tokens || !is_array($tokens)) { return; }
		self::connect();
		foreach ($tokens as $token) {
			if (!$token) { continue; }
			self::$client->query('UpdatePartner', $token, 'beautomated');
		}
	}

	// Lookup Lists For Account
	function lists() {
		self::connect();
		self::$client->query('listGet', self::$token, '', 1, 100, 'name', 'asc');
		$response = self::$client->getResponse();
		return isset($response['faultCode']) ? $response['faultCode'] : $response;
	}

	// Get Existing Subscriber Data
	function find($email) {
		self::connect();
		self::$client->query(
			'listGetContacts', self::$token, self::$listid, $email, 1, 100, 'name', 'asc'
		);
		if (self::$client->isError()) {
			return array(false, self::$client->getErrorMessage());
		}
		$data = self::$client->getResponse();
		return (
			is_array($data) && array_key_exists(0, $data)
			&& is_array($data[0]) && array_key_exists('id', $data[0])
		) ? $data[0]['id'] : false;
	}

	// Add or Update Subscriber
	function subscribe($data) {

		// Check for Subscription Preexistance
		if (!isset($data['Email'])) { return array(false, '[No Email Address]'); }
		$data['email'] = $data['Email'];
		$contactID = self::find($data['Email']);
		if (!is_numeric($contactID) && $contactID != false) { return $contactID; }
		self::connect();

		// Doesn't Pre-Exist, Add New Subscription
		if (!is_numeric($contactID)) {
			self::$client->query('listAddContactsOptin', self::$token, self::$listid, array($data), '1');
			if (self::$client->isError()) {
				return array(false, self::$client->getErrorMessage());
			}
			return array(true, __('A verification email has been sent.', 'benchmark-email-lite'));
		}

		// Or Update Preexisting Subscription
		self::$client->query('listUpdateContactDetails', self::$token, self::$listid, $contactID, $data);
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
		self::connect();

		// Check For Preexistance
		self::$client->query('emailGet', self::$token, $title, '', 1, 1, '', '');
		$response = self::$client->getResponse();
		self::$campaignid = isset($response[0]['id']) ? $response[0]['id'] : false;

		// Handle Preexisting And Sent Campaign
		if (self::$campaignid && $response[0]['status'] == 'Sent') {
			self::$campaignid = false;
			return __('preexists', 'benchmark-email-lite');
		}

		// Update Existing Campaign
		if (self::$campaignid) {
			$data['id'] = self::$campaignid;
			self::$client->query('emailUpdate', self::$token, $data);
			$response = self::$client->getResponse();
			return self::$client->isError() ? false : __('updated', 'benchmark-email-lite');
		}

		// Create New Campaign
		self::$client->query('emailCreate', self::$token, $data);
		self::$campaignid = self::$client->getResponse();
		return self::$client->isError() ? self::$client->getErrorMessage() : __('created', 'benchmark-email-lite');
	}

	// Test Email Campaign
	function campaign_test($to) {
		if (!is_numeric(self::$campaignid) || !$to) { return; }
		self::connect();
		self::$client->query('emailSendTest', self::$token, self::$campaignid, $to);
		return self::$client->isError();
	}

	// Send Email Campaign
	function campaign_now() {
		if (!is_numeric(self::$campaignid)) { return; }
		self::connect();
		self::$client->query('emailSendNow', self::$token, self::$campaignid);
		return self::$client->isError();
	}

	// Schedule Email Campaign
	function campaign_later($when) {
		if (!is_numeric(self::$campaignid)) { return; }
		self::connect();
		self::$client->query('emailSchedule', self::$token, self::$campaignid, $when);
		return self::$client->isError();
	}
}

?>