<?php

class benchmarkemaillite_api {
	static $token, $listid, $campaignid, $apiurl = 'https://api.benchmarkemail.com/1.0/';

	// Executes Query with Time Tracking
	static function query() {
		$options = get_option( 'benchmark-email-lite_group' );
		$timeout = ( isset( $options[5] ) ) ? $options[5] : 10;
		ini_set( 'default_socket_timeout', $timeout );
		require_once( ABSPATH . WPINC . '/class-IXR.php' );

		// Skip This Request If Temporarily Disabled
		if ( $disabled = get_transient( 'benchmark-email-lite_serverdown' ) ) { return; }

		// Connect and Communicate
		$client = new IXR_Client( self::$apiurl, false, 443, $timeout );
		$time = time();
		$args = func_get_args();
		call_user_func_array( array( $client, 'query' ), $args );
		$response = $client->getResponse();

		// If Over Limit, Disable for Five Minutes And Produce Warning
		$time = ( time() - $time );
		if ( $time >= $timeout ) {
			set_transient( 'benchmark-email-lite_serverdown', true, 300 );
			set_transient(
				'benchmark-email-lite_error',
				__( 'Error connecting to Benchmark Email API server. Connection throttled until', 'benchmark-email-lite' )
				. ' ' . date( 'H:i:s', ( current_time('timestamp') + 300 ) )
				. ' ' . __( 'to prevent sluggish behavior.', 'benchmark-email-lite' )
				. ' ' . __( 'If this occurs frequently, try increasing your' , 'benchmark-email-lite' )
				. ' <a href="admin.php?page=benchmark-email-lite-settings">'
				. __( 'Connection Timeout setting.', 'benchmark-email-lite' ) . '</a>',
				300
			);
			return false;
		}
		return $response;
	}

	// Register Vendor
	static function handshake( $tokens ) {
		if ( ! $tokens || ! is_array( $tokens ) ) { return; }
		foreach ( $tokens as $token ) {
			if ( ! $token ) { continue; }
			return self::query( 'UpdatePartner', $token, 'beautomated' );
		}
	}

	// Lookup Lists For Account
	static function lists() {
		$response = self::query( 'listGet', self::$token, '', 1, 100, 'name', 'asc' );
		return isset( $response['faultCode'] ) ? $response['faultCode'] : $response;
	}

	// Get Existing Subscriber Data
	static function find( $email ) {
		$response = self::query( 'listGetContacts', self::$token, self::$listid, $email, 1, 100, 'name', 'asc' );
		return isset( $response[0]['id'] ) ? $response[0]['id'] : false;
	}

	// Add or Update Subscriber
	static function subscribe( $bmelist, $data ) {

		// Ensure Valid Email Address
		if( ! isset( $data['Email'] ) || ! is_email( $data['Email'] ) ) {
			return 'fail-email';
		}

		// Handle Communications Failure
		$response = self::lists();
		if( ! is_array( $response ) ) {

			// Send to Queue
			benchmarkemaillite_widget::queue_subscription( $bmelist, $data );
			return 'success-queue';
		}

		// Check for Subscription Preexistance
		$contactID = self::find( $data['Email'] );

		// Helper
		$data['email'] = $data['Email'];

		// Add New Subscription
		if ( ! is_numeric( $contactID ) ) {
			return self::query( 'listAddContactsOptin', self::$token, self::$listid, array( $data ), '1' )
				? 'success-add' : 'fail-add';
		}

		// Update Preexisting Subscription
		return self::query( 'listUpdateContactDetails', self::$token, self::$listid, $contactID, $data )
			? 'success-update' : 'fail-update';
	}

	// Create Email Campaign
	static function campaign( $title, $from, $subject, $body, $webpageVersion, $permissionMessage ) {
		$data = array(
			'emailName' => $title,
			'toListID' => (int) self::$listid,
			'fromName' => $from,
			'subject' => $subject,
			'templateContent' => $body,
			'webpageVersion' => $webpageVersion,
			'permissionReminderMessage' => $permissionMessage,
		);

		// Check For Preexistance
		if ( $response = self::query( 'emailGet', self::$token, $title, '', 1, 1, '', '' ) ) {
			self::$campaignid = isset( $response[0]['id'] ) ? $response[0]['id'] : false;
		}

		// Handle Preexisting And Sent Campaign
		if ( self::$campaignid && $response[0]['status'] == 'Sent' ) {
			self::$campaignid = false;
			return __( 'preexists', 'benchmark-email-lite' );
		}

		// Update Existing Campaign
		if ( self::$campaignid ) {
			$data['id'] = self::$campaignid;
			if ( $response = self::query( 'emailUpdate', self::$token, $data ) ) {
				return ( $response ) ? __( 'updated', 'benchmark-email-lite' ) : false;
			}
		}

		// Create New Campaign
		if ( $response = self::query( 'emailCreate', self::$token, $data ) ) {
			self::$campaignid = $response;
			return ( $response ) ? __( 'created', 'benchmark-email-lite' ) : false;
		}
		return false;
	}

	// Test Email Campaign
	static function campaign_test( $to ) {
		if ( ! is_numeric( self::$campaignid ) || !$to ) { return; }
		return self::query('emailSendTest', self::$token, self::$campaignid, $to);
	}

	// Send Email Campaign
	static function campaign_now() {
		if ( ! is_numeric( self::$campaignid ) ) { return; }
		return self::query( 'emailSendNow', self::$token, self::$campaignid );
	}

	// Schedule Email Campaign
	static function campaign_later( $when ) {
		if ( ! is_numeric( self::$campaignid ) ) { return; }
		return self::query( 'emailSchedule', self::$token, self::$campaignid, $when );
	}

	// Get Email Campaigns
	static function campaigns() {
		return self::query( 'reportGet', self::$token, '', 1, 25, 'date', 'desc' );
	}

	// Get Email Campaign Report Summary
	static function campaign_summary( $id ) {
		return self::query( 'reportGetSummary', self::$token, (string) $id );
	}
}

?>