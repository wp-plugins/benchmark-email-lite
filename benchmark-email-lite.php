<?php
/*
Plugin Name: Benchmark Email Lite
Plugin URI: http://www.beautomated.com/benchmark-email-lite/
Description: A plugin to create a Benchmark Email newsletter widget in WordPress.
Version: 1.0.1
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

// Main Class for the Widget
class benchmarkemaillite_widget extends WP_Widget {

	// Process the Widget
	function benchmarkemaillite_widget() {
		$widget_ops = array(
			'classname' => 'benchmarkemaillite_widget',
			'description' => __('Create a Benchmark Email newsletter widget in WordPress.')
		);
		$this->WP_Widget('benchmarkemaillite_widget', __('Benchmark Email Lite'), $widget_ops);
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
		$defaults = array('id' => uniqid(), 'title' => __('Join Our Newsletter'), 'page' => '', 'token' => '', 'list' => '');
		$instance = wp_parse_args((array) $instance, $defaults);
?>
		<p>
			<?php echo __('Widget Title'); ?>
			<input name="<?php echo $this->get_field_name('id'); ?>" type="hidden" value="<?php echo uniqid(); ?>" />
			<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			<?php echo __('Limit to Page'); ?>
<?php
wp_dropdown_pages(
	array(
		'depth' => 0,
		'child_of' => 0,
		'selected' => $instance['page'],
		'echo' => 1,
		'name' => $this->get_field_name('page'),
		'show_option_none' => '- ' . __('Show Everywhere') . ' -'
	)
);
?>
		</p>
		<p>
			<?php echo __('Your Benchmark Email API Key'); ?>
			<input class="widefat" name="<?php echo $this->get_field_name('token'); ?>" type="text" value="<?php echo esc_attr($instance['token']); ?>" />
		</p>
		<p>
			<a href="http://www.benchmarkemail.com/?p=68907" target="_blank">
			<?php echo __('Signup for a 30-day FREE Trial and support this plugin\'s Development!'); ?></a>
		</p>
		<p>
			<?php echo __('Name of Benchmark Email List'); ?>
			<input class="widefat" name="<?php echo $this->get_field_name('list'); ?>" type="text" value="<?php echo esc_attr($instance['list']); ?>" />
		</p>
<?php
	}

	// Save the Widget Settings
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['id'] = sanitize_text_field($new_instance['id']);
		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['page'] = absint($new_instance['page']);
		$instance['token'] = sanitize_text_field($new_instance['token']);
		$instance['list'] = sanitize_text_field($new_instance['list']);
		return $instance;
	}

	// Display the Widget
	function widget($args, $instance) {

		// Vars
		global $post;
		$response = '';
		extract($args);
		$key = $instance['id'];

		// Exclude from Pages/Posts
		if ($instance['page'] && $instance['page'] != $post->ID) { return false; }

		// Begin Outputting Widget
		echo $before_widget;
		$title = apply_filters('widget_title', $instance['title']);
		if (!empty($title)) { echo $before_title . $title . $after_title; }
		if (empty($instance['token']) || empty($instance['list'])) {
			echo '<p class="errormsg">' . __('Error: Please configure your Benchmark Email API Key and List Name.') . '</p>' . $after_widget;
			return false;
		}

		// Process Submission
		if ($_POST['formid'] == 'benchmark-email-lite-' . $key) {

			// Sanitize Submission
			$email = sanitize_email($_POST['subscribe_email'][$key]);
			$first = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_first'][$key])));
			$last = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_last'][$key])));
			if (!self::valid_email($email)) {
				$response = '<p class="errormsg">' . __('Error: Please enter a valid Email Address.') . '</p>';
			} else {
				list($status, $client) = self::connect();
				if (!$status) { echo $client . $after_widget; return false; }
				$response = self::subscription($client, $instance['token'], $instance['list'], $email, $first, $last, $key);
			}

			// Re-process Fields In Case They Were Cleared Upon Success
			$email = sanitize_email($_POST['subscribe_email'][$key]);
			$first = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_first'][$key])));
			$last = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_last'][$key])));
		}

		// Finish Outputting Widget
		echo '
<form method="post" action="#benchmark-email-lite-' . $key . '" id="benchmark-email-lite-' . $key . '">
<ul style="list-style-type:none;margin:0;">
	<li>
		<input type="hidden" name="formid" value="benchmark-email-lite-' . $key . '" />
		<input type="hidden" name="subscribe_key" value="' . $key . '" />
		<label for="subscribe_first-' . $key . '" style="display:block;">' . __('First Name') . '</label>
		<input type="text" id="subscribe_first-' . $key . '" name="subscribe_first[' . $key . ']" value="' . $first . '" />
	</li>
	<li>
		<label for="subscribe_last-' . $key . '" style="display:block;">' . __('Last Name') . '</label>
		<input type="text" id="subscribe_last-' . $key . '" name="subscribe_last[' . $key . ']" value="' . $last . '" />
	</li>
	<li>
		<label for="subscribe_email-' . $key . '" style="display:block;">' . __('Email Address (required)') . '</label>
		<input type="text" id="subscribe_email-' . $key . '" name="subscribe_email[' . $key . ']" value="' . $email . '" />
	</li>
	<li><input type="submit" value="' . __('Subscribe') . '" /></li>
	<li>' . $response . '</li>
</ul>
</form>
' . $after_widget;
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
			return array(false, '<p class="errormsg">' . __('Error: IXR Client library isn\'t installed.') . '</p>');
		}
		return array(true, new IXR_Client('http://api.benchmarkemail.com/1.0/'));
	}

	// Get Existing Subscriber Data
	function lookup($client, $token, $listID, $email) {
		$client->query('listGetContacts', $token, $listID, $email, 1, 100, 'name', 'asc');
		//$client->query('listGetContactDetails', $token, $listID, $email); THIS BME API CALL IS BROKEN!
		if ($client->isError()) {
			return array(false, '<p class="errormsg">Error: [C] ' . $client->getErrorMessage()) . '</p>';
		}
		$data = $client->getResponse();
		return array(true, $data[0]['id']);
	}

	// Add or Update Subscriber
	function subscription($client, $token, $list, $email, $first, $last, $key) {

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
		if (!$listID) { return '<p class="errormsg">' . __('Error: Unable to locate the specified list.') . '</p>'; }

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
			$_POST['subscribe_first'][$key] = '';
			$_POST['subscribe_last'][$key] = '';
			$_POST['subscribe_email'][$key] = '';
			return '<p class="successmsg">' . __('Successful. Thank you.') . '</p>';
		}
	}
}

?>