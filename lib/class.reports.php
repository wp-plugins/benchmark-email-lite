<?php

class benchmarkemaillite_reports {
	static $base_url = 'admin.php?page=benchmark-email-lite&amp;';

	// Generates Internal Data/URLs
	function meta() {
		return (object) array(
			'campaign' => isset( $_GET['campaign'] ) ? intval( $_GET['campaign'] ) : '',
			'tokenindex' => isset( $_GET['tokenindex'] ) ? intval( $_GET['tokenindex'] ) : 0,
			'show' => isset( $_GET['show'] ) ? esc_attr( $_GET['show'] ) : '',
		);
	}

	// Generates Internal URLs	
	function url( $args=array() ) {
		$meta = self::meta();
		foreach( $args as $key => $val ) {
			$meta->$key = $val;
		}
		return self::$base_url . http_build_query( $meta );
	}

	// Page Controller
	function show() {
		$options = get_option('benchmark-email-lite_group');
		$meta = self::meta();

		// Showing Campaign Listings
		if( !$meta->campaign ) { self::showListings(); }

		// Showing Requested Report
		else {

			// Set API To Selected Token
			benchmarkemaillite_api::$token = $options[1][$meta->tokenindex];

			// Show Detail Page
			if( $meta->show ) { self::showDetail( $meta->show ); }

			// Show Campaign Summary Page
			else { self::showCampaignSummary(); }
		}
	}

	// Show Email Campaign Listings
	function showListings() {
		$options = get_option('benchmark-email-lite_group');
		$url = self::url();

		// Print Header
		echo '<h3>' . __('Email Reports', 'benchmark-email-lite') . "</h3>\n";

		// Loop API Tokens
		foreach ($options[1] as $tokenindex => $key) {
			if (!$key) { continue; }

			// Set API To Iterated Token
			benchmarkemaillite_api::$token = $key;

			// Get Email Campaigns For Token
			$response = benchmarkemaillite_api::campaigns();

			// Error When Found 0 Email Campaigns
			if ( !$response ) {
				echo "
					<p>
						<strong>
							" . __('No results found for API key', 'benchmark-email-lite') . ":
							{$key}
						</strong>
					</p>
				";
				continue;
			}

			$emails = array();
			foreach ( $response as $email ) {

				// Only Show Sent Email Campaigns
				if ( $email['status'] != 'Sent' ) { continue; }

				// Append Data
				$email['toListName'] = isset( $email['toListName'] ) ? $email['toListName'] : '[none]';
				$email['report_url'] = self::url(
					array(
						'tokenindex' => $tokenindex,
						'campaign' => $email['id'],
					)
				);

				// Save Data For Template Reference
				$emails[] = $email;
				set_transient("benchmarkemaillite_{$email['id']}", $email);
			}
			require( dirname( __FILE__ ) . '/../views/reports.level1.html.php' );
		}

		// Handle No Sent Campaigns
		if( !$emails ) {
			echo '<p>' . __(
				'Data will start appearing only after your emails have been sent.',
				'benchmark-email-lite'
			) . '</p>';
		}
	}

	// Show Email Campaign Summary
	function showCampaignSummary() {
		$meta = self::meta();
		$url = self::$base_url . http_build_query( $meta );
		echo '
			<p>
				<a href="' . self::$base_url . '"
					title="' . __('Back to Email Reports', 'benchmark-email-lite') . '">
					' . __('Back to Email Reports', 'benchmark-email-lite') . '</a>
			</p>
		';
		$response = benchmarkemaillite_api::campaign_summary( $meta->campaign );
		$response['unopens'] =
			intval( $response['mailSent'] )
			- intval( $response['opens'] )
			- intval( $response['bounces'] );
		$response = array_merge( $response, get_transient( 'benchmarkemaillite_' . $meta->campaign ) );
		require( dirname( __FILE__ ) . '/../views/reports.level2.html.php' );
	}

	// Show Requested Detail
	function showDetail( $show ) {
		$meta = self::meta();
		$url = self::url( array( 'show' => '' ) );
		echo '<p><a href="' . $url . '" title="' . __('Back to Email Summary', 'benchmark-email-lite')
			. '">' . __('Back to Email Summary', 'benchmark-email-lite') . '</a></p>';
		switch ( $show ) {
			case 'clicks': self::showClicks(); break;
			case 'opens': self::showOpens(); break;
			case 'unopens': self::showUnopens(); break;
			case 'bounces': self::showBounces(); break;
			case 'unsubscribes': self::showUnsubscribes(); break;
			case 'forwards': self::showForwards(); break;
		}
	}

	// Header For Specific Reports
	function showReportHeading($title) {
		$meta = self::meta();
		$response = benchmarkemaillite_api::campaign_summary( $meta->campaign );
		echo "
			<h3>{$title}</h3>
			<p>
				<strong>" . __('Email name', 'benchmark-email-lite') . ":</strong> {$response['emailName']}
				<br /><strong>" . __('Subject', 'benchmark-email-lite') . ":</strong> {$response['subject']}
			</p>
		";
	}

