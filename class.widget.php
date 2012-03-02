<?php

class benchmarkemaillite_widget extends WP_Widget {
	static $response = array();

	// Load JavaScript Into Header On Widgets Page
	function loadjs() {
		global $pagenow;
		if ($pagenow == 'widgets.php') {
			wp_enqueue_script(
				'benchmarkemaillite_widgetadmin',
				plugins_url('widget.admin.js', __FILE__),
				array(), false, false
			);
		}
	}

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
		$fields = array(
			'First Name','Last Name','Middle Name',
			'Address','City','State','Zip','Country','Phone','Fax','Cell Phone',
			'Company Name','Job Title','Business Phone','Business Fax',
			'Business Address','Business City','Business State','Business Zip','Business Country',
			'Notes','Extra 1','Extra 2','Extra 3','Extra 4','Extra 5','Extra 6',
		);

		// Prepare Default Values
		$defaults = array(
			'title' => __('Subscribe to Newsletter', 'benchmark-email-lite'),
			'button' => __('Subscribe', 'benchmark-email-lite'),
			'description' => __('Get the latest news and information direct from us to you!', 'benchmark-email-lite'),
			'page' => '',
			'list' => '',
			'filter' => 0,
			'fields' => array('First Name', 'Last Name', 'Email'),
			'fields_labels' => array('First Name', 'Last Name', 'Email'),
			'fields_required' => array(0, 0, 1),
		);

		// Get Widget ID And Saved Values
		$instance = wp_parse_args((array) $instance, $defaults);
		$instance['id'] = $this->id;

		// Get Drop Down Values
		$options = get_option('benchmark-email-lite_group');
		if (!isset($options[1][0]) || !$options[1][0]) {
			echo benchmarkemaillite_settings::badconfig_message();
			//return;
		}
		$dropdown = benchmarkemaillite::print_lists($options[1], $instance['list']);

		// Insert "Add New" Hidden Row
		array_unshift($instance['fields'], '');
		array_unshift($instance['fields_labels'], '');
		array_unshift($instance['fields_required'], 0);

		// Print Widget
		require('widget.admin.html.php');
	}

	// Save the Widget Settings
	function update($submitted, $instance) {

		// Sanitize Admin Submitted Fields
		$instance['fields'] = array();
		$instance['fields_labels'] = array();
		$instance['fields_required'] = array();
		foreach ($submitted['fields'] as $key => $val) {
			if ($key == 'INSERT-KEY') { continue; }
			$instance['fields'][$key] = esc_attr($submitted['fields'][$key]);
			$instance['fields_labels'][$key] = esc_attr($submitted['fields_labels'][$key]);
			$instance['fields_required'][$key] = isset($submitted['fields_required'][$key]) ? 1 : 0;
		}

		// Sanitize Other Admin Submissions
		$instance['title'] = esc_attr($submitted['title']);
		$instance['page'] = absint($submitted['page']);
		$instance['list'] = esc_attr($submitted['list']);
		$instance['description'] = wp_kses_post($submitted['description']);
		$instance['button'] = esc_attr($submitted['button']);
		$instance['filter'] = ($submitted['filter']) ? 1 : 0;
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

			// If Submission Without Errors, Output Response Without Form
			if (isset(self::$response[$widgetid][0])) {
				echo "{$before_widget}{$printresponse}{$after_widget}";
				return;
			}
		}

		// Output Widget
		$description = ($instance['filter'] == 1)
			? wpautop($instance['description']) : $instance['description'];
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
			$data = array();
			foreach ($instance['fields'] as $key => $field) {
				$fieldslug = sanitize_title($field);
				$id = "{$fieldslug}-{$key}-{$widgetid}";
				$data[$field] = isset($_POST[$id]) ? esc_attr($_POST[$id]) : '';
			}

			// Run Subscription
			self::$response[$widgetid] = self::processsubscription($instance['list'], $data);
		}
	}

	// Main Subscription Logic
	function processsubscription($bmelist, $data) {
		list(benchmarkemaillite_api::$token, $listname, benchmarkemaillite_api::$listid) = explode('|', $bmelist);

		// Check for Missing or Invalid Email Address
		if (!isset($data['Email']) || !is_email($data['Email'])) {
			return array(false, __('Error: Please enter a valid email address.', 'benchmark-email-lite'));
		}

		// Try to Run Live Subscription
		$response = benchmarkemaillite_api::subscribe($data);

		// Failover to Queue
		if (!$response[0]) { $response = self::queue_subscription($bmelist, $data); }
		return $response;
	}

	// Queue Subscription
	function queue_subscription($bmelist, $data) {
		$queue = get_option('benchmarkemaillite_queue');
		$data = is_serialized($data) ? $data : serialize($data);
		$queue .= "{$bmelist}||{$data}\n";
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
			$row[1] = is_serialized($row[1]) ? unserialize($row[1]) : $row[1];
			self::processsubscription($row[0], $row[1]);
		}
	}
}

?>