<?php

class benchmarkemaillite_settings {
	static $linkaffiliate = 'http://www.benchmarkemail.com/Register?p=68907';
	static $linkcontact = 'http://www.beautomated.com/contact/';

	// Good Key Or Connection Message
	function goodconnection_message() {
		return __( 'Valid API key and API server connection.', 'benchmark-email-lite' );
	}

	// Bad Key Or Connection Message
	function badconnection_message() {
		return __( 'Invalid API key or API server connection problem.', 'benchmark-email-lite' );
	}

	/***************
	 WP Hook Methods
	 ***************/

	// Administrative Links
	function plugin_row_meta( $links, $file ) {
		if( basename( $file ) == basename( __FILE__ ) ) {
			$link = '<a href="' . self::$linkcontact . '">'
				. __( 'Contact Developer', 'benchmark-email-lite' ) . '</a>';
			array_unshift( $links, $link );
			$link = '<a href="' . self::$linkaffiliate . '">'
				. __( 'Free 30 Day Benchmark Email Trial', 'benchmark-email-lite' ) . '</a>';
			array_unshift( $links, $link );
		}
		return $links;
	}

	// Admin Area Notices
	function admin_notices() {
		if ( $val = get_transient( 'benchmark-email-lite_error' ) ) {
			echo "
				<div class='error'>
					<p><strong>Benchmark Email Lite</strong></p>
					<p>{$val}</p>
				</div>
			";
			delete_transient( 'benchmark-email-lite_error' );
		}
		if ( $val = get_transient( 'benchmark-email-lite_updated' ) ) {
			echo "
				<div class='updated fade'>
					<p><strong>Benchmark Email Lite</strong></p>
					<p>{$val}</p>
				</div>
			";
			delete_transient( 'benchmark-email-lite_updated' );
		}
	}

	// Bad Configuration Message
	function badconfig_message() {
		return 
			__( 'Please configure your API key(s) on the', 'benchmark-email-lite' )
			. ' <a href="admin.php?page=benchmark-email-lite-settings">'
			. __( 'Benchmark Email Lite settings page', 'benchmark-email-lite' )
			. '.</a>';
	}

	// Triggered By Front And Back Ends - Try To Upgrade Plugin and Widget Settings
	// This Exists Because WordPress Sadly Doesn't Fire Activation Hook Upon Upgrade Reactivation
	function init() {
		$options = get_option( 'benchmark-email-lite_group' );

		// Check And Set Defaults
		if( ! isset( $options[1] ) ) { $options[1] = array(); }
		if( ! isset( $options[2] ) ) { $options[2] = 'yes'; }
		if( ! isset( $options[3] ) ) { $options[3] = 'simple'; }
		if( ! isset( $options[4] ) ) { $options[4] = ''; }
		if( ! isset( $options[5] ) ) { $options[5] = 10; }
		update_option( 'benchmark-email-lite_group', $options );

		// Exit If Already Configured
		if( isset( $options[1][0] ) && $options[1][0] ) { return; }

		// Search For v1.x Widgets, Gather API Keys For Plugin Settings
		$tokens = array();
		$widgets = get_option( 'widget_benchmarkemaillite_widget' );
		if( is_array( $widgets ) ) {
			foreach( $widgets as $instance => $widget ) {
				if( isset( $widget['token'] ) && $widget['token'] != '' ) {
					$tokens[] = $widget['token'];

					// Update List Selection In Widget
					benchmarkemaillite_api::$token = $widget['token'];
					$lists = benchmarkemaillite_api::lists();
					if( ! is_array( $lists ) ) { continue; }
					foreach( $lists as $list ) {
						if( $list['listname'] == $widget['list'] ) {
							$widgets[$instance]['list']
								= "{$widget['token']}|{$widget['list']}|{$list['id']}";
						}
					}
				}
			}
			update_option( 'widget_benchmarkemaillite_widget', $widgets );
		}

		// Gather Preexisting API Keys
		if( isset( $options[1][0] ) ) { $tokens = array_merge( $tokens, $options[1] ); }
		$tokens = array_unique($tokens);
		update_option(
			'benchmark-email-lite_group', array(
				1 => $tokens, 2 => $options[2], 3 => $options[3], 4 => $options[4], 5 => $options[5],
			)
		);

		// Vendor Handshake With Benchmark Email
		benchmarkemaillite_api::handshake( $tokens );

		// Search For v2.0.x Widgets And Upgrade To 2.1
		$widgets = get_option( 'widget_benchmarkemaillite_widget' );
		if( ! is_array( $widgets ) ) { return; }
		$changed = false;
		foreach ( $widgets as $instance => $widget ) {
			if ( ! is_array( $widget ) || isset( $widget['fields'] ) ) { continue; }
			$changed = true;
			if ( isset( $widget['showname'] ) && $widget['showname'] != '1' ) {
				$widgets[$instance]['fields'] = array( 'Email' );
				$widgets[$instance]['fields_labels'] = array( 'Email' );
				$widgets[$instance]['fields_required'] = array( 1 );
			} else {
				$widgets[$instance]['fields'] = array( 'First Name', 'Last Name', 'Email' );
				$widgets[$instance]['fields_labels'] = array( 'First Name', 'Last Name', 'Email' );
				$widgets[$instance]['fields_required'] = array( 0, 0, 1 );
			}
		}
		if ( $changed ) { update_option( 'widget_benchmarkemaillite_widget', $widgets ); }
	}

