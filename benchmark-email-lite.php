<?php
/*
Plugin Name: Benchmark Email Lite
Plugin URI: http://www.beautomated.com/benchmark-email-lite/
Description: A plugin to create a Benchmark Email newsletter widget in WordPress.
Version: 1.0.5
Author: beAutomated
Author URI: http://www.beautomated.com/
License: GPL
*/

// Wordpress API Hooks
add_action('admin_init', array('benchmarkemaillite_widget', 'admin_js'));
add_action('wp_ajax_bmewidget', array('benchmarkemaillite_widget', 'admin_ajax_callback'));
add_action('widgets_init', 'benchmarkemaillite_register_widget');
add_action('widgets_init', array('benchmarkemaillite_widget', 'widgetfrontendsubmission'));
add_action('benchmarkemaillite_queue', array('benchmarkemaillite_widget', 'queue_upload'));
add_filter('plugin_row_meta', array('benchmarkemaillite_widget', 'pluginlinks'), 10, 2);
function benchmarkemaillite_register_widget() { register_widget('benchmarkemaillite_widget'); }

/*********
 PHP CLASS
 *********/

// Widget Class
class benchmarkemaillite_widget extends WP_Widget {

	// Variables Available Without Class Instantiation
	private static $apiurl = 'http://api.benchmarkemail.com/1.0/';
	private static $linkaffiliate = 'http://www.benchmarkemail.com/?p=68907';
	private static $linkcontact = 'http://www.beautomated.com/contact/';
	private static $listid = false;
	private static $client = false;
	private static $token = false;
	private static $response = array();
	private static $version = '1.0.5';

	// Class Constructor
	function benchmarkemaillite_widget() {
		$widget_ops = array(
			'classname' => 'benchmarkemaillite_widget',
			'description' => __('Create a Benchmark Email newsletter widget in WordPress.')
		);
		$this->WP_Widget('benchmarkemaillite_widget', __('Benchmark Email Lite'), $widget_ops);
	}

	/**********************
	 WORDPRESS HOOK METHODS
	 **********************/

	// Admin Area JavaScripts
	function admin_js() {
		wp_deregister_script('benchmark-email-lite');
		wp_register_script(
			'benchmark-email-lite',
			plugins_url() . '/benchmark-email-lite/benchmark-email-lite.admin.js',
			array('jquery'),
			self::$version,
			true
		);
		wp_enqueue_script('benchmark-email-lite');
	}

	// Admin Area AJAX Callback - Open Benchmark Email Connection and Try to Locate List
	function admin_ajax_callback() {
		self::$token = sanitize_text_field($_POST['bmetoken']);
		self::bme_connect();
		$status = self::bme_list(sanitize_text_field($_POST['bmelist']));
		$field = sanitize_text_field($_POST['bmefield']);
		if (strstr($field, 'list')) {
			echo ($status !== true)
				? '<span style="color:red;font-weight:bold;">' . __('Error: Unable to verify the list.') . '</span>'
				: '<span style="color:green;font-weight:bold;">' . __('Successfully verified the list.') . '</span>';
		} else if (strstr($field, 'token')) {
			echo ($status !== true && !$status[0])
				? '<span style="color:red;font-weight:bold;">' . __('Error: Unable to verify the key.') . '</span>'
				: '<span style="color:green;font-weight:bold;">' . __('Successfully verified the key.') . '</span>';
		}
		exit;
	}

	// Administrative Links
	function pluginlinks($links, $file) {
		if (basename($file) == basename(__FILE__)) {
			$link = '<a href="' . self::$linkcontact . '">' . __('Contact Developer') . '</a>';
			array_unshift($links, $link);
			$link = '<a href="' . self::$linkaffiliate . '">' . __('Free 30 Day Benchmark Email Trial') . '</a>';
			array_unshift($links, $link);
		}
		return $links;
	}

	// Process Form Submission
	function widgetfrontendsubmission() {

		// Proceed Processing Upon Widget Form Submission
		if (array_key_exists('formid', $_POST) && strstr($_POST['formid'], 'benchmark-email-lite')) {

			// Get Widget Options for this Instance
			$instance = get_option('widget_benchmarkemaillite_widget');
			$widgetid = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_key'])));
			$instance = $instance[$widgetid];
			self::$token = $instance['token'];

			// Sanitize Submission
			$first = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_first'][$widgetid])));
			$last = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_last'][$widgetid])));
			$email = sanitize_email($_POST['subscribe_email'][$widgetid]);

			// Run Subscription
			self::$response[$widgetid] = self::processsubscription(
				$instance['list'], $email, $first, $last
			);
		}
	}

	/************************
	 WORDPRESS WIDGET METHODS
	 ************************/

