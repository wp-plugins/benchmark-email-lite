<?php

class benchmarkemaillite_reports {
	static $url = 'options-general.php?page=benchmark-email-lite&amp;tab=reports';
	static $campaign = false;

	function show() {
		$options = get_option('benchmark-email-lite_group');
		
		// Handle Requests
		if (isset($_GET['tokenindex']) && isset($_GET['campaign'])) {
			echo "<p><a href='" . self::$url . "'>Back to Email Reports</a></p>";
			$tokenindex = intval($_GET['tokenindex']);
			benchmarkemaillite_api::$token = $options[1][$tokenindex];
			self::$campaign = (string)intval($_GET['campaign']);
			$url = self::$url . '&amp;campaign=' . self::$campaign . "&amp;tokenindex={$tokenindex}&amp;show=";

			// Show Detail Page
			if (isset($_GET['show']) && $show = esc_attr($_GET['show'])) {
				echo "<p><a href='{$url}'>Back to Email Campaign Report</a></p>";
				benchmarkemaillite_api::connect();
				switch ($show) {
					case 'clicks': self::showClicks(); break;
					case 'opens': self::showOpens(); break;
					case 'unopens': self::showUnopens(); break;
					case 'bounces': self::showBounces(); break;
					case 'unsubscribes': self::showUnsubscribes(); break;
					case 'forwards': self::showForwards(); break;
				}
			}
		
			// Campaign Summary
			else {
				$response = benchmarkemaillite_api::campaign_summary(self::$campaign);
				$response['unopens'] = intval($response['mailSent']) - intval($response['opens']);
				$response = array_merge($response, get_transient('benchmarkemaillite_' . self::$campaign));
				/*
				[mailSent] => 143
				[id] => 1114607
				[emailName] => November 2011 Newsletter
				[clicks] => 2
				[opens] => 42
				[bounces] => 0
				[abuseReports] => 0
				[unsubscribes] => 0
				[toListID] => 169055
				[toListName] => beAutomated Distribution
				[forwards] => 0
				[scheduleDate] => Dec 06 2011, 08:00 AM
				[subject] => November Update from beAutomated
				[shareURL] => http://visitor.benchmarkemail.com/c/b/4A6947
				[timezone] => PDT
				*/
				require_once('reports.detail.html.php');
			}
		}
		
		// Get Sent Campaigns
		else {
			echo '<p>' . __('Please select a campaign to view the report.', 'benchmark-email-lite') . '</p>';
			$emails = array();
			foreach ($options[1] as $tokenindex => $key) {
				if (!$key) { continue; }
				benchmarkemaillite_api::$token = $key;
				$response = benchmarkemaillite_api::campaigns();
				/*
				[sequence]=> 1
				[id] => 1367579
				[emailName] => test
				[isSegment] => false
				[toListID] => 175166
				[toListName] => Test List
				[status] => Sent
				[webpageVersion] => false
				[scheduleDate] => Mar 02, 2012
				[createdDate] => Jan 01, 0001
				[modifiedDate] => Jan 01, 0001
				[rssurl] => 
				[listcount] => 4
				[communityurl] => http://community.benchmarkemail.com/users/beautomated/newsletter/test
				[isfavorite] => 0
				[encToken] => mFcQnoBFKMTLHop7IM%2BVPRJBf%2BXlOWc4
				*/
				foreach ($response as $email) {
					$emails[] = $email;
					set_transient("benchmarkemaillite_{$email['id']}", $email);
				}
			}
			require_once('reports.overview.html.php');
		}
	}

	// Specific Reports
	function showLocations() {
		echo '<h3>Opens by Location</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetOpenCountry', benchmarkemaillite_api::$token, self::$campaign
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
			$data = array();
			foreach ($response as $row) {
				if (!$row['openCount']) { continue; }
				$data[] = array(
					'Country' => ucwords(strtolower($row['country_name'])),
					'Opens' => $row['openCount'],
				);
			}
			benchmarkemaillite::maketable($data);
		} else {
			echo '<p>' . __('Sorry, no data is available.', 'benchmark-email-lite') . '</p>';
		}
	}
	function showClicks() {
		echo '<h3>Email Clicks Report</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetClicks', benchmarkemaillite_api::$token, self::$campaign
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
			$data = array();
			foreach ($response as $row) {
				benchmarkemaillite_api::$client->query(
					'reportGetClickEmails', benchmarkemaillite_api::$token, self::$campaign, $row['URL'], 1, 100, 'date', 'desc'
				);
				if ($response2 = benchmarkemaillite_api::$client->getResponse()) {
					foreach ($response2 as $row2) {
						$data[] = array(
							'Name' => $row2['name'],
							'Email' => $row2['email'],
							'URL' => $row['URL'],
							'Date' => $row2['logdate'],
						);
					}
				}
			}
			benchmarkemaillite::maketable($data);
		} else {
			echo '<p>' . __('Sorry, no data is available.', 'benchmark-email-lite') . '</p>';
		}
	}
	function showOpens() {
		echo '<h3>Email Opens Report</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetOpens', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
			$data = array();
			foreach ($response as $row) {
				$data[] = array(
					'Name' => $row['name'],
					'Email' => $row['email'],
					'Date' => $row['logdate'],
				);
			}
			benchmarkemaillite::maketable($data);
		} else {
			echo '<p>' . __('Sorry, no data is available.', 'benchmark-email-lite') . '</p>';
		}
	}
	function showUnopens() {
		echo '<h3>Email Unopened Report</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetUnopens', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
			$data = array();
			foreach ($response as $row) {
				$data[] = array(
					'Name' => $row['name'],
					'Email' => $row['email'],
				);
			}
			benchmarkemaillite::maketable($data);
		} else {
			echo '<p>' . __('Sorry, no data is available.', 'benchmark-email-lite') . '</p>';
		}
	}
	function showBounces() {
		echo '<h3>Email Bounce Report</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetHardBounces', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		$response1 = benchmarkemaillite_api::$client->getResponse();
		benchmarkemaillite_api::$client->query(
			'reportGetSoftBounces', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		$response2 = benchmarkemaillite_api::$client->getResponse();
		$response = array_merge($response1, $response2);
		if ($response) {
			$data = array();
			foreach ($response as $row) {
				$data[] = array(
					'Name' => $row['name'],
					'Email' => $row['email'],
					'Bounce Type' => $row['type'],
				);
			}
			benchmarkemaillite::maketable($data);
		} else {
			echo '<p>' . __('Sorry, no data is available.', 'benchmark-email-lite') . '</p>';
		}
	}
	function showUnsubscribes() {
		echo '<h3>Email Unsubscribes Report</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetUnsubscribes', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
			$data = array();
			foreach ($response as $row) {
				$data[] = array(
					'Name' => $row['name'],
					'Email' => $row['email'],
					'Date' => $row['logdate'],
				);
			}
			benchmarkemaillite::maketable($data);
		} else {
			echo '<p>' . __('Sorry, no data is available.', 'benchmark-email-lite') . '</p>';
		}
	}
	function showForwards() {
		echo '<h3>Email Forwards Report</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetForwards', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
			benchmarkemaillite::maketable($response);
		} else {
			echo '<p>' . __('Sorry, no data is available.', 'benchmark-email-lite') . '</p>';
		}
	}
}

?>