<?php

class benchmarkemaillite_reports {
	static $base_url = 'admin.php?page=benchmark-email-lite&amp;';

	// Generates Internal Data/URLs
	function meta() {
		return (object) array(
			'campaign' => isset( $_GET['campaign'] ) ? intval( $_GET['campaign'] ) : '',
			'tokenindex' => isset( $_GET['tokenindex'] ) ? intval( $_GET['tokenindex'] ) : 0,
			'show' => isset( $_GET['show'] ) ? strtolower( esc_attr( $_GET['show'] ) ) : '',
		);
	}

	// Generates Internal URLs	
	function url( $args=array() ) {
		$meta = self::meta();
		foreach( $args as $key => $val ) { $meta->$key = $val; }
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
		$options = get_option( 'benchmark-email-lite_group' );
		$url = self::url();
		$data = array();

		// Loop API Tokens
		foreach( $options[1] as $tokenindex => $key ) {
			if( ! $key ) { continue; }
			$data[$key] = array();

			// Get Email Campaigns For Token
			benchmarkemaillite_api::$token = $key;
			$response = benchmarkemaillite_api::campaigns();

			// Loop Email Campaigns For Token
			if( ! $response ) { continue; }
			foreach( $response as $email ) {

				// Only Show Sent Email Campaigns
				if ( $email['status'] != 'Sent' ) { continue; }

				// Append Data
				$email['toListName'] = isset( $email['toListName'] )
					? $email['toListName'] : '[none]';
				$email['report_url'] = self::url(
					array(
						'tokenindex' => $tokenindex,
						'campaign' => $email['id'],
					)
				);

				// Save Data For Template Reference
				$data[$key][] = $email['id'];
				set_transient( "benchmarkemaillite_{$email['id']}", $email );
			}
		}
		require( dirname( __FILE__ ) . '/../views/reports.level1.html.php' );
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
		$response = array_merge( $response, get_transient( "benchmarkemaillite_{$meta->campaign}" ) );
		set_transient( "benchmarkemaillite_{$meta->campaign}", $response );
		require( dirname( __FILE__ ) . '/../views/reports.level2.html.php' );
	}

	// Used For All Reports - Loops And Accumulates Page Content
	function reportQueryAllPages() {
		$args = func_get_args();
		$data = array();
		$run = true;
		$page = 1;
		while( $run ) {
			$response = call_user_func_array( array( 'benchmarkemaillite_api', 'query' ), $args );
			if( ! is_array( $response ) ) { break; }
			$run = ( sizeof( $response ) == 100 ) ? true : false;
			$data = array_merge( $data, $response );
			foreach( $args as $key => $val ) {
				if( $val === $page ) {
					$args[$key]++;
					$page++;
				}
			}
		}
		return $data;
	}

