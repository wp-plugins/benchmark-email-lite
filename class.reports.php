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
			foreach ($options[1] as $tokenindex => $key) {
				$emails = array();
				if (!$key) { continue; }
				benchmarkemaillite_api::$token = $key;
				$response = benchmarkemaillite_api::campaigns();
				if (sizeof($response) > 0) {
					foreach ($response as $email) {
						$email['toListName'] = isset($email['toListName']) ? $email['toListName'] : '[none]';
						$emails[] = $email;
						set_transient("benchmarkemaillite_{$email['id']}", $email);
					}
					require('reports.overview.html.php');
					continue;
				}
				echo '<p><strong>' . __('No results found for API key: ', 'benchmark-email-lite') . "</strong> {$key}</p>";
			}
		}
	}

	// Specific Reports
	function showLocations() {
		echo '<h3>' . __('Opens by Location', 'benchmark-email-lite') . '</h3>';
		$response = benchmarkemaillite_api::query(
			'reportGetOpenCountry', benchmarkemaillite_api::$token, self::$campaign
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
	function showClicks() {
		echo '<h3>' . __('Email Clicks Report', 'benchmark-email-lite') . '</h3>';
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
		echo '<h3>' . __('Email Opens Report', 'benchmark-email-lite') . '</h3>';
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
		echo '<h3>' . __('Email Unopened Report', 'benchmark-email-lite') . '</h3>';
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
		echo '<h3>' . __('Email Bounce Report', 'benchmark-email-lite') . '</h3>';
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
		echo '<h3>' . __('Email Unsubscribes Report', 'benchmark-email-lite') . '</h3>';
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
		echo '<h3>' . __('Email Forwards Report', 'benchmark-email-lite') . '</h3>';
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