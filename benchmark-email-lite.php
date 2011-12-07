<?php
/*
Plugin Name: Benchmark Email Lite
Plugin URI: http://www.beautomated.com/benchmark-email-lite/
Description: A plugin to create a Benchmark Email newsletter widget in WordPress.
Version: 1.1
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
	static $version = '1.1';
	static $apiurl = 'http://api.benchmarkemail.com/1.0/';
	static $linkaffiliate = 'http://www.benchmarkemail.com/?p=68907';
	static $linkcontact = 'http://www.beautomated.com/contact/';
	static $listid = false;
	static $client = false;
	static $token = false;
	static $response = array();

	// Class Constructor
	function benchmarkemaillite_widget() {
		$widget_ops = array(
			'classname' => 'benchmarkemaillite_widget',
			'description' => __('Create a Benchmark Email newsletter widget in WordPress.')
		);
		$control_ops = array('width' => 400);
		$this->WP_Widget('benchmarkemaillite_widget', 'Benchmark Email Lite', $widget_ops, $control_ops);
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
		self::$token = sanitize_title($_POST['bmetoken']);
		self::bme_connect();
		$status = self::bme_list(esc_attr($_POST['bmelist']));
		$field = esc_attr($_POST['bmefield']);
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
			$widgetid = esc_attr($_POST['subscribe_key']);
			$instance = $instance[$widgetid];
			self::$token = $instance['token'];

			// Sanitize Submission
			$first = (array_key_exists('subscribe_first', $_POST) && ($val = $_POST['subscribe_first'][$widgetid]))
				? esc_attr($val) : '';
			$last = (array_key_exists('subscribe_last', $_POST) && ($val = $_POST['subscribe_last'][$widgetid]))
				? esc_attr($val) : '';
			$email = (array_key_exists('subscribe_email', $_POST) && ($val = $_POST['subscribe_email'][$widgetid]))
				? sanitize_email($val) : '';

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
			'title' => __('Subscribe to Newsletter'),
			'button' => __('Subscribe'),
			'description' => __('Get the latest news and information direct from us to you!'),
			'page' => '',
			'token' => '',
			'list' => '',
			'filter' => 1,
			'showname' => 0,
		);
		$instance = wp_parse_args((array) $instance, $defaults);
		$instance['id'] = $this->id;
		require('admin.html.php');
	}

	// Save the Widget Settings
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['page'] = absint($new_instance['page']);
		$instance['token'] = sanitize_title($new_instance['token']);
		$instance['list'] = esc_attr($new_instance['list']);
		$instance['description'] = wp_kses_post($new_instance['description']);
		$instance['button'] = esc_attr($new_instance['button']);
		$instance['filter'] = ($new_instance['filter']) ? 1 : 0;
		$instance['showname'] = ($new_instance['showname']) ? 1 : 0;
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

		// Prepopulate Standard Fields If Logged In
		$user = wp_get_current_user();
		$first = ($user->ID) ? $user->user_firstname : '';
		$last = ($user->ID) ? $user->user_lastname : '';
		$email = ($user->ID) ? $user->user_email : '';

		// Exclude from Pages/Posts Per Setting
		if ($instance['page'] && $instance['page'] != $post->ID) { return; }

		// Skip Output If Widget Is Not Yet Setup
		if (empty($instance['token']) || empty($instance['list'])) { return; }

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
				$first = (array_key_exists('subscribe_first', $_POST) && ($val = $_POST['subscribe_first'][$widgetid]))
					? esc_attr($val) : '';
				$last = (array_key_exists('subscribe_last', $_POST) && ($val = $_POST['subscribe_last'][$widgetid]))
					? esc_attr($val) : '';
				$email = (array_key_exists('subscribe_email', $_POST) && ($val = $_POST['subscribe_email'][$widgetid]))
					? sanitize_email($val) : '';

			// If Submission Without Errors, Output Response Without Form
			} else {
				echo "{$before_widget}{$printresponse}{$after_widget}";
				return;
			}
		}

		// Output Widget
		$data = array(
			'widgetid' => $widgetid,
			'first' => $first,
			'last' => $last,
			'email' => $email,
			'printresponse' => $printresponse,
			'description' => ($instance['filter'] == 1)
				? wpautop($instance['description']) : $instance['description'],
			'button' => $instance['button'],
			'showname' => ($instance['showname'] == 1) ? true : false,
		);
		$widget = self::require_to_var($data, 'widget.html.php');
		echo "{$before_widget}{$widget}{$after_widget}";
	}

	/******************
	 SUBSCRIPTION LOGIC
	 ******************/

	// Main Subscription Logic
	function processsubscription($listname, $email, $first, $last) {

		// Check for Missing or Invalid Email Address
		if (!$email || !is_email($email)) {
			return array(false, __('Error: Please enter a valid email address.'));
		}

		// Try to Open Benchmark Email Connection and Locate List
		self::bme_connect();
		self::bme_list($listname);

		// Try to Run Live Subscription
		$response = self::bme_subscribe($email, $first, $last);

		// Failover to Queue
		if (!$response[0]) {
			$response = self::queue_subscription($listname, $email, $first, $last);
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
		if (!$queue = get_option('benchmarkemaillite_queue')) { return; }
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

	/*******
	 UTILITY
	 *******/

	// Provides Ability to Return Included File Output
	function require_to_var($data, $file) {
		ob_start();
		require($file);
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
		return ob_get_clean();
	}
}

?>