	// Show Requested Detail Report
	function showDetail( $show ) {
		$meta = self::meta();
		$data = array();
		switch ( $show ) {

			// Opens By Location Report
			case 'locations':
				$response = self::reportQueryAllPages(
					'reportGetOpenCountry',
					benchmarkemaillite_api::$token,
					(string) $meta->campaign
				);
				foreach( $response as $row ) {
					if( ! $row['openCount'] ) { continue; }
					$data[] = array(
						__( 'Country', 'benchmark-email-lite' ) => ucwords( strtolower( $row['country_name'] ) ),
						__( 'Opens', 'benchmark-email-lite' ) => $row['openCount'],
					);
				}
				benchmarkemaillite_display::maketable( $data );
				return;

			// Click Performance Report
			case 'clicks':
				$response = self::reportQueryAllPages(
					'reportGetClicks',
					benchmarkemaillite_api::$token,
					(string) $meta->campaign
				);
				foreach ($response as $row) {
					$link = self::url( array( 'show' => 'clicks_detail', 'url' => urlencode( $row['URL'] ) ) );
					$data[] = array(
						__( 'URL', 'benchmark-email-lite' ) => "<a href='{$link}'>{$row['URL']}</a>",
						__( 'Clicks', 'benchmark-email-lite' ) => $row['clicks'],
						__( 'Percent', 'benchmark-email-lite' ) => $row['percent'] . '%',
					);
				}
				benchmarkemaillite_display::maketable( $data );
				return;

			// Click Performance Sub Reports
			case 'clicks_detail':
				$title = __( 'Links Clicked Detail Report', 'benchmark-email-lite' );
				$instructions = __( 'Displays the subscribers who clicked on the following email link:', 'benchmark-email-lite' );
				$instructions .= '<br /><em>' . urldecode( $_GET['url'] ) . '</em>';
				$response = self::reportQueryAllPages(
					'reportGetClickEmails',
					benchmarkemaillite_api::$token,
					(string) $meta->campaign,
					$_GET['url'],
					1,
					100,
					'date',
					'desc'
				);
				foreach( $response as $row ) {
					$data[] = array(
						__( 'Name', 'benchmark-email-lite' ) => $row['name'],
						__( 'Email', 'benchmark-email-lite' ) => $row['email'],
						__( 'Date', 'benchmark-email-lite' ) => $row['logdate'],
					);
				}
				break;

			// Email Opened Report
			case 'opens':
				$title = __( 'Emails Opened Report', 'benchmark-email-lite' );
				$instructions = __( 'Displays the subscribers who opened the email in their email client.', 'benchmark-email-lite' );
				$response =self::reportQueryAllPages(
					'reportGetOpens',
					benchmarkemaillite_api::$token,
					(string) $meta->campaign,
					1,
					100,
					'date',
					'desc'
				);
				foreach( $response as $row ) {
					$data[] = array(
						__( 'Name', 'benchmark-email-lite' ) => $row['name'],
						__( 'Email', 'benchmark-email-lite' ) => $row['email'],
						__( 'Date', 'benchmark-email-lite' ) => $row['logdate'],
					);
				}
				break;

			// Email Unopened Report
			case 'unopens':
				$title = __( 'Emails Unopened Report', 'benchmark-email-lite' );
				$instructions = __( 'Displays the subscribers who never opened the email.', 'benchmark-email-lite' );
				$response =self::reportQueryAllPages(
					'reportGetUnopens',
					benchmarkemaillite_api::$token,
					(string) $meta->campaign,
					1,
					100,
					'date',
					'desc'
				);
				foreach( $response as $row ) {
					$data[] = array(
						__( 'Name', 'benchmark-email-lite' ) => $row['name'],
						__( 'Email', 'benchmark-email-lite' ) => $row['email'],
					);
				}
				break;

			// Email Bounced Report
			case 'bounces':
				$title = __( 'Emails Bounced Report', 'benchmark-email-lite' );
				$instructions = __( 'Displays the subscribers whose email service provider rejected the email.', 'benchmark-email-lite' );
				$response1 =self::reportQueryAllPages(
					'reportGetHardBounces',
					benchmarkemaillite_api::$token,
					(string) $meta->campaign,
					1,
					100,
					'date',
					'desc'
				);
				$response2 =self::reportQueryAllPages(
					'reportGetSoftBounces',
					benchmarkemaillite_api::$token,
					(string) $meta->campaign,
					1,
					100,
					'date',
					'desc'
				);
				$response = array_merge( $response1, $response2 );
				foreach( $response as $row ) {
					$data[] = array(
						__( 'Name', 'benchmark-email-lite' ) => $row['name'],
						__( 'Email', 'benchmark-email-lite' ) => $row['email'],
						__( 'Bounce Type', 'benchmark-email-lite' ) => $row['type'],
					);
				}
				break;

			// Email Unsubscribed Report
			case 'unsubscribes':
				$title = __( 'Emails Unsubscribed Report', 'benchmark-email-lite' );
				$instructions = __( 'Displays previous subscribers who unsubscribed from the list during this campaign.', 'benchmark-email-lite' );
				$response =self::reportQueryAllPages(
					'reportGetUnsubscribes',
					benchmarkemaillite_api::$token,
					(string) $meta->campaign,
					1,
					100,
					'date',
					'desc'
				);
				foreach( $response as $row ) {
					$data[] = array(
						__( 'Name', 'benchmark-email-lite' ) => $row['name'],
						__( 'Email', 'benchmark-email-lite' ) => $row['email'],
						__( 'Date', 'benchmark-email-lite' ) => $row['logdate'],
					);
				}
				break;

			// Emails Forwarded Report
			case 'forwards':
				$title = __( 'Emails Forwarded Report', 'benchmark-email-lite' );
				$instructions = __( 'Displays the subscribers who successfully forwarded the email to others.', 'benchmark-email-lite' );
				$response =self::reportQueryAllPages(
					'reportGetForwards',
					benchmarkemaillite_api::$token,
					(string) $meta->campaign,
					1,
					100,
					'date',
					'desc'
				);
				foreach( $response as $row ) {
					$data[] = array(
						__( 'Name', 'benchmark-email-lite' ) => $row['name'],
						__( 'Email', 'benchmark-email-lite' ) => $row['email'],
						__( 'Date', 'benchmark-email-lite' ) => $row['logdate'],
					);
				}
				break;
		}

		// Output Requested Report
		$url = self::url( array( 'show' => '' ) );
		$response = get_transient( "benchmarkemaillite_{$meta->campaign}" );
		require( dirname( __FILE__ ) . '/../views/reports.level3.html.php' );
	}
}

?>