<?php

class benchmarkemaillite_widget extends WP_Widget {
	static $response = array();

	/************************
	 WORDPRESS WIDGET METHODS
	 ************************/

	// Constructor
	function benchmarkemaillite_widget() {
		$widget_ops = array(
			'classname' => 'benchmarkemaillite_widget',
			'description' => __('Create a Benchmark Email newsletter widget in WordPress.', 'benchmark-email-lite')
		);
		$control_ops = array('width' => 400);
		$this->WP_Widget('benchmarkemaillite_widget', 'Benchmark Email Lite', $widget_ops, $control_ops);
	}

	// Build the Widget Settings Form
	function form($instance) {

		// Prepare Default Values
		$defaults = array(
			'title' => __('Subscribe to Newsletter', 'benchmark-email-lite'),
			'button' => __('Subscribe', 'benchmark-email-lite'),
			'description' => __('Get the latest news and information direct from us to you!', 'benchmark-email-lite'),
			'page' => '',
			'list' => '',
			'filter' => 1,
			'showname' => 1,
		);

		// Get Widget ID And Saved Values
		$instance = wp_parse_args((array) $instance, $defaults);
		$instance['id'] = $this->id;

		// Get Drop Down Values
		$options = get_option('benchmark-email-lite_group');
		if (!isset($options[1][0]) || !$options[1][0]) {
			echo benchmarkemaillite_settings::badconfig_message();
			return;
		}
		$dropdown = benchmarkemaillite::print_lists($options[1], $instance['list']);

		// Print Widget
		require('widget.admin.html.php');
	}

	// Save the Widget Settings
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['page'] = absint($new_instance['page']);
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
		if (empty($instance['list'])) { return; }

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
		$description = ($instance['filter'] == 1)
			? wpautop($instance['description']) : $instance['description'];
		$showname = ($instance['showname'] == 1) ? true : false;
		echo $before_widget;
		require('widget.frontend.html.php');
		echo $after_widget;
	}

	/******************
	 SUBSCRIPTION LOGIC
	 ******************/

	// Process Form Submission
	function widgetfrontendsubmission() {

		// Proceed Processing Upon Widget Form Submission
		if (array_key_exists('formid', $_POST) && strstr($_POST['formid'], 'benchmark-email-lite')) {

			// Get Widget Options for this Instance
			$instance = get_option('widget_benchmarkemaillite_widget');
			$widgetid = esc_attr($_POST['subscribe_key']);
			$instance = $instance[$widgetid];

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

	// Main Subscription Logic
	function processsubscription($bmelist, $email, $first, $last) {
		list(benchmarkemaillite_api::$token, $listname, benchmarkemaillite_api::$listid) = explode('|', $bmelist);

		// Check for Missing or Invalid Email Address
		if (!$email || !is_email($email)) {
			return array(false, __('Error: Please enter a valid email address.', 'benchmark-email-lite'));
		}

		// Try to Run Live Subscription
		$response = benchmarkemaillite_api::subscribe($email, $first, $last);

		// Failover to Queue
		if (!$response[0]) {
			$response = self::queue_subscription($bmelist, $email, $first, $last);
		}
		return $response;
	}

	// Queue Subscription
	function queue_subscription($list, $email, $first, $last) {
		$queue = get_option('benchmarkemaillite_queue');
		$queue .= "$list||$email||$first||$last\n";
		update_option('benchmarkemaillite_queue', $queue);

		// Load Queue File into WP Cron
		if (!wp_next_scheduled('benchmarkemaillite_queue')) {
			wp_schedule_single_event(time()+300, 'benchmarkemaillite_queue');
		}
		return array(true, __('Successfully queued subscription.', 'benchmark-email-lite'));
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
			self::processsubscription($row[0], $row[1], $row[2], $row[3]);
		}
	}
}

?>