	// Admin Load
	function admin_init() {

		// Handle Force Reconnection
		if( isset( $_POST['force_reconnect'] ) ) {
			delete_transient( 'benchmark-email-lite_serverdown' );
		}

		// Admin Settings Notice
		$options = get_option( 'benchmark-email-lite_group' );
		if ( ! isset( $options[1][0] ) || ! $options[1][0] ) {
			set_transient( 'benchmark-email-lite_error', self::badconfig_message() );
		}

		// Load Settings API
		register_setting(
			'benchmark-email-lite_group',
			'benchmark-email-lite_group',
			array( 'benchmarkemaillite_settings', 'validate' )
		);
		add_settings_section(
			'benchmark-email-lite_section1',
			__( 'Benchmark Email Credentials', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'section1' ),
			__FILE__
		);
		add_settings_section(
			'benchmark-email-lite_section2',
			__( 'New Email Campaign Preferences', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'section2' ),
			__FILE__
		);
		add_settings_section(
			'benchmark-email-lite_section3',
			__( 'Diagnostics', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'section3' ),
			__FILE__
		);
		add_settings_field(
			'benchmark-email-lite_1',
			__( 'API Key(s) from your Benchmark Email account(s)', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'field1' ),
			__FILE__,
			'benchmark-email-lite_section1'
		);
		add_settings_field(
			'benchmark-email-lite_2',
			__( 'Webpage version', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'field2' ),
			__FILE__,
			'benchmark-email-lite_section2'
		);
		add_settings_field(
			'benchmark-email-lite_4',
			'', 
			array( 'benchmarkemaillite_settings', 'field4' ),
			__FILE__,
			'benchmark-email-lite_section2'
		);
		add_settings_field(
			'benchmark-email-lite_3',
			'', array( 'benchmarkemaillite_settings', 'field3' ),
			__FILE__,
			'benchmark-email-lite_section2'
		);
		add_settings_field(
			'benchmark-email-lite_5',
			__( 'Connection Timeout (seconds)', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'field5' ),
			__FILE__,
			'benchmark-email-lite_section3'
		);
	}

