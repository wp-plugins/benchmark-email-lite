<?php
/*
Plugin Name: Benchmark Email Lite
Plugin URI: http://www.beautomated.com/benchmark-email-lite/
Description: A plugin to create a Benchmark Email newsletter widget in WordPress.
Version: 1.0
Author: beAutomated
Author URI: http://www.beautomated.com/
License: GPL
*/

// Use widgets_init action hook to execute custom function
add_action('widgets_init', 'benchmarkemaillite_register_widget');
add_filter('plugin_row_meta', array('benchmarkemaillite_widget', 'pluginlinks'), 10, 2);

// Register the widget
function benchmarkemaillite_register_widget() {
	register_widget('benchmarkemaillite_widget');
}

class benchmarkemaillite_widget extends WP_Widget {

	// Process the new widget
	function benchmarkemaillite_widget() {
		$widget_ops = array(
			'classname' => 'benchmarkemaillite_widget',
			'description' => 'Create a Benchmark Email newsletter widget in WordPress.'
		);
		$this->WP_Widget('benchmarkemaillite_widget', 'Benchmark Email Lite', $widget_ops);
	}

	// Administrative Links
	function pluginlinks($links, $file) {
		if (basename($file) == basename(__FILE__)) {
			$link = '<a href="http://www.beautomated.com/contact/">Contact Developer</a>';
			array_unshift($links, $link);
			$link = '<a href="http://www.benchmarkemail.com/?p=68907">Free 30 Day Benchmark Email Trial</a>';
			array_unshift($links, $link);
		}
		return $links;
	}

	// Build the Widget Settings Form
	function form($instance) {
		$defaults = array('title' => 'Join Our Newsletter', 'page' => '', 'token' => '', 'list' => '');
		$instance = wp_parse_args((array) $instance, $defaults);
?>
		<p>
			Widget Title
			<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			Limit to Page
			<?php wp_dropdown_pages("name={$this->get_field_name('page')}&show_option_none=".__('- Show Everywhere -')."&selected=" . esc_attr($instance['page'])); ?>
		</p>
		<p>
			Your Benchmark Email Token
			<input class="widefat" name="<?php echo $this->get_field_name('token'); ?>" type="text" value="<?php echo esc_attr($instance['token']); ?>" />
		</p>
		<p>
			<a href="http://www.benchmarkemail.com/?p=68907" target="_blank">
			Sign-up for a 30-day Free Trial while supporting the plugin Developer!</a>
		</p>
		<p>
			Name of Benchmark Email List
			<input class="widefat" name="<?php echo $this->get_field_name('list'); ?>" type="text" value="<?php echo esc_attr($instance['list']); ?>" />
		</p>
<?php
	}