	// Build the Widget Settings Form
	function form($instance) {
		$defaults = array(
			'title' => __('Join Our Newsletter'),
			'page' => '',
			'token' => '',
			'list' => ''
		);
		$instance = wp_parse_args((array) $instance, $defaults);
?>
		<p>
			<?php echo __('Widget Title'); ?>
			<input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text"
				value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			<?php echo __('Limit to Page'); ?>
			<?php wp_dropdown_pages(array('depth' => 0, 'child_of' => 0,
				'selected' => $instance['page'], 'echo' => 1, 'name' => $this->get_field_name('page'),
				'show_option_none' => '- ' . __('Show Everywhere') . ' -')); ?>
		</p>
		<p>
			<?php echo __('Your Benchmark Email API Key'); ?>
			<input class="widefat" name="<?php echo $this->get_field_name('token'); ?>" type="text"
				value="<?php echo esc_attr($instance['token']); ?>" id="<?php echo $this->get_field_name('token'); ?>" /><br />
			<span id="<?php echo $this->get_field_name('token'); ?>-response"></span>
		</p>
		<p>
			<a href="<?php echo self::$linkaffiliate; ?>" target="_blank">
			<?php echo __('Signup for a 30-day FREE Trial and support this plugin\'s development!'); ?></a>
		</p>
		<p>
			<?php echo __('Name of Benchmark Email List'); ?>
			<input class="widefat" name="<?php echo $this->get_field_name('list'); ?>" type="text"
				value="<?php echo esc_attr($instance['list']); ?>" id="<?php echo $this->get_field_name('list'); ?>" /><br />
			<span id="<?php echo $this->get_field_name('list'); ?>-response"></span>
		</p>
		<p>
			<input type="button" class="button-primary" value="Verify API Key and List Name"
				onclick="benchmarkemaillite_check('<?php echo $this->get_field_name('token'); ?>', '<?php echo $this->get_field_name('list'); ?>', '<?php echo $this->get_field_name('token'); ?>');benchmarkemaillite_check('<?php echo $this->get_field_name('list'); ?>', '<?php echo $this->get_field_name('list'); ?>', '<?php echo $this->get_field_name('token'); ?>');" />
		</p>
<?php
	}

