<?php

class benchmarkemaillite_settings {

	/***************
	 WP Hook Methods
	 ***************/

	// Bad Configuration Message
	function badconfig_message() {
		return '<br /><strong style="color:red;">'
			. __('Please configure your API key(s) on the', 'benchmark-email-lite')
			. ' <a href="options-general.php?page=benchmark-email-lite">'
			. __('Benchmark Email Lite settings page', 'benchmark-email-lite')
			. '.</a></strong>';
	}

	// Triggered By Front And Back Ends - Try To Upgrade Plugin and Widget Settings
	// This Exists Because WordPress Sadly Doesn't Fire Activation Hook Upon Upgrade Reactivation
	function upgrade1() {
		$options = get_option('benchmark-email-lite_group');

		// Check And Set Defaults
		if (!isset($options[1])) { $options[1] = array(); }
		if (!isset($options[2])) { $options[2] = 'yes'; }
		if (!isset($options[3])) { $options[3] = 'simple'; }
		if (!isset($options[4])) { $options[4] = ''; }
		if (!isset($options[5])) { $options[5] = 5; }
		update_option('benchmark-email-lite_group', $options);

		// Exit If Already Configured
		if (isset($options[1][0]) && $options[1][0]) { return; }

		// Search For v1.x Widgets, Gather API Keys For Plugin Settings
		$tokens = array();
		$widgets = get_option('widget_benchmarkemaillite_widget');
		if (is_array($widgets)) {
			foreach ($widgets as $instance => $widget) {
				if (isset($widget['token']) && $widget['token'] != '') {
					$tokens[] = $widget['token'];
	
					// Update List Selection In Widget
					benchmarkemaillite_api::$token = $widget['token'];
					$lists = benchmarkemaillite_api::lists();
					if (!is_array($lists)) { continue; }
					foreach ($lists as $list) {
						if ($list['listname'] == $widget['list']) {
							$widgets[$instance]['list']
								= "{$widget['token']}|{$widget['list']}|{$list['id']}";
						}
					}
				}
			}
			update_option('widget_benchmarkemaillite_widget', $widgets);
		}

		// Gather Preexisting API Keys
		if (isset($options[1][0])) {$tokens = array_merge($tokens, $options[1]); }
		$tokens = array_unique($tokens);
		update_option(
			'benchmark-email-lite_group', array(
				1 => $tokens, 2 => $options[2], 3 => $options[3], 4 => $options[4], 5 => $options[5],
			)
		);

		// Vendor Handshake With Benchmark Email
		benchmarkemaillite_api::handshake($tokens);
	}

	// Search For v2.0.x Widgets And Upgrade To 2.1
	function upgrade2() {
		$widgets = get_option('widget_benchmarkemaillite_widget');
		if (!is_array($widgets)) { return; }
		$changed = false;
		foreach ($widgets as $instance => $widget) {
			if (!is_array($widget) || isset($widget['fields'])) { continue; }
			$changed = true;
			if (isset($widget['showname']) && $widget['showname'] != '1') {
				$widgets[$instance]['fields'] = array('Email');
				$widgets[$instance]['fields_labels'] = array('Email');
				$widgets[$instance]['fields_required'] = array(1);
			} else {
				$widgets[$instance]['fields'] = array('First Name', 'Last Name', 'Email');
				$widgets[$instance]['fields_labels'] = array('First Name', 'Last Name', 'Email');
				$widgets[$instance]['fields_required'] = array(0, 0, 1);
			}
		}
		if ($changed) { update_option('widget_benchmarkemaillite_widget', $widgets); }
	}

	// Admin Load
	function initialize() {

		// Admin Settings Notice
		$options = get_option('benchmark-email-lite_group');
		if (!isset($options[1][0]) || !$options[1][0]) {
			set_transient('benchmark-email-lite_errors', self::badconfig_message(), 5);
		}

		// Load Settings API
		register_setting('benchmark-email-lite_group', 'benchmark-email-lite_group', array('benchmarkemaillite_settings', 'validate'));
		add_settings_section('benchmark-email-lite_section1', __('Benchmark Email Credentials', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'section1'), __FILE__);
		add_settings_section('benchmark-email-lite_section2', __('New Email Campaign Preferences', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'section2'), __FILE__);
		add_settings_section('benchmark-email-lite_section3', __('Diagnostics', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'section3'), __FILE__);
		add_settings_field('benchmark-email-lite_1', __('API Key(s) from your Benchmark Email account(s)', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'field1'), __FILE__, 'benchmark-email-lite_section1');
		add_settings_field('benchmark-email-lite_2', __('Webpage version', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'field2'), __FILE__, 'benchmark-email-lite_section2');
		add_settings_field('benchmark-email-lite_4', '', array('benchmarkemaillite_settings', 'field4'), __FILE__, 'benchmark-email-lite_section2');
		add_settings_field('benchmark-email-lite_3', '', array('benchmarkemaillite_settings', 'field3'), __FILE__, 'benchmark-email-lite_section2');
		add_settings_field('benchmark-email-lite_5', __('Connection Timeout (seconds)', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'field5'), __FILE__, 'benchmark-email-lite_section3');
	}

	// Admin Menu
	function menu() {
		add_options_page(
			'Benchmark Email Lite',
			'Benchmark Email Lite',
			'manage_options',
			'benchmark-email-lite',
			array('benchmarkemaillite_settings', 'page')
		);
	}