	// Save the Widget Settings
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['page'] = strip_tags($new_instance['page']);
		$instance['token'] = strip_tags($new_instance['token']);
		$instance['list'] = strip_tags($new_instance['list']);
		return $instance;
	}

	// Display the Widget
	function widget($args, $instance) {
		global $post;
		$response = '';
		extract($args);
		if ($instance['page'] && $instance['page'] != $post->ID) { return false; }
		echo $before_widget;
		$title = apply_filters('widget_title', $instance['title']);
		if (!empty($title)) { echo $before_title . $title . $after_title; }
		if (empty($instance['token']) || empty($instance['list'])) {
			echo '<p class="errormsg">Please configure your Benchmark Email Token and List Name.</p>' . $after_widget;
			return false;
		}
		if ($_POST['formid'] == 'benchmarkemaillite' && !self::valid_email($_POST['subscribe_email'])) {
			$response = '<p class="errormsg">Please enter your valid Email Address.</p>';
		} else if ($email = $_POST['subscribe_email']) {
			$email = preg_replace('/[^a-zA-Z0-9 @.+_-]/', '', $email);
			$first = preg_replace('/[^a-zA-Z0-9 \'-]/', '', $_POST['subscribe_first']);
			$last = preg_replace('/[^a-zA-Z0-9 \'-]/', '', $_POST['subscribe_last']);
			list($status, $client) = self::connect();
			if (!$status) { echo $client . $after_widget; return false; }
			$response = self::subscription($client, $instance['token'], $instance['list'], $email, $first, $last);
		}
		echo '
<form method="post" action="" id="benchmark-email-lite">
<ul style="list-style-type:none;margin:0;">
	<li>
		<input type="hidden" name="formid" value="benchmarkemaillite" />
		<label for="subscribe_first" style="display:block;">First Name</label>
		<input type="text" id="subscribe_first" name="subscribe_first" value="' . $_POST['subscribe_first'] . '" />
	</li>
	<li>
		<label for="subscribe_last" style="display:block;">Last Name</label>
		<input type="text" id="subscribe_last" name="subscribe_last" value="' . $_POST['subscribe_last'] . '" />
	</li>
	<li>
		<label for="subscribe_email" style="display:block;">Email Address (required)</label>
		<input type="text" id="subscribe_email" name="subscribe_email" value="' . $_POST['subscribe_email'] . '" />
	</li>
	<li><input id="subscribe_submit" name="subscribe_submit" type="submit" value="Subscribe" /></li>
	<li>' . $response . '</li>
</ul>
</form>
';
		echo $after_widget;
	}

	// Validate Email Address
	function valid_email($str) {
		return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)
			? true : false;
	}

	// Connect to Benchmark Email
	function connect() {
		require_once(ABSPATH . WPINC . '/class-IXR.php');
		if (!class_exists(IXR_Client)) {
			return array(false, '<p class="errormsg">IXR Client library isn\'t installed.</p>');
		}
		return array(true, new IXR_Client('http://api.benchmarkemail.com/1.0/'));
	}

	// Get Existing Subscriber Data
	function lookup($client, $token, $listID, $email) {
		$client->query('listGetContacts', $token, $listID, $email, 1, 100, 'name', 'asc');
		//$client->query('listGetContactDetails', $token, $listID, $email); THIS API CALL IS BROKEN!
		if ($client->isError()) {
			return array(false, '<p class="errormsg">Error: [C] ' . $client->getErrorMessage()) . '</p>';
		}
		$data = $client->getResponse();
		return array(true, $data[0]['id']);
	}

	// Add or Update Subscriber
	function subscription($client, $token, $list, $email, $first, $last) {

		// Locate List to Subscribe Onto
		$client->query('listGet', $token, '', 1, 100, 'name', 'asc');
		if ($client->isError()) {
			return '<p class="errormsg">Error: [L] ' . $client->getErrorMessage() . '</p>';
		}
		$lists = $client->getResponse();
		$listID = false;
		foreach ($lists as $listdata) {
			if (strtolower($listdata['listname']) == strtolower($list)) { $listID = $listdata['id']; }
		}
		if (!$listID) { return '<p class="errormsg">Error: Unable to locate the specified list.</p>'; }

		// Check for Pre-Existance
		list($status, $contactID) = self::lookup($client, $token, $listID, $email);
		if (!$status) { return $contactID; }

		// Subscriber Doesn't Pre-Exist, Add New Subscriber
		if (!is_numeric($contactID)) {
			$client->query('listAddContacts', $token, $listID, array(array('email' => $email)));
			if ($client->isError()) {
				return '<p class="errormsg">Error: [A] ' . $client->getErrorMessage() . '</p>';
			}

			// Get New Subscriber ID
			list($status, $contactID) = self::lookup($client, $token, $listID, $email);
			if (!$status) { return $contactID; }
		}

		// Set Subscriber Data
		if ($contactID) {
			$client->query(
				'listUpdateContactDetails', $token, $listID, $contactID, array(
					'firstname' => $first, 'lastname' => $last
				)
			);
			if ($client->isError()) { return 'Error: [U] ' . $client->getErrorMessage(); }
			return '<p class="successmsg">Successful. Thank you.</p>';
		}
	}
}

?>