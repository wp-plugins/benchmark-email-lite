<?php
/*
Plugin Name: Benchmark Email Lite
Plugin URI: http://www.beautomated.com/benchmark-email-lite/
Description: Benchmark Email Lite lets you build an email list right from your WordPress site, and easily send your subscribers email versions of your blog posts.
Version: 2.3.1
Author: beAutomated
Author URI: http://www.beautomated.com/
License: GPLv2

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version, see <http://www.gnu.org/licenses/>.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

// Include Plugin Object Files
require_once('class.api.php');
require_once('class.posts.php');
require_once('class.reports.php');
require_once('class.settings.php');
require_once('class.widget.php');

// Plugin API Hooks
add_action('wp_init', array('benchmarkemaillite', 'initialize'));
add_filter('plugin_row_meta', array('benchmarkemaillite', 'pluginlinks'), 10, 2);
add_action('admin_notices', array('benchmarkemaillite', 'notices'));

// Posts API Hooks
add_action('save_post', array('benchmarkemaillite_posts', 'save_post'));
add_action('admin_init', array('benchmarkemaillite_posts', 'post_metabox'));

// Widget API Hooks
add_action('admin_init', array('benchmarkemaillite_widget', 'loadjs'));
add_action('widgets_init', array('benchmarkemaillite_widget', 'widgetfrontendsubmission'));
add_action('benchmarkemaillite_queue', array('benchmarkemaillite_widget', 'queue_upload'));
add_action('widgets_init', 'benchmarkemaillite_register_widget');
function benchmarkemaillite_register_widget() { register_widget('benchmarkemaillite_widget'); }

// Settings API Hooks
add_action('init', array('benchmarkemaillite_settings', 'upgrade1'));
add_action('init', array('benchmarkemaillite_settings', 'upgrade2'));
add_action('admin_init', array('benchmarkemaillite_settings', 'initialize'));
add_action('admin_menu', array('benchmarkemaillite_settings', 'menu'));
add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('benchmarkemaillite_settings', 'links'));

class benchmarkemaillite {

	// Variables Available Without Class Instantiation
	static $apiurl = 'https://api.benchmarkemail.com/1.0/';
	static $linkaffiliate = 'http://www.benchmarkemail.com/Register';
	static $linkcontact = 'http://www.beautomated.com/contact/';

	/**********************
	 WORDPRESS HOOK METHODS
	 **********************/

	// Load Localizations
	function initialize() {
		load_plugin_textdomain('benchmark-email-lite', false, basename(dirname(__FILE__)) . '/languages');
	}

	// Administrative Links
	function pluginlinks($links, $file) {
		if (basename($file) == basename(__FILE__)) {
			$link = '<a href="' . benchmarkemaillite::$linkcontact . '">' . __('Contact Developer', 'benchmark-email-lite') . '</a>';
			array_unshift($links, $link);
			$link = '<a href="' . benchmarkemaillite::$linkaffiliate . '">' . __('Free 30 Day Benchmark Email Trial', 'benchmark-email-lite') . '</a>';
			array_unshift($links, $link);
		}
		return $links;
	}

	/********
	 MESSAGES
	 ********/

	// Good Key Or Connection Message
	function goodconnection_message() {
		return __('Valid API key and API server connection.', 'benchmark-email-lite');
	}

	// Bad Key Or Connection Message
	function badconnection_message() {
		return __('Invalid API key or API server connection problem.', 'benchmark-email-lite');
	}

	/*******
	 UTILITY
	 *******/

	// Admmin Area Notices
	function notices() {
		if ($val = get_transient('benchmark-email-lite_errors')) {
			echo "<div class='fade updated'><p><strong>Benchmark Email Lite</strong></p><p>{$val}</p></div>";
		}
	}

	// Makes Drop Down Lists From API Keys
	function print_lists($keys, $selected='') {
		$lists = array();
		foreach ($keys as $key) {
			if (!$key) { continue; }
			benchmarkemaillite_api::$token = $key;
			$response = benchmarkemaillite_api::lists();
			$lists[$key] = is_array($response) ? $response : '';
		}

		// Generate Output
		$output = '';
		$i = 0;
		foreach ($lists as $key => $list1) {
			if (!$key) { continue; }
			if ($i > 0) { $output .= "<option disabled='disabled' value=''></option>\n"; }
			$output .= "<option disabled='disabled' value=''>{$key}</option>\n";
			if (!$list1) {
				$i++;
				$list1 = array();
				$output .= "<option value=''" . (($i == 1) ? " selected='selected'" : '')
					. " disabled='disabled'>↳ " . self::badconnection_message() . "</option>\n";
				continue;
			}
			foreach ($list1 as $list) {
				if ($list['listname'] == 'Master Unsubscribe List') { continue; }
				$i++;
				if (!$selected && $i == 1) { $select = " selected='selected'"; }
				else {
					$select = ($selected == "{$key}|{$list['listname']}|{$list['id']}")
						? " selected='selected'" : '';
				}
				$output .= "<option{$select} value='{$key}|{$list['listname']}|{$list['id']}'>↳ {$list['listname']}</option>\n";
			}
		}
		return $output;
	}

	// Provides Ability to Return Included File Output
	function require_to_var($data, $file) {
		ob_start();
		require($file);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
		return ob_get_clean();
	}

	// HTML Table Generator
	function maketable($data) {
	?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<?php foreach ($data[0] as $i => $val) { ?>
				<th><?php echo $i; ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach($data as $i => $val) { ?>
			<tr>
				<?php foreach ($val as $i2 => $val2) { ?>
				<td><?php echo $val2; ?></td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php
	}
}

?>