	// Plugins Page Settings Link
	function links($links) {
		return array(
			'settings' => '<a href="options-general.php?page=benchmark-email-lite">' . __('Settings', 'benchmark-email-lite') . '</a>',
			'deactivate' => $links['deactivate'],
			'edit' => $links['edit'],
		);
	}

	/********************
	 Settings API Methods
	 ********************/

	function page() {
		$options = get_option('benchmark-email-lite_group');
		$tabs = array('reports' => 'Benchmark Email Lite Reports', 'settings' => 'Benchmark Email Lite Settings');
		$current = isset($_GET['tab']) ? esc_attr($_GET['tab']) : 'settings';
 		require_once('admin.html.php');
	}
	function print_settings() {
		echo '<form action="options.php" method="post">';
		settings_fields('benchmark-email-lite_group');
		do_settings_sections(__FILE__);
		echo '<p><input name="Submit" type="submit" class="button-primary" value="Save Changes" /></p></form>';
	}
	function section1() {
		echo '<p>' . __('The API Key(s) connect your WordPress site with your Benchmark Email account(s).', 'benchmark-email-lite') . ' '
			. __('Only one key is required per Benchmark Email account.', 'benchmark-email-lite') . ' '
			. __('API Key(s) may expire after one year.', 'benchmark-email-lite') . '</p>'
			. '<p><a href="' . benchmarkemaillite::$linkaffiliate . '" target="BenchmarkEmail">'
			. __('Signup for a 30-day FREE Trial', 'benchmark-email-lite') . '</a>, ' . __('or', 'benchmark-email-lite')
			. ' <a href="http://ui.benchmarkemail.com/EditSetting#_ctl0_ContentPlaceHolder1_UC_ClientSettings1_lnkGenerate" target="BenchmarkEmail">'
			. __('log in to Benchmark Email to get your API key', 'benchmark-email-lite') . '</a>.</p>';
	}
	function section2() { }
	function section3() {
		echo '<p style="color:red;">' . __('This section is for troubleshooting purposes only.', 'benchmark-email-lite') . '</p>';
	}
	function field1() {
		$options = get_option('benchmark-email-lite_group');
		$results = array();
		$key = $options[1];
		for ($i = 0; $i < 5; $i++) {
			$key[$i] = isset($key[$i]) ? $key[$i] : '';
			if (!$key[$i]) { $results[$i] = '<img style="vertical-align:middle;opacity:0;" src="images/yes.png" alt="" width="16" height="16" />'; }
			else {
				benchmarkemaillite_api::$token = $key[$i];
				$results[$i] = (is_array(benchmarkemaillite_api::lists()))
					? '<img style="vertical-align:middle;" src="images/yes.png" alt="Yes" title="'
						. benchmarkemaillite::goodconnection_message() . '" width="16" height="16" />'
					: '<img style="vertical-align:middle;" style="" src="images/no.png" alt="No" title="'
						. benchmarkemaillite::badconnection_message() . '" width="16" height="16" />';
			}
		}
		echo "{$results[0]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[0]}' /> Primary<br />
			{$results[1]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[1]}' /> Optional<br />
			{$results[2]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[2]}' /> Optional <br />
			{$results[3]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[3]}' /> Optional<br />
			{$results[4]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[4]}' /> Optional";
	}
	function field2() {
		$options = get_option('benchmark-email-lite_group');
		echo "<input id='benchmark-email-lite_group_2' type='checkbox' name='benchmark-email-lite_group[2]'
			value='yes'" . checked('yes', $options[2], false) . " /> "
			. __("Include the sentence &quot;Having trouble viewing this email? <u>click here</u>.&quot; in the top of emails?", 'benchamrk-email-lite');
	}
	function field3() { // Design Template - This Field Is Disabled
		echo "<input id='benchmark-email-lite_group_3' type='hidden' name='benchmark-email-lite_group[3]'
			value='simple' checked='checked' />";
	}
	function field4() { // Permission Reminder - This Field Is Disabled
		echo "<input id='benchmark-email-lite_group_4' type='hidden' name='benchmark-email-lite_group[4]'
			value='' checked='checked' />";
	}
	function field5() {
		$options = get_option('benchmark-email-lite_group');
		echo __('If the connection with the Benchmark Email server takes', 'benchmark-email-lite')
			. " <input id='benchmark-email-lite_group_5' type='text' size='2' maxlength='2' name='benchmark-email-lite_group[5]' value='{$options[5]}' /> "
			. __('seconds or longer, disable connections for 5 minutes to prevent site administration from becoming sluggish. (Default: 5)', 'benchmark-email-lite');
	}
	function validate($values) {
		$options = get_option('benchmark-email-lite_group');
		foreach ($options as $key => $val) {
			$val = isset($values[$key]) ? $values[$key] : '';

			// Process Saving Of API Keys
			if ($key == '1') {

				// WordPress Sadly Pre-serializes This Array Setting
				$values[1] = is_serialized($val) ? unserialize($val) : $val;

				// Remove Empties
				$values[1] = array_filter($values[1]);

				// Remove Duplicates
				$values[1] = array_unique($values[1]);

				// Reset Keys
				$values[1] = array_values($values[1]);

				// Vendor Handshake With Benchmark Email
				benchmarkemaillite_api::handshake($values[1]);
			}

			// Sanitize Non Array Settings
			else { $values[$key] = esc_attr($val); }
		}
		return $values;
	}
}

?>