	// Show Opens By Location Table
	function showLocations() {
		$meta = self::meta();
		$response = benchmarkemaillite_api::query(
			'reportGetOpenCountry', benchmarkemaillite_api::$token, (string)$meta->campaign
		);
		$data = array();
		foreach ($response as $row) {
			if (!$row['openCount']) { continue; }
			$data[] = array(
				__('Country', 'benchmark-email-lite') => ucwords(strtolower($row['country_name'])),
				__('Opens', 'benchmark-email-lite') => $row['openCount'],
			);
		}
		benchmarkemaillite::maketable($data);
	}

	/********************************
	 Specific Report Functions Follow
	 ********************************/

	function showClicks() {
		$meta = self::meta();
		self::showReportHeading(__('Email Clicks Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetClicks', benchmarkemaillite_api::$token, (string)$meta->campaign
		);
		$data = array();
		foreach ($response as $row) {
			$response2 = benchmarkemaillite_api::query(
				'reportGetClickEmails', benchmarkemaillite_api::$token, (string)$meta->campaign, $row['URL'], 1, 100, 'date', 'desc'
			);
			foreach ($response2 as $row2) {
				$data[] = array(
					__('Name', 'benchmark-email-lite') => $row2['name'],
					__('Email', 'benchmark-email-lite') => $row2['email'],
					__('URL', 'benchmark-email-lite') => $row['URL'],
					__('Date', 'benchmark-email-lite') => $row2['logdate'],
				);
			}
		}
		benchmarkemaillite::maketable($data);
	}
	function showOpens() {
		$meta = self::meta();
		self::showReportHeading(__('Email Opens Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetOpens', benchmarkemaillite_api::$token, (string)$meta->campaign, 1, 100, 'date', 'desc'
		);
		$data = array();
		foreach ($response as $row) {
			$data[] = array(
				__('Name', 'benchmark-email-lite') => $row['name'],
				__('Email', 'benchmark-email-lite') => $row['email'],
				__('Date', 'benchmark-email-lite') => $row['logdate'],
			);
		}
		benchmarkemaillite::maketable($data);
	}
	function showUnopens() {
		$meta = self::meta();
		self::showReportHeading(__('Email Unopened Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetUnopens', benchmarkemaillite_api::$token, (string)$meta->campaign, 1, 100, 'date', 'desc'
		);
		$data = array();
		foreach ($response as $row) {
			$data[] = array(
				__('Name', 'benchmark-email-lite') => $row['name'],
				__('Email', 'benchmark-email-lite') => $row['email'],
			);
		}
		benchmarkemaillite::maketable($data);
	}
	function showBounces() {
		$meta = self::meta();
		self::showReportHeading(__('Email Bounce Report', 'benchmark-email-lite'));
		$response1 = benchmarkemaillite_api::query(
			'reportGetHardBounces', benchmarkemaillite_api::$token, (string)$meta->campaign, 1, 100, 'date', 'desc'
		);
		$response2 = benchmarkemaillite_api::query(
			'reportGetSoftBounces', benchmarkemaillite_api::$token, (string)$meta->campaign, 1, 100, 'date', 'desc'
		);
		$response = array_merge($response1, $response2);
		$data = array();
		foreach ($response as $row) {
			$data[] = array(
				__('Name', 'benchmark-email-lite') => $row['name'],
				__('Email', 'benchmark-email-lite') => $row['email'],
				__('Bounce Type', 'benchmark-email-lite') => $row['type'],
			);
		}
		benchmarkemaillite::maketable($data);
	}
	function showUnsubscribes() {
		$meta = self::meta();
		self::showReportHeading(__('Email Unsubscribes Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetUnsubscribes', benchmarkemaillite_api::$token, (string)$meta->campaign, 1, 100, 'date', 'desc'
		);
		$data = array();
		foreach ($response as $row) {
			$data[] = array(
				__('Name', 'benchmark-email-lite') => $row['name'],
				__('Email', 'benchmark-email-lite') => $row['email'],
				__('Date', 'benchmark-email-lite') => $row['logdate'],
			);
		}
		benchmarkemaillite::maketable($data);
	}
	function showForwards() {
		$meta = self::meta();
		self::showReportHeading(__('Email Forwards Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetForwards', benchmarkemaillite_api::$token, (string)$meta->campaign, 1, 100, 'date', 'desc'
		);
		$data = array();
		foreach ($response as $row) {
			$data[] = array(
				__('Name', 'benchmark-email-lite') => $row['name'],
				__('Email', 'benchmark-email-lite') => $row['email'],
				__('Date', 'benchmark-email-lite') => $row['logdate'],
			);
		}
		benchmarkemaillite::maketable($data);
	}
}

?>
