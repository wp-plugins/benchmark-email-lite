<?php
/*
Plugin Name: Benchmark Email Lite
Plugin URI: http://www.beautomated.com/benchmark-email-lite/
Description: A plugin to create a Benchmark Email newsletter widget in WordPress.
Version: 1.0.2
Author: beAutomated
Author URI: http://www.beautomated.com/
License: GPL
*/

// Global Variables
$benchmarkemaillite_client = false;
$benchmarkemaillite_token = false;
$benchmarkemaillite_listid = false;
$benchmarkemaillite_apiurl = 'http://api.benchmarkemail.com/1.0/';
$benchmarkemaillite_cachefile = plugin_dir_path(__FILE__) . 'subscription_cache.csv';

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
			<?php echo __('Signup for a 30-day FREE Trial and support this plugin\'s development!'); ?></a>
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

		// Widget Variables
		global $post, $benchmarkemaillite_token;
		$benchmarkemaillite_token = $instance['token'];
		$response = '';
		extract($args);
		$key = ($val = $instance['id']) ? $val : uniqid();

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

			// Validate Email Address
			if (!self::valid_email($email)) {
				$response = '<p class="errormsg">' . __('Error: Please enter a valid Email Address.') . '</p>';
			}

			// Valid Email Address
			else {

				// Connect to Benchmark Email
				if (!$response) {
					$status = self::bme_connect();
					if ($status !== true) { $response = $status; }
				}

				// Locate List
				if (!$response) {
					$status = self::bme_list($instance['list']);
					if ($status !== true) { $response = $status; }
				}

				// Flush Cache and Run Live Subscription
				if (!$response) {
					self::bme_upload($key);
					$response = self::bme_subscribe($email, $first, $last, $key);
				}

				// Handle Failover to Cache File
				if (!strstr($response, 'successmsg')) {
					$response = self::cache_subscription($instance['list'], $email, $first, $last, $key);
				}
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

	// Cache Subscription Option
	function cache_subscription($list, $email, $first, $last, $key) {
		global $benchmarkemaillite_cachefile;
		$fw = fopen($benchmarkemaillite_cachefile, 'a');
		$string = "\"$list\",\"$email\",\"$first\",\"$last\"\n";
		fwrite($fw, $string);
		fclose($fw);
		$_POST['subscribe_first'][$key] = '';
		$_POST['subscribe_last'][$key] = '';
		$_POST['subscribe_email'][$key] = '';
		return '<p class="successmsg">' . __('Successfully Queued Subscription.') . '</p>';
	}

	// Attempt to Connect to Benchmark Email
	function bme_connect() {
		global $benchmarkemaillite_client, $benchmarkemaillite_apiurl;
		require_once(ABSPATH . WPINC . '/class-IXR.php');
		if (!class_exists(IXR_Client)) {
			return '<p class="errormsg">' . __('Error: Unable to access the IXR Client library file: class-IXR.php.') . '</p>';
		}
		$benchmarkemaillite_client = new IXR_Client($benchmarkemaillite_apiurl);
		return true;
	}

	// Locate List to Subscribe Onto
	function bme_list($list) {
		global $benchmarkemaillite_client, $benchmarkemaillite_token, $benchmarkemaillite_listid;
		$benchmarkemaillite_client->query('listGet', $benchmarkemaillite_token, '', 1, 100, 'name', 'asc');
		if ($benchmarkemaillite_client->isError()) {
			return '<p class="errormsg">Error: [L] ' . $benchmarkemaillite_client->getErrorMessage() . '</p>';
		}
		$lists = $benchmarkemaillite_client->getResponse();
		foreach ($lists as $listdata) {
			if (strtolower($listdata['listname']) == strtolower($list)) {
				$benchmarkemaillite_listid = $listdata['id'];
				return true;
			}
		}
		return '<p class="errormsg">' . __('Error: Unable to locate the specified list.') . '</p>';
	}

	// Process Subscription Cache
	function bme_upload($key) {
		global $benchmarkemaillite_cachefile;
		if (!file_exists($benchmarkemaillite_cachefile)) { return false; }
		if (($fr = fopen($benchmarkemaillite_cachefile, 'r')) !== false) {
			while (($data = fgetcsv($fr)) !== false) {
				if (self::bme_list($data[0]) === true) {
					self::bme_subscribe($data[1], $data[2], $data[3], $key);
				}
			}
			fclose($fr);
			unlink($benchmarkemaillite_cachefile);
		}
	}

	// Get Existing Subscriber Data
	function bme_find($email) {
		global $benchmarkemaillite_client, $benchmarkemaillite_token, $benchmarkemaillite_listid;
		$benchmarkemaillite_client->query(
			'listGetContacts', $benchmarkemaillite_token, $benchmarkemaillite_listid, $email, 1, 100, 'name', 'asc'
		);
		if ($benchmarkemaillite_client->isError()) {
			return '<p class="errormsg">Error: [C] ' . $benchmarkemaillite_client->getErrorMessage() . '</p>';
		}
		$data = $benchmarkemaillite_client->getResponse();
		return ($data[0]) ? $data[0]['id'] : false;
	}

	// Add or Update Subscriber
	function bme_subscribe($email, $first, $last, $key) {
		global $benchmarkemaillite_client, $benchmarkemaillite_token, $benchmarkemaillite_listid;

		// Check for Subscription Preexistance
		$contactID = self::bme_find($email);
		if (!is_numeric($contactID) && $contactID != false) { return $contactID; }

		// Doesn't Pre-Exist, Add New Subscription
		if (!is_numeric($contactID)) {
			$type = 'Added';
			$benchmarkemaillite_client->query(
				'listAddContacts', $benchmarkemaillite_token, $benchmarkemaillite_listid, array(
					array('email' => $email, 'firstname' => $first, 'lastname' => $last)
				)
			);
			if ($benchmarkemaillite_client->isError()) {
				return '<p class="errormsg">Error: [A] ' . $benchmarkemaillite_client->getErrorMessage() . '</p>';
			}
			$_POST['subscribe_first'][$key] = '';
			$_POST['subscribe_last'][$key] = '';
			$_POST['subscribe_email'][$key] = '';
			return '<p class="successmsg">' . __('Successfully Added Subscription.') . '</p>';
		}

		// Or Update Preexisting Subscription
		$benchmarkemaillite_client->query(
			'listUpdateContactDetails', $benchmarkemaillite_token, $benchmarkemaillite_listid, $contactID, array(
				'firstname' => $first, 'lastname' => $last
			)
		);
		if ($benchmarkemaillite_client->isError()) {
			return 'Error: [U] ' . $benchmarkemaillite_client->getErrorMessage();
		}
		$_POST['subscribe_first'][$key] = '';
		$_POST['subscribe_last'][$key] = '';
		$_POST['subscribe_email'][$key] = '';
		return '<p class="successmsg">' . __('Successfully Updated Subscription.') . '</p>';
	}
}

?>