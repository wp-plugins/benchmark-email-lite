<?php

class benchmarkemaillite_api {
	static $token, $listid, $campaignid, $timeout = 5;

	// Executes Query with Time Tracking
	function query() {
		ini_set('default_socket_timeout', self::$timeout);
		require_once(ABSPATH . WPINC . '/class-IXR.php');

		// Skip This Request If Temporarily Disabled
		if ($disabled = get_transient('benchmark-email-lite_serverdown')) { return; }

		// Connect and Communicate
		$client = new IXR_Client(benchmarkemaillite::$apiurl, false, 443, self::$timeout);
		$time = time();
		$args = func_get_args();
		call_user_func_array(array($client, 'query'), $args);
		$response = $client->getResponse();

		// If Over Limit, Disable for Five Minutes And Produce Warning
		$time = (time() - $time);
		if ($time >= self::$timeout) {
			set_transient('benchmark-email-lite_serverdown', true, 300);
			set_transient(
				'benchmark-email-lite_errors',
				__('Error connecting to Benchmark Email API server. Connection throttled until', 'benchmark-email-lite')
				. ' ' . date('H:i:s', (current_time('timestamp') + 300))
				. ' ' . __('to prevent sluggish behavior.', 'benchmark-email-lite'),
				300
			);
			return false;
		}
		return $response;
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
		if ($response = self::query('listGet', self::$token, '', 1, 100, 'name', 'asc')) {
			return isset($response['faultCode']) ? $response['faultCode'] : $response;
		}
		return false;
	}

	// Get Existing Subscriber Data
	function find($email) {
		if ($response = self::query(
				'listGetContacts', self::$token, self::$listid, $email, 1, 100, 'name', 'asc'
			)
		) {
			return (
				is_array($response) && array_key_exists(0, $response)
				&& is_array($response[0]) && array_key_exists('id', $response[0])
			) ? $response[0]['id'] : false;
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
			return array(true, __('A verification email has been sent.', 'benchmark-email-lite'));
		}

		// Or Update Preexisting Subscription
		if (!self::query('listUpdateContactDetails', self::$token, self::$listid, $contactID, $data)) {
			return array(false, '');
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
		if ($response = self::query('emailGet', self::$token, $title, '', 1, 1, '', '')) {
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
			if ($response = self::query('emailUpdate', self::$token, $data)) {
				return (!$response) ? false : __('updated', 'benchmark-email-lite');
			}
		}

		// Create New Campaign
		if ($response = self::query('emailCreate', self::$token, $data)) {
			return (!$response) ? false : __('created', 'benchmark-email-lite');
		}
		return false;
	}

	// Test Email Campaign
	function campaign_test($to) {
		if (!is_numeric(self::$campaignid) || !$to) { return; }
		return self::query('emailSendTest', self::$token, self::$campaignid, $to);
	}

	// Send Email Campaign
	function campaign_now() {
		if (!is_numeric(self::$campaignid)) { return; }
		return self::query('emailSendNow', self::$token, self::$campaignid);
	}

	// Schedule Email Campaign
	function campaign_later($when) {
		if (!is_numeric(self::$campaignid)) { return; }
		return self::query('emailSchedule', self::$token, self::$campaignid, $when);
	}
}

?>