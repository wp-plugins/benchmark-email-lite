<?php

class benchmarkemaillite_posts {

	// Create Pages+Posts Metaboxes
	function post_metabox() {
		add_meta_box(
			'benchmark-email-lite',
			'Benchmark Email Lite',
			array('benchmarkemaillite_posts', 'metabox'),
			'post',
			'side',
			'default'
		);
		add_meta_box(
			'benchmark-email-lite',
			'Benchmark Email Lite',
			array('benchmarkemaillite_posts', 'metabox'),
			'page',
			'side',
			'default'
		);
	}

	// Page+Post Metabox Contents
	function metabox() {
		global $post;

		// Get Values For Form Prepopulations
		$user = wp_get_current_user();
		$email = isset($user->user_email) ? $user->user_email : get_bloginfo('admin_email');
		$bmelist = ($val = get_transient('bmelist')) ? esc_attr($val) : '';
		$title = ($val = get_transient('bmetitle')) ? esc_attr($val) : '';
		$from = ($val = get_transient('bmefrom')) ? esc_attr($val) : '';
		$subject = ($val = get_transient('bmesubject')) ? esc_attr($val) : '';
		$email = ($val = get_transient('bmetestto')) ? sanitize_email($val) : $email;

		// Open Benchmark Email Connection and Locate List
		$options = get_option('benchmark-email-lite_group');
		if (!isset($options[1][0]) || !$options[1][0]) {
			echo benchmarkemaillite_settings::badconfig_message();
			return;
		}
		$dropdown = benchmarkemaillite::print_lists($options[1], $bmelist);

		// Round Time To Nearest Quarter Hours
		$localtime = current_time('timestamp');
		$minutes = date('i', $localtime);
		$newminutes = ceil($minutes / 15) * 15;
		$localtime_quarterhour = $localtime + 60 * ($newminutes - $minutes);

		// Get Timezone String
		$tz = ($val = get_option('timezone_string')) ? $val : 'UTC';
		$dateTime = new DateTime();
		$dateTime->setTimeZone(new DateTimeZone($tz));
		$localtime_zone = $dateTime->format('T');

		// Output Form
		require('metabox.html.php');
	}

	// Called when Adding, Creating or Updating any Page+Post
	function save_post($postID) {

		// Set Variables
		$bmelist = isset($_POST['bmelist']) ? esc_attr($_POST['bmelist']) : false;
		if ($bmelist) {
			list(benchmarkemaillite_api::$token, $listname, benchmarkemaillite_api::$listid)
				= explode('|', $bmelist);
		}
		$bmetitle = isset($_POST['bmetitle']) ? stripslashes(strip_tags($_POST['bmetitle'])) : false;
		$bmefrom = isset($_POST['bmefrom']) ? stripslashes(strip_tags($_POST['bmefrom'])) : false;
		$bmesubject = isset($_POST['bmesubject']) ? stripslashes(strip_tags($_POST['bmesubject'])) : false;
		$bmeaction = isset($_POST['bmeaction']) ? esc_attr($_POST['bmeaction']) : false;
		$bmetestto = isset($_POST['bmetestto']) ? sanitize_email($_POST['bmetestto']) : false;

		// Handle Prepopulation Loading
		if ($bmeaction == '1') {
			set_transient('bmelist', $bmelist, 15);
			set_transient('bmeaction', $bmeaction, 15);
			set_transient('bmetitle', $bmetitle, 15);
			set_transient('bmefrom', $bmefrom, 15);
			set_transient('bmesubject', $bmesubject, 15);
			set_transient('bmetestto', $bmetestto, 15);
		}

		// Don't Work With Post Revisions Or Other Post Actions
		if (wp_is_post_revision($postID) || !isset($_POST['bmesubmit']) || $_POST['bmesubmit'] != 'yes') { return; }

		// Get User Info
		if (!$user = wp_get_current_user()) { return; }
		$user = get_userdata($user->ID);
		$name = isset($user->first_name) ? $user->first_name : '';
		$name .= isset($user->last_name) ? ' ' . $user->last_name : '';
		$name = trim($name);

		// Get Post Info
		if (!$post = get_post($postID)) { return; }

		// Prepare Campaign Data
		$data = array(
			'title' => $post->post_title,
			'body' => apply_filters('the_content', $post->post_content),
		);
		$options = get_option('benchmark-email-lite_group');
		switch ($options[3]) {
			case 'theme': $themefile = get_permalink($postID); break;
			default: $themefile = 'templates/simple.html.php';
		}
		$body = benchmarkemaillite::require_to_var($data, $themefile, true);
		$webpageVersion = ($options[2] == 'yes') ? true : false;
		$permissionMessage = isset($options[4]) ? $options[4] : '';

		// Create Campaign
		$result = benchmarkemaillite_api::campaign(
			$bmetitle, $bmefrom, $bmesubject, $body, $webpageVersion, $permissionMessage
		);

		// Handle Error Conditions
		if ($result == __('preexists', 'benchmark-email-lite')) {
			update_option(
				'benchmark-email-lite_errors',
				__('This campaign was previously sent, therefore it cannot be updated nor sent again. Please choose another email name.', 'benchmark-email-lite')
			);
			return;
		} else if (!is_numeric(benchmarkemaillite_api::$campaignid)) {
			update_option(
				'benchmark-email-lite_errors',
				__('There was a problem creating or updating your email campaign. Please try again later.', 'benchmark-email-lite')
				. (isset(benchmarkemaillite_api::$campaignid['faultString'])
					? ' ' . __('Benchmark Email response code: ', 'benchmark-email-lite') . benchmarkemaillite_api::$campaignid['faultCode'] : '')
			);
			return;
		}

		// Schedule Campaign
		switch ($bmeaction) {
			case '1':
				benchmarkemaillite_api::campaign_test($bmetestto);
				update_option(
					'benchmark-email-lite_errors',
					__('Your campaign', 'benchmark-email-lite') . " <em>{$bmetitle}</em>&nbsp; "
					. __('was successfully', 'benchmark-email-lite') . " {$result}."
				);
				break;
			case '2':
				benchmarkemaillite_api::campaign_now();
				update_option(
					'benchmark-email-lite_errors',
					__('Your campaign', 'benchmark-email-lite') . " <em>{$bmetitle}</em>&nbsp; "
					. __('was successfully sent', 'benchmark-email-lite') . '.'
				);
				break;
			case '3':
				$bmedate = isset($_POST['bmedate'])
					? esc_attr($_POST['bmedate']) : date('d M Y', current_time('timestamp'));
				$bmetime = isset($_POST['bmetime'])
					? esc_attr($_POST['bmetime']) : date('H:i', current_time('timestamp'));
				$when = "$bmedate $bmetime";
				benchmarkemaillite_api::campaign_later($when);
				update_option(
					'benchmark-email-lite_errors',
					__('Your campaign', 'benchmark-email-lite') . ' <em>' . $bmetitle . '</em>&nbsp; '
					. __('was successfully scheduled for', 'benchmark-email-lite') . " <em>{$when}</em>."
				);
		}
	}

	// For Printing Custom Admin Notices
	function custom_errors() {
		if ($val = get_option('benchmark-email-lite_errors')) {
			update_option('benchmark-email-lite_errors', '');
			echo "<div class='fade updated'><p><strong>Benchmark Email Lite</strong></p><p>{$val}</p></div>";
		}
	}
}

?>