	// Admin Menu
	function admin_menu() {
		add_menu_page(
			'Benchmark Email Lite',
			'BenchmarkEmail',
			'manage_options',
			'benchmark-email-lite',
			'',
			plugin_dir_url( __FILE__ ) . '../favicon.png'
		);
		add_submenu_page(
			'benchmark-email-lite',
			'Benchmark Email Lite Reports',
			'Reports',
			'manage_options',
			'benchmark-email-lite',
			array( 'benchmarkemaillite_settings', 'page' )
		);
		add_submenu_page(
			'benchmark-email-lite',
			'Benchmark Email Lite Settings',
			'Settings',
			'manage_options',
			'benchmark-email-lite-settings',
			array( 'benchmarkemaillite_settings', 'page' )
		);
	}

	// Plugins Page Settings Link
	function plugin_action_links( $links ) {
		$links['settings'] = '<a href="admin.php?page=benchmark-email-lite-settings">'
			. __( 'Settings', 'benchmark-email-lite' ) . '</a>';
		return $links;
	}


	/********************
	 Settings API Methods
	 ********************/

	function page() {
		$options = get_option( 'benchmark-email-lite_group' );
		$tabs = array(
			'benchmark-email-lite' => __( 'Reports', 'benchmark-email-lite' ),
			'benchmark-email-lite-settings' => __( 'Settings', 'benchmark-email-lite' ),
		);
		$current = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : 'benchmark-email-lite';
		require( dirname( __FILE__ ) . '/../views/settings.html.php');
	}
	function print_settings() {
		echo '<form action="options.php" method="post">';
		settings_fields( 'benchmark-email-lite_group' );
		do_settings_sections( __FILE__ );
		echo '<p><input name="Submit" type="submit" class="button-primary" value="Save Changes" /></p></form>';
	}
	function section1() {
		echo '<p>' . __( 'The API Key(s) connect your WordPress site with your Benchmark Email account(s).', 'benchmark-email-lite' ) . ' '
			. __( 'Only one key is required per Benchmark Email account.', 'benchmark-email-lite' ) . ' '
			. __( 'API Key(s) may expire after one year.', 'benchmark-email-lite' ) . '</p>'
			. '<p><a href="' . self::$linkaffiliate . '" target="BenchmarkEmail">'
			. __( 'Signup for a 30-day FREE Trial', 'benchmark-email-lite') . '</a>, ' . __( 'or', 'benchmark-email-lite' )
			. ' <a href="http://ui.benchmarkemail.com/EditSetting#_ctl0_ContentPlaceHolder1_UC_ClientSettings1_lnkGenerate" target="BenchmarkEmail">'
			. __( 'log in to Benchmark Email to get your API key', 'benchmark-email-lite' ) . '</a>.</p>';
	}
	function section2() { }
	function section3() {
		echo '<p style="color:red;">' . __( 'This section is for troubleshooting purposes only.', 'benchmark-email-lite' ) . '</p>';
	}
	function field1() {
		$options = get_option( 'benchmark-email-lite_group' );
		$results = array();
		$key = $options[1];
		for ( $i = 0; $i < 5; $i ++ ) {
			$key[$i] = isset( $key[$i] ) ? $key[$i] : '';
			if ( ! $key[$i] ) { $results[$i] = '<img style="vertical-align:middle;opacity:0;" src="images/yes.png" alt="" width="16" height="16" />'; }
			else {
				benchmarkemaillite_api::$token = $key[$i];
				$results[$i] = ( is_array( benchmarkemaillite_api::lists() ) )
					? '<img style="vertical-align:middle;" src="images/yes.png" alt="Yes" title="'
						. self::goodconnection_message() . '" width="16" height="16" />'
					: '<img style="vertical-align:middle;" style="" src="images/no.png" alt="No" title="'
						. self::badconnection_message() . '" width="16" height="16" />';
			}
		}
		echo "{$results[0]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[0]}' /> Primary<br />
			{$results[1]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[1]}' /> Optional<br />
			{$results[2]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[2]}' /> Optional <br />
			{$results[3]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[3]}' /> Optional<br />
			{$results[4]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[4]}' /> Optional";
	}
	function field2() {
		$options = get_option( 'benchmark-email-lite_group' );
		echo "<input id='benchmark-email-lite_group_2' type='checkbox' name='benchmark-email-lite_group[2]'
			value='yes'" . checked( 'yes', $options[2], false ) . " /> "
			. __("Include the sentence &quot;Having trouble viewing this email? <u>click here</u>&quot; in the top of emails?", 'benchamrk-email-lite');
	}
	function field3() { // Design Template - This Field Is Disabled
		echo "<input id='benchmark-email-lite_group_3' type='hidden' name='benchmark-email-lite_group[3]'
			value='simple' checked='checked' />";
	}
	function field4() { // Permission Reminder - This Field Is Disabled
		echo "<input id='benchmark-email-lite_group_4' type='hidden' name='benchmark-email-lite_group[4]'
			value='' checked='checked' />";
	}
	function field5() {
		$options = get_option( 'benchmark-email-lite_group' );
		echo __( 'If the connection with the Benchmark Email server takes', 'benchmark-email-lite' )
			. " <input id='benchmark-email-lite_group_5' type='text' size='2' maxlength='2' name='benchmark-email-lite_group[5]' value='{$options[5]}' /> "
			. __( 'seconds or longer, disable connections for 5 minutes to prevent site administration from becoming sluggish. (Default: 10)', 'benchmark-email-lite' );
	}
	function validate( $values ) {
		$options = get_option( 'benchmark-email-lite_group' );
		foreach( $options as $key => $val ) {
			$val = isset( $values[$key] ) ? $values[$key] : '';

			// Process Saving Of API Keys
			if( $key == '1' ) {

				// Unserialize API Keys
				$values[1] = maybe_unserialize( $val );

				// Remove Empties
				$values[1] = array_filter( $values[1] );

				// Remove Duplicates
				$values[1] = array_unique( $values[1] );

				// Reset Keys
				$values[1] = array_values( $values[1] );

				// Remove Any Previous Errors
				if( $values[1] ) { delete_transient( 'benchmark-email-lite_error' ); }

				// Vendor Handshake With Benchmark Email
				benchmarkemaillite_api::handshake( $values[1] );

				// Modify Widgets of Deleted API Keys
				$widgets = get_option( 'widget_benchmarkemaillite_widget' );
				$sidebars_widgets = get_option( 'sidebars_widgets' );
				if( ! is_array( $widgets ) ) { conntinue; }
				foreach( $widgets as $instance => $widget ) {
					$widget_id = "benchmarkemaillite_widget-{$instance}";
					if( ! is_array( $widget ) || ! isset( $widget['list'] ) ) { continue; }
					$list = explode( '|', $widget['list'] );
					if( ! in_array( $list[0], $values[1] ) ) {

						// Deactivate The Widget
						$delete = array();
						foreach( $sidebars_widgets as $index1 => $sidebar_widgets ) {
							if( ! is_array( $sidebar_widgets ) ) { continue; }
							$index2 = array_search( $widget_id, $sidebar_widgets );
							if( $index2 !== false ) {
								$delete[] = array( $index1, $index2 );
							}
						}
						foreach( $delete as $todo ) {
							list( $index1, $index2 ) = $todo;
							unset( $sidebars_widgets[$index1][$index2] );
							$sidebars_widgets['wp_inactive_widgets'][] = $widget_id;
						}
						update_option( 'sidebars_widgets', $sidebars_widgets );

						// Inform About Deactivation
						set_transient(
							'benchmark-email-lite_updated',
							sprintf(
								__( 'We moved %d widget(s) of no longer existing API keys to your Inactive Widgets sidebar.', 'benchmark-email-lite' ),
								sizeof( $delete )
							)
						);
					}
				}
				update_option( 'widget_benchmarkemaillite_widget', $widgets );
			}

			// Sanitize Non Array Settings
			else { $values[$key] = esc_attr( $val ); }
		}
		return $values;
	}
}

?>