	// Save the Widget Settings
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['page'] = absint($new_instance['page']);
		$instance['token'] = sanitize_text_field($new_instance['token']);
		$instance['list'] = sanitize_text_field($new_instance['list']);
		return $instance;
	}

	// Display the Widget
	function widget($args, $instance) {

		// Widget Variables
		global $post;
		extract($args);
		$widgetid = explode('-', $widget_id);
		$widgetid = $widgetid[1];
		$before_widget .= '<div id="benchmark-email-lite-' . $widgetid . '" class="benchmark-email-lite">';
		$after_widget = '</div>' . $after_widget;
		$printresponse = '';
		$first = '';
		$last = '';
		$email = '';

		// Exclude from Pages/Posts Per Setting
		if ($instance['page'] && $instance['page'] != $post->ID) { return false; }

		// Output Widget Title, If Exists
		$title = apply_filters('widget_title', $instance['title']);
		if (!empty($title)) { echo $before_title . $title . $after_title; }

		// Display Any Submission Response
		if (is_array(self::$response) && array_key_exists($widgetid, self::$response) && is_array(self::$response[$widgetid])) {
			$printresponse = (self::$response[$widgetid][0])
				? '<p class="successmsg">' . self::$response[$widgetid][1] . '</p>'
				: '<p class="errormsg">' . self::$response[$widgetid][1] . '</p>';

			// If Submission Errors, Sanitize Submission for Form Prepopulation
			if (!self::$response[$widgetid][0]) {
				$first = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_first'][$widgetid])));
				$last = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_last'][$widgetid])));
				$email = stripslashes(sanitize_text_field(str_replace('"', '', $_POST['subscribe_email'][$widgetid])));

			// If Submission Without Errors, Output Response Without Form
			} else {
				echo $before_widget . $printresponse . $after_widget;
				return true;
			}
		}

		// Output Setup Notification, If Widget Not Yet Setup
		if (empty($instance['token']) || empty($instance['list'])) {
			echo $before_widget . '<p class="errormsg">'
				. __('Error: Please configure your Benchmark Email API key and list name.')
				. '</p>' . $after_widget;
			return false;
		}

		// Output Widget Subscription Form
		echo $before_widget . '
<form method="post" action="#benchmark-email-lite-' . $widgetid . '">
<ul style="list-style-type:none;margin:0;">
	<li>
		<input type="hidden" name="formid" value="benchmark-email-lite-' . $widgetid . '" />
		<input type="hidden" name="subscribe_key" value="' . $widgetid . '" />
		<label for="subscribe_first-' . $widgetid . '" style="display:block;">' . __('First Name') . '</label>
		<input type="text" id="subscribe_first-' . $widgetid . '" name="subscribe_first[' . $widgetid . ']" value="' . $first . '" />
	</li>
	<li>
		<label for="subscribe_last-' . $widgetid . '" style="display:block;">' . __('Last Name') . '</label>
		<input type="text" id="subscribe_last-' . $widgetid . '" name="subscribe_last[' . $widgetid . ']" value="' . $last . '" />
	</li>
	<li>
		<label for="subscribe_email-' . $widgetid . '" style="display:block;">' . __('Email Address (required)') . '</label>
		<input type="text" id="subscribe_email-' . $widgetid . '" name="subscribe_email[' . $widgetid . ']" value="' . $email . '" />
	</li>
	<li><input type="submit" value="' . __('Subscribe') . '" onclick="document.getElementById(\'subscribe_spinner-' . $widgetid . '\').style.display=\'block\';this.form.style.display=\'none\';" /></li>
	<li>' . $printresponse . '</li>
</ul>
</form>
<p id="subscribe_spinner-' . $widgetid . '" style="display:none;text-align:center;">
	<br /><img alt="Loading" src="' . plugins_url() . '/benchmark-email-lite/loading.gif" />
	<br />' . __('Loading - Please Wait') . '
</p>
' . $after_widget;
	}

	/**********
	 MAIN LOGIC
	 **********/

	// Main Subscription Logic
	function processsubscription($listname, $email, $first, $last) {
		$response = '';

		// Check for Missing or Invalid Email Address
		if (!$email || !is_email($email)) {
			$response = array(false, __('Error: Please enter a valid email address.'));
		}

		// Valid Email Address
		else {

			// Open Benchmark Email Connection and Try to Locate List
			self::bme_connect();
			$status = self::bme_list($listname);
			if ($status !== true) { $response = $status; }

			// Try to Run Live Subscription
			if (!$response) { $response = self::bme_subscribe($email, $first, $last); }

			// Failover to Queue
			if (!$response[0]) {
				$response = self::queue_subscription($listname, $email, $first, $last);
			}
		}
		return $response;
	}

	// Queue Subscription
	function queue_subscription($list, $email, $first, $last) {
		$queue = get_option('benchmarkemaillite_queue');
		$queue .= "$list||$email||$first||$last||" . self::$token . "\n";
		update_option('benchmarkemaillite_queue', $queue);

		// Load Queue File into WP Cron
		if (!wp_next_scheduled('benchmarkemaillite_queue')) {
			wp_schedule_single_event(time()+300, 'benchmarkemaillite_queue');
		}
		return array(true, __('Successfully queued subscription.'));
	}

	// Process Subscription Queue Cron Request
	function queue_upload() {

		// Continue Only If Queue Exists
		if (!$queue = get_option('benchmarkemaillite_queue')) { return false; }
		delete_option('benchmarkemaillite_queue');

		// Attempt to Subscribe Each Queued Record, Or Fail Back To Queue
		$queue = explode("\n", $queue);
		foreach ($queue as $row) {
			$row = explode('||', $row);
			if (sizeof($row) < 5) { continue; }
			self::$token = $row[4];
			self::processsubscription($row[0], $row[1], $row[2], $row[3]);
		}
	}

	/***************************
	 BENCHMARK EMAIL API METHODS
	 ***************************/

	// Attempt to Connect to Benchmark Email
	function bme_connect() {
		require_once(ABSPATH . WPINC . '/class-IXR.php');
		self::$client = new IXR_Client(self::$apiurl);
	}

	// Locate List to Subscribe Onto
	function bme_list($list) {
		self::$client->query('listGet', self::$token, '', 1, 100, 'name', 'asc');
		if (self::$client->isError()) { return array(false); }
		$lists = self::$client->getResponse();
		foreach ($lists as $listdata) {
			if (strtolower(trim($listdata['listname'])) == strtolower(trim($list))) {
				self::$listid = $listdata['id'];
				return true;
			}
		}
		return $lists;
	}

	// Get Existing Subscriber Data
	function bme_find($email) {
		self::$client->query(
			'listGetContacts', self::$token, self::$listid, $email, 1, 100, 'name', 'asc'
		);
		if (self::$client->isError()) {
			return array(false, __('Error: [Cont] ' . self::$client->getErrorMessage()));
		}
		$data = self::$client->getResponse();
		return (
			is_array($data) && array_key_exists(0, $data)
			&& is_array($data[0]) && array_key_exists('id', $data[0])
		) ? $data[0]['id'] : false;
	}

	// Add or Update Subscriber
	function bme_subscribe($email, $first, $last) {

		// Check for Subscription Preexistance
		$contactID = self::bme_find($email);
		if (!is_numeric($contactID) && $contactID != false) { return $contactID; }

		// Doesn't Pre-Exist, Add New Subscription
		if (!is_numeric($contactID)) {
			self::$client->query(
				'listAddContactsOptin', self::$token, self::$listid, array(
					array('email' => $email, 'First Name' => $first, 'Last Name' => $last)
				), '1'
			);
			if (self::$client->isError()) {
				return array(false, __('Error: [Add] ' . self::$client->getErrorMessage()));
			}
			return array(true, __('A verification email has been sent.'));
		}

		// Or Update Preexisting Subscription
		self::$client->query(
			'listUpdateContactDetails', self::$token, self::$listid, $contactID, array(
				'First Name' => $first, 'Last Name' => $last
			)
		);
		if (self::$client->isError()) {
			return array(false, 'Error: [Updt] ' . self::$client->getErrorMessage());
		}
		return array(true, __('Successfully updated subscription.'));
	}
}

?>