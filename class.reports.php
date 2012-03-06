<?php

class benchmarkemaillite_reports {
	static $url = 'options-general.php?page=benchmark-email-lite&amp;tab=reports';

	function show() {
		$options = get_option('benchmark-email-lite_group');
		
		// Handle Requests
		if (isset($_GET['tokenindex']) && isset($_GET['campaign'])) {
			echo "<p><a href='" . self::$url . "'>Back to Reports</a></p>";
			$tokenindex = intval($_GET['tokenindex']);
			benchmarkemaillite_api::$token = $options[1][$tokenindex];
			$id = (string)intval($_GET['campaign']);
			$url = self::$url . "&amp;campaign={$id}&amp;tokenindex={$tokenindex}&amp;show=";

			// Show Detail Page
			if (isset($_GET['show']) && $show = esc_attr($_GET['show'])) {
				benchmarkemaillite_api::connect();
				switch ($show) {
					case 'clicks':
						benchmarkemaillite_api::$client->query(
							'reportGetClicks', benchmarkemaillite_api::$token, $id
						);
						break;
					case 'opens':
						benchmarkemaillite_api::$client->query(
							'reportGetOpens', benchmarkemaillite_api::$token, $id, 1, 100, 'date', 'desc'
						);
						break;
					case 'unopens':
						benchmarkemaillite_api::$client->query(
							'reportGetUnopens', benchmarkemaillite_api::$token, $id, 1, 100, 'date', 'desc'
						);
						break;
					case 'bounces':
						benchmarkemaillite_api::$client->query(
							'reportGetHardBounces', benchmarkemaillite_api::$token, $id, 1, 100, 'date', 'desc'
						);
						break;
					case 'unsubscribes':
						benchmarkemaillite_api::$client->query(
							'reportGetUnsubscribes', benchmarkemaillite_api::$token, $id, 1, 100, 'date', 'desc'
						);
						break;
					case 'forwards':
						benchmarkemaillite_api::$client->query(
							'reportGetForwards', benchmarkemaillite_api::$token, $id, 1, 100, 'date', 'desc'
						);
						break;
				}
				if ($response = benchmarkemaillite_api::$client->getResponse()) {
					benchmarkemaillite::maketable($response);
				} else {
					echo '<p>' . __('Sorry, no data is available.', 'benchmark-email-lite') . '</p>';
				}
			}
		
			// Campaign Summary
			else {
				$response = benchmarkemaillite_api::campaign_summary($id);
				$response['unopens'] = intval($response['mailSent']) - intval($response['opens']);
				$response = array_merge($response, get_transient("benchmarkemaillite_{$id}"));
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
}

?>