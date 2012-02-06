<?php

class benchmarkemaillite_settings {

	/***************
	 WP Hook Methods
	 ***************/

	// Plugin Upgrade Reactivation - Hooked Into Activation And Admin Area Notice
	function activate() {

		// Search For v1.x Widgets, Move API Keys To Plugin Settings
		$widgets = get_option('widget_benchmarkemaillite_widget');
		$tokens = array();
		foreach ($widgets as $instance => $widget) {
			if (isset($widget['token']) && $widget['token'] != '') {
				$tokens[] = $widget['token'];

				// Update List Selection In Widget
				benchmarkemaillite_api::$token = $widget['token'];
				$lists = benchmarkemaillite_api::lists();
				foreach ($lists as $list) {
					if ($list['listname'] == $widget['list']) {
						$widgets[$instance]['list'] = "{$widget['token']}|{$widget['list']}|{$list['id']}";
					}
				}
			}
		}
		update_option('widget_benchmarkemaillite_widget', $widgets);

		// Gather Preexisting API Keys
		$options = get_option('benchmark-email-lite_group');
		if (isset($options[1]) && is_array($options[1])) {
			$tokens = array_merge($tokens, $options[1]);
		}
		$tokens = array_unique($tokens);
		update_option(
			'benchmark-email-lite_group', array(
				1 => $tokens,

				// Set Default Values
				2 => isset($options[2]) ? $options[2] : 'yes',
				3 => isset($options[3]) ? $options[3] : 'simple',
				4 => isset($options[4]) ? $options[4] : '',
			)
		);

		// Vendor Handshake With Benchmark Email
		benchmarkemaillite_api::handshake($tokens);
	}

	// Admin Settings Notice
	function notices() {

		// Try To Upgrade v1.x to v2.x Plugin Settings
		// This Exists Because WordPress Sadly Doesn't Fire Activation Hook Upon Upgrade Reactivation
		$options = get_option('benchmark-email-lite_group');
		if (!isset($options[1][0])) { self::activate(); }

		// Check Second Time For API Key - If Not Found, Print Admin Notice
		$options = get_option('benchmark-email-lite_group');
		if (!isset($options[1][0])) {
			echo '<div class="fade updated"><p><strong>Benchmark Email Lite</strong></p><p>' . __('Please configure your API Key(s) on the', 'benchmark-email-lite') . ' '
				. '<a href="options-general.php?page=benchmark-email-lite">' . __('settings page', 'benchmark-email-lite') . '</a>.</p></div>';
		}
	}

	// Admin Load
	function initialize() {
		register_setting('benchmark-email-lite_group', 'benchmark-email-lite_group', array('benchmarkemaillite_settings', 'validate'));
		add_settings_section('benchmark-email-lite_section1', __('Benchmark Email Credentials', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'section1'), __FILE__);
		add_settings_section('benchmark-email-lite_section2', __('New Email Campaign Preferences', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'section2'), __FILE__);
		add_settings_field('benchmark-email-lite_1', __('API Key(s) from your Benchmark Email account(s)', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'field1'), __FILE__, 'benchmark-email-lite_section1');
		add_settings_field('benchmark-email-lite_2', __('Webpage version', 'benchmark-email-lite'), array('benchmarkemaillite_settings', 'field2'), __FILE__, 'benchmark-email-lite_section2');
		add_settings_field('benchmark-email-lite_4', '', array('benchmarkemaillite_settings', 'field4'), __FILE__, 'benchmark-email-lite_section2');
		add_settings_field('benchmark-email-lite_3', '', array('benchmarkemaillite_settings', 'field3'), __FILE__, 'benchmark-email-lite_section2');
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
		echo '<div class="wrap">' . screen_icon() . '<h2>Benchmark Email Lite</h2>'
 			. '<form action="options.php" method="post">';
		settings_fields('benchmark-email-lite_group');
		do_settings_sections(__FILE__);
		echo '<p><input name="Submit" type="submit" class="button-primary" value="Save Changes" /></p>'
			. '</form></div>';
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
	function field1() {
		$options = get_option('benchmark-email-lite_group');
		$key = $options[1];
		$key[0] = isset($key[0]) ? $key[0] : '';
		$key[1] = isset($key[1]) ? $key[1] : '';
		$key[2] = isset($key[2]) ? $key[2] : '';
		$key[3] = isset($key[3]) ? $key[3] : '';
		$key[4] = isset($key[4]) ? $key[4] : '';
		echo "<input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[0]}' /> Primary<br />
			<input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[1]}' /> Optional<br />
			<input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[2]}' /> Optional <br />
			<input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[3]}' /> Optional<br />
			<input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[4]}' /> Optional";
	}
	function field2() {
		$options = get_option('benchmark-email-lite_group');
		echo "<input id='benchmark-email-lite_group_2' type='checkbox' name='benchmark-email-lite_group[2]' value='yes'" . checked('yes', $options[2], false) . " /> "
			. __("Include the sentence &quot;Having trouble viewing this email? <u>click here</u>.&quot; in the top of emails?", 'benchamrk-email-lite');
	}
	function field3() { // Design Template - This Field Is Disabled
		$options = get_option('benchmark-email-lite_group');
		echo "<input id='benchmark-email-lite_group_3' type='hidden' name='benchmark-email-lite_group[3]'
			value='simple' checked='checked' />";
	}
	function field4() { // Permission Reminder - This Field Is Disabled
		$options = get_option('benchmark-email-lite_group');
		echo "<input id='benchmark-email-lite_group_4' type='hidden' name='benchmark-email-lite_group[4]'
			value='' checked='checked' />";
	}
	function validate($values) {
		foreach ($values as $key => $val) {
			if ($key == '1') {
				benchmarkemaillite_api::handshake($val);

				// WordPress Sadly Pre-serializes This
				$values[$key] = is_serialized($val) ? unserialize($val) : $val;
				$values[$key] = array_unique($values[$key], SORT_STRING);
			}
			else { $values[$key] = esc_attr($val); }
		}
		return $values;
	}
}

?>
