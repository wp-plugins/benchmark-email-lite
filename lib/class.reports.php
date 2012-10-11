<?php

class benchmarkemaillite_reports {
	static $url = 'admin.php?page=benchmark-email-lite';
	static $campaign = false;

	// Show a Report
	function show() {
		$options = get_option('benchmark-email-lite_group');

		// Showing Requested Report
		if( isset( $_GET['tokenindex'] ) && isset( $_GET['campaign'] ) ) {
			$tokenindex = intval($_GET['tokenindex']);
			benchmarkemaillite_api::$token = $options[1][$tokenindex];
			self::$campaign = (string)intval($_GET['campaign']);
			$url = self::$url . '&amp;campaign=' . self::$campaign . "&amp;tokenindex={$tokenindex}&amp;show=";
			$show = isset($_GET['show']) ? strtolower(esc_attr($_GET['show'])) : false;
			$show = isset($_POST['show']) ? strtolower(esc_attr($_POST['show'])) : $show;

			// Show Detail Page
			if( $show ) {
				echo '<p><a href="' . $url . '" title="' . __('Back to Email Summary', 'benchmark-email-lite')
					. '">' . __('Back to Email Summary', 'benchmark-email-lite') . '</a></p>';
				switch ($show) {
					case 'clicks': self::showClicks(); break;
					case 'opens': self::showOpens(); break;
					case 'unopens': self::showUnopens(); break;
					case 'bounces': self::showBounces(); break;
					case 'unsubscribes': self::showUnsubscribes(); break;
					case 'forwards': self::showForwards(); break;
				}
			}

			// Show Campaign Summary Page
			else {
				echo '
					<p>
						<a href="' . self::$url . '"
							title="' . __('Back to Email Reports', 'benchmark-email-lite') . '">
							' . __('Back to Email Reports', 'benchmark-email-lite') . '</a>
					</p>
				';
				$response = benchmarkemaillite_api::campaign_summary(self::$campaign);
				$response['unopens'] = intval($response['mailSent']) - intval($response['opens']) - intval($response['bounces']);
				$response = array_merge($response, get_transient('benchmarkemaillite_' . self::$campaign));
				require( dirname( __FILE__ ) . '/../views/reports.detail.html.php');
			}
		}

		// Showing Campaign Listings
		else {
			foreach ($options[1] as $tokenindex => $key) {
				$emails = array();
				if (!$key) { continue; }
				benchmarkemaillite_api::$token = $key;
				$response = benchmarkemaillite_api::campaigns();
				if (sizeof($response) > 0) {
					foreach ($response as $email) {
						if ($email['status'] != 'Sent') { continue; }
						$email['toListName'] = isset($email['toListName']) ? $email['toListName'] : '[none]';
						$emails[] = $email;
						set_transient("benchmarkemaillite_{$email['id']}", $email);
					}
					require( dirname( __FILE__ ) . '/../views/reports.overview.html.php');
					continue;
				}
			}

			// Handle No Sent Campaigns
			if( !$emails ) {
				echo '<p>' . __(
					'Data will start appearing only after your emails have been sent.',
					'benchmark-email-lite'
				) . '</p>';
			}
		}
	}

	// Header For Specific Reports
	function showReportHeading($title) {
		$response = benchmarkemaillite_api::campaign_summary(self::$campaign);
		echo "
			<h3>{$title}</h3>
			<p>
				<strong>" . __('Email name', 'benchmark-email-lite') . ":</strong> {$response['emailName']}
				<br /><strong>" . __('Subject', 'benchmark-email-lite') . ":</strong> {$response['subject']}
			</p>
		";
	}

	/********************************
	 Specific Report Functions Follow
	 ********************************/

	function showLocations() {
		$response = benchmarkemaillite_api::query(
			'reportGetOpenCountry', benchmarkemaillite_api::$token, self::$campaign
		);
		echo '<h3>' . __('Opens by Location', 'benchmark-email-lite') . '</h3>';
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
	function showClicks() {
		self::showReportHeading(__('Email Clicks Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetClicks', benchmarkemaillite_api::$token, self::$campaign
		);
		$data = array();
		foreach ($response as $row) {
			$response2 = benchmarkemaillite_api::query(
				'reportGetClickEmails', benchmarkemaillite_api::$token, self::$campaign, $row['URL'], 1, 100, 'date', 'desc'
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
		self::showReportHeading(__('Email Opens Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetOpens', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
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
		self::showReportHeading(__('Email Unopened Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetUnopens', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
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
		self::showReportHeading(__('Email Bounce Report', 'benchmark-email-lite'));
		$response1 = benchmarkemaillite_api::query(
			'reportGetHardBounces', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		$response2 = benchmarkemaillite_api::query(
			'reportGetSoftBounces', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
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
		self::showReportHeading(__('Email Unsubscribes Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetUnsubscribes', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
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
		self::showReportHeading(__('Email Forwards Report', 'benchmark-email-lite'));
		$response = benchmarkemaillite_api::query(
			'reportGetForwards', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
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
