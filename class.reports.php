<?php

class benchmarkemaillite_reports {
	static $url = 'options-general.php?page=benchmark-email-lite&amp;tab=reports';
	static $campaign = false;

	function show() {
		$options = get_option('benchmark-email-lite_group');
		
		// Handle Requests
		if (isset($_GET['tokenindex']) && isset($_GET['campaign'])) {
			echo '<p><a href="' . self::$url . '" title="' . __('Back to Email Reports', 'benchmark-email-lite')
				. '">' . __('Back to Email Reports', 'benchmark-email-lite') . '</a></p>';
			$tokenindex = intval($_GET['tokenindex']);
			benchmarkemaillite_api::$token = $options[1][$tokenindex];
			self::$campaign = (string)intval($_GET['campaign']);
			$url = self::$url . '&amp;campaign=' . self::$campaign . "&amp;tokenindex={$tokenindex}&amp;show=";

			// Show Detail Page
			$show = isset($_GET['show']) ? strtolower(esc_attr($_GET['show'])) : false;
			$show = isset($_POST['show']) ? strtolower(esc_attr($_POST['show'])) : $show;
			if ($show) {
				echo '<p><a href="' . $url . '" title="' . __('Back to Email Campaign Report', 'benchmark-email-lite')
					. '">' . __('Back to Email Campaign Report', 'benchmark-email-lite') . '</a></p>';
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
		echo '<h3>' . __('Opens by Location', 'benchmark-email-lite') . '</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetOpenCountry', benchmarkemaillite_api::$token, self::$campaign
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
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
	}
	function showClicks() {
		echo '<h3>' . __('Email Clicks Report', 'benchmark-email-lite') . '</h3>';
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
							__('Name', 'benchmark-email-lite') => $row2['name'],
							__('Email', 'benchmark-email-lite') => $row2['email'],
							__('URL', 'benchmark-email-lite') => $row['URL'],
							__('Date', 'benchmark-email-lite') => $row2['logdate'],
						);
					}
				}
			}
			benchmarkemaillite::maketable($data);
		}
	}
	function showOpens() {
		echo '<h3>' . __('Email Opens Report', 'benchmark-email-lite') . '</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetOpens', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
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
	function showUnopens() {
		echo '<h3>' . __('Email Unopened Report', 'benchmark-email-lite') . '</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetUnopens', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
			$data = array();
			foreach ($response as $row) {
				$data[] = array(
					__('Name', 'benchmark-email-lite') => $row['name'],
					__('Email', 'benchmark-email-lite') => $row['email'],
				);
			}
			benchmarkemaillite::maketable($data);
		}
	}
	function showBounces() {
		echo '<h3>' . __('Email Bounce Report', 'benchmark-email-lite') . '</h3>';
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
					__('Name', 'benchmark-email-lite') => $row['name'],
					__('Email', 'benchmark-email-lite') => $row['email'],
					__('Bounce Type', 'benchmark-email-lite') => $row['type'],
				);
			}
			benchmarkemaillite::maketable($data);
		}
	}
	function showUnsubscribes() {
		echo '<h3>' . __('Email Unsubscribes Report', 'benchmark-email-lite') . '</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetUnsubscribes', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
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
	function showForwards() {
		echo '<h3>' . __('Email Forwards Report', 'benchmark-email-lite') . '</h3>';
		benchmarkemaillite_api::$client->query(
			'reportGetForwards', benchmarkemaillite_api::$token, self::$campaign, 1, 100, 'date', 'desc'
		);
		if ($response = benchmarkemaillite_api::$client->getResponse()) {
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
}

?>