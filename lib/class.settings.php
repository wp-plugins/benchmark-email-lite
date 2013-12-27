<?php

class benchmarkemaillite_settings {
	static
		$linkaffiliate = 'http://www.benchmarkemail.com/Register?p=68907',
		$linkcontact = 'http://www.beautomated.com/contact/';

	// Good Key Or Connection Message
	static function goodconnection_message() {
		return __( 'Valid API key and API server connection.', 'benchmark-email-lite' );
	}

	// Bad Key Or Connection Message
	static function badconnection_message() {
		return __( 'Invalid API key or API server connection problem.', 'benchmark-email-lite' );
	}

	/***************
	 WP Hook Methods
	 ***************/

	// Administrative Links
	static function plugin_row_meta( $links, $file ) {
		if( basename( $file ) == basename( __FILE__ ) ) {
			$link = '<a target="_blank" href="' . self::$linkcontact . '">' . __( 'Contact Developer', 'benchmark-email-lite' ) . '</a>';
			array_unshift( $links, $link );
			$link = '<a target="_blank" href="' . self::$linkaffiliate . '">' . __( 'Free 30 Day Benchmark Email Trial', 'benchmark-email-lite' ) . '</a>';
			array_unshift( $links, $link );
		}
		return $links;
	}

	// Admin Area Notices
	static function admin_notices() {

		// Print Errors
		if ( $val = get_transient( 'benchmark-email-lite_error' ) ) {
			add_settings_error(
				'benchmark-email-lite-notice',
				esc_attr( 'settings_updated' ),
				"<p>Benchmark Email Lite</p><p>{$val}</p>",
				'error'
			);
			delete_transient( 'benchmark-email-lite_error' );
		}

		// Print Updates
		if ( $val = get_transient( 'benchmark-email-lite_updated' ) ) {
			add_settings_error(
				'benchmark-email-lite-notice',
				esc_attr( 'settings_updated' ),
				"<p>Benchmark Email Lite</p><p>{$val}</p>",
				'updated'
			);
			delete_transient( 'benchmark-email-lite_updated' );
		}

		// Settings API Notices
		settings_errors( 'benchmark-email-lite-notice' );
	}

	// Bad Configuration Message
	static function badconfig_message() {
		return 
			__( 'Please configure your API key(s) on the', 'benchmark-email-lite' )
			. ' <a target="_blank" href="admin.php?page=benchmark-email-lite-settings">'
			. __( 'Benchmark Email Lite settings page', 'benchmark-email-lite' )
			. '.</a>';
	}

	// Triggered By Front And Back Ends - Try To Upgrade Plugin and Widget Settings
	// This Exists Because WordPress Sadly Doesn't Fire Activation Hook Upon Upgrade Reactivation
	static function init() {

		// Check And Set Default Settings
		$options = get_option( 'benchmark-email-lite_group' );
		if( ! isset( $options[1] ) ) { $options[1] = array(); }
		if( ! isset( $options[2] ) ) { $options[2] = 'yes'; }
		if( ! isset( $options[3] ) ) { $options[3] = 'simple'; }
		if( ! isset( $options[4] ) ) { $options[4] = ''; }
		if( ! isset( $options[5] ) ) { $options[5] = 10; }
		update_option( 'benchmark-email-lite_group', $options );

		// Check And Set Defaults For Template Settings
		$options_template = get_option( 'benchmark-email-lite_group_template' );
		if( ! isset( $options_template['html'] ) || ! strstr( $options_template['html'], 'BODY_HERE' ) ) {
			$options_template['html'] = implode( '', file( dirname( __FILE__ ) . '/../templates/simple.html.php' ) );
			update_option( 'benchmark-email-lite_group_template', $options_template );
		}

		// Exit If Already Configured
		if( isset( $options[1][0] ) && $options[1][0] ) { return; }

		// Search For v1.x Widgets, Gather API Keys For Plugin Settings
		benchmarkemaillite_widget::upgrade_widgets_1();

		// Gather Preexisting API Keys
		if( isset( $options[1][0] ) ) { $tokens = array_merge( $tokens, $options[1] ); }
		$tokens = array_unique( $tokens );
		update_option(
			'benchmark-email-lite_group', array(
				1 => $tokens, 2 => $options[2], 3 => $options[3], 4 => $options[4], 5 => $options[5],
			)
		);

		// Vendor Handshake With Benchmark Email
		benchmarkemaillite_api::handshake( $tokens );

		// Search For v2.0.x Widgets And Upgrade To 2.1
		benchmarkemaillite_widget::upgrade_widgets_2();
	}

	// Admin Load
	static function admin_init() {

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
		register_setting(
			'benchmark-email-lite_group_template',
			'benchmark-email-lite_group_template',
			array( 'benchmarkemaillite_settings', 'validate' )
		);

		// Settings API Sections Follow
		add_settings_section(
			'benchmark-email-lite-section-main',
			__( 'Benchmark Email Credentials', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'section_main' ),
			'benchmark-email-lite-settings-pg1'
		);
		add_settings_section(
			'benchmark-email-lite-section-regular',
			__( 'New Email Campaign Preferences', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'section_regular' ),
			'benchmark-email-lite-settings-pg1'
		);
		add_settings_section(
			'benchmark-email-lite_section_diagnostics',
			__( 'Diagnostics', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'section_diagnostics' ),
			'benchmark-email-lite-settings-pg1'
		);
		add_settings_section(
			'benchmark-email-lite-section-regular-pg2',
			__( 'Email Template', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'section_template' ),
			'benchmark-email-lite-settings-pg2'
		);

		// Settings API Fields Follow
		add_settings_field(
			'benchmark-email-lite-api-keys',
			__( 'API Key(s) from your Benchmark Email account(s)', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'field_api_keys' ),
			'benchmark-email-lite-settings-pg1',
			'benchmark-email-lite-section-main'
		);
		add_settings_field(
			'benchmark-email-lite-webpage-flag',
			__( 'Webpage version', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'field_webpage_flag' ),
			'benchmark-email-lite-settings-pg1',
			'benchmark-email-lite-section-regular'
		);
		add_settings_field(
			'benchmark-email-lite-connection-timeout',
			__( 'Connection Timeout (seconds)', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'field_connection_timeout' ),
			'benchmark-email-lite-settings-pg1',
			'benchmark-email-lite_section_diagnostics'
		);
		add_settings_field(
			'benchmark-email-lite-template-html',
			__( 'Email Template HTML', 'benchmark-email-lite' ),
			array( 'benchmarkemaillite_settings', 'field_template' ),
			'benchmark-email-lite-settings-pg2',
			'benchmark-email-lite-section-regular-pg2'
		);
	}

	// Admin Menu
	static function admin_menu() {
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
			'Benchmark Email Lite Emails',
			'Emails',
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
		add_submenu_page(
			'benchmark-email-lite',
			'Benchmark Email Lite Template',
			'Email Template',
			'manage_options',
			'benchmark-email-lite-template',
			array( 'benchmarkemaillite_settings', 'page' )
		);
	}

	// Plugins Page Settings Link
	static function plugin_action_links( $links ) {
		$links['settings'] = '<a href="admin.php?page=benchmark-email-lite-settings">'
			. __( 'Settings', 'benchmark-email-lite' ) . '</a>';
		return $links;
	}


	/********************
	 Settings API Methods
	 ********************/

	// Page Loaders
	static function page() {
		$options = get_option( 'benchmark-email-lite_group' );
		$tabs = array(
			'benchmark-email-lite' => __( 'Emails', 'benchmark-email-lite' ),
			'benchmark-email-lite-settings' => __( 'Settings', 'benchmark-email-lite' ),
			'benchmark-email-lite-template' => __( 'Email Template', 'benchmark-email-lite' ),
		);
		$current = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : 'benchmark-email-lite';
		require( dirname( __FILE__ ) . '/../views/settings.html.php');
	}
	static function print_settings( $page, $group ) {
		echo '<form method="post" action="options.php">';
		settings_fields( $group );
		do_settings_sections( $page );
		submit_button( __( 'Save Changes', 'benchmark-email-lite' ), 'primary', 'submit', false );
		echo '&nbsp; <input type="reset" class="button-secondary" value="' . __( 'Reset Changes', 'benchmark-email-lite' ) . '" />';
		if( $page == 'benchmark-email-lite-settings-pg2' ) {
			echo '&nbsp; <input name="submit" type="submit" class="button-secondary" value="' . __( 'Reset to Defaults', 'benchmark-email-lite' ) . '"
				onclick="return confirm( \'' . __( 'Are you sure you wish to load the default values and lose your customizations?', 'benchmark-email-lite' ) . '\' );" />';
		}
		echo '</form>';
	}

	// Settings API Sections Follow
	static function section_regular() { }
	static function section_main() {
		echo '
			<p>
				' . __( 'The API Key(s) connect your WordPress site with your Benchmark Email account(s).', 'benchmark-email-lite' ) . '
				' . __( 'Only one key is required per Benchmark Email account.', 'benchmark-email-lite' ) . '
				' . __( 'API Key(s) may expire after one year.', 'benchmark-email-lite' ) . '
			</p>
			<p>
				<a target="_blank" href="' . self::$linkaffiliate . '" target="BenchmarkEmail">
				' . __( 'Signup for a 30-day FREE Trial', 'benchmark-email-lite') . '
				</a>,
				' . __( 'or', 'benchmark-email-lite' ) . '
				<a target="_blank" href="http://ui.benchmarkemail.com/EditSetting#_ctl0_ContentPlaceHolder1_UC_ClientSettings1_lnkGenerate" target="BenchmarkEmail">
				' . __( 'log in to Benchmark Email to get your API key', 'benchmark-email-lite' ) . '
				</a>.
			</p>
		';
	}
	static function section_diagnostics() {
		echo '<p style="color:red;">' . __( 'This section is for troubleshooting purposes only.', 'benchmark-email-lite' ) . '</p>';
	}
	static function section_template() {
		echo '
			<p>
				' . __( 'The following is for advanced users to customize the HTML and CSS template that wraps the output of the post-to-campaign feature.', 'benchmark-email-lite' ) . '
			</p>
			<p>
				' . __( 'For example, one can place an IMG tag that brings their logo URL in from their Media Library by placing code above the `H1` line.', 'benchmark-email-lite' ) . '
				' . __( 'One can also change the two color codes `EEEEEE` and `FFFFFF` to their desired background and foreground colors.', 'benchmark-email-lite' ) . '
				' . __( 'One can also change their fonts by changing the `font-family` priorities and `font-size` sizing.', 'benchmark-email-lite' ) . '
			</p>
			<p>
				' . __( 'There are two deprecated methods for applying changes to the template:', 'benchmark-email-lite' ) . '
				' . __( 'the file in the `templates` folder of this plugin, used when the below is left empty,', 'benchmark-email-lite' ) . '
				' . __( 'or an external custom plugin using the `benchmarkemaillite_compile_email_theme` filter.', 'benchmark-email-lite' ) . '
			</p>
			<p>
				<a target="_blank" href="https://ui.benchmarkemail.com/help-support/help-FAQ-details?id=100">
				' . __( 'Please review this article for help with email template coding.', 'benchmark-email-lite' ) . '
				</a>
			</p>
			<p>
				<a target="_blank" href="http://www.w3schools.com/tags/ref_colorpicker.asp">
				' . __( 'This is a good resource for getting hexidecimal color codes.', 'benchmark-email-lite' ) . '
				</a>
			</p>
			<p>
				<strong>
				' . __( 'Be sure to send an email test after making any changes to the email template.', 'benchmark-email-lite' ) . '
				</strong>
			</p>
		';
	}

	// Settings API Fields Follow
	static function field_api_keys() {
		$options = get_option( 'benchmark-email-lite_group' );
		$results = array();
		$key = $options[1];
		for ( $i = 0; $i < 5; $i ++ ) {
			$key[$i] = isset( $key[$i] ) ? $key[$i] : '';
			if ( ! $key[$i] ) { $results[$i] = '<img style="vertical-align:middle;opacity:0;" src="images/yes.png" alt="" width="16" height="16" />'; }
			else {
				benchmarkemaillite_api::$token = $key[$i];
				$results[$i] = ( is_array( benchmarkemaillite_api::lists() ) )
					? '<img style="vertical-align:middle;" src="images/yes.png" alt="Yes" title="' . self::goodconnection_message() . '" width="16" height="16" />'
					: '<img style="vertical-align:middle;" style="" src="images/no.png" alt="No" title="' . self::badconnection_message() . '" width="16" height="16" />';
			}
		}
		echo "
			{$results[0]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[0]}' /> Primary<br />
			{$results[1]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[1]}' /> Optional<br />
			{$results[2]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[2]}' /> Optional <br />
			{$results[3]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[3]}' /> Optional<br />
			{$results[4]} <input type='text' size='36' maxlength='50' name='benchmark-email-lite_group[1][]' value='{$key[4]}' /> Optional
		";
	}
	static function field_webpage_flag() {
		$options = get_option( 'benchmark-email-lite_group' );
		echo "<input id='benchmark-email-lite_group_2' type='checkbox' name='benchmark-email-lite_group[2]'
			value='yes'" . checked( 'yes', $options[2], false ) . " /> "
			. __("Include the sentence &quot;Having trouble viewing this email? <u>click here</u>&quot; in the top of emails?", 'benchamrk-email-lite');
	}
	static function field_connection_timeout() {
		$options = get_option( 'benchmark-email-lite_group' );
		echo __( 'If the connection with the Benchmark Email server takes', 'benchmark-email-lite' )
			. " <input id='benchmark-email-lite_group_5' type='text' size='2' maxlength='2' name='benchmark-email-lite_group[5]' value='{$options[5]}' /> "
			. __( 'seconds or longer, disable connections for 5 minutes to prevent site administration from becoming sluggish. (Default: 10)', 'benchmark-email-lite' );
	}
	static function field_template() {
		$options = get_option( 'benchmark-email-lite_group_template' );
		echo "
			<textarea id='benchmark-email-template' name='benchmark-email-lite_group_template[html]'
				style='width:100%;' cols='30' rows='20'>{$options['html']}</textarea><br />
			<small>
				<code>TITLE_HERE</code>" . __( 'will be replaced with the WP page title.', 'benchmark-email-lite' ) . "<br />
				<code>BODY_HERE</code>" . __( 'will be replaced with the WP page body.', 'benchmark-email-lite' ) . "
			</small>
		";
	}

	// Settings API Field Validations
	static function validate( $values ) {
		$options = get_option( 'benchmark-email-lite_group' );

		// Handle Reset to Defaults
		if( isset( $_REQUEST['submit'] ) && $_REQUEST['submit'] == 'Reset to Defaults' ) {
			$values['html'] = implode( '', file( dirname( __FILE__ ) . '/../templates/simple.html.php' ) );
		}

		foreach( $options as $key => $val ) {
			$val = isset( $values[$key] ) ? $values[$key] : '';

			// Process Saving Of API Keys
			if( $key == '1' ) {

				// Unserialize API Keys
				$values[1] = maybe_unserialize( $val );

				// Ensure This Is The Expected Field
				if( ! is_array( $values[1] ) ) { continue; }

				// Remove Empties
				$values[1] = array_filter( $values[1] );

				// Remove Duplicates
				$values[1] = array_unique( $values[1] );

				// Reset Keys
				$values[1] = array_values( $values[1] );

				// Remove Any Previous Errors
				delete_transient( 'benchmark-email-lite_error' );

				// Vendor Handshake With Benchmark Email
				benchmarkemaillite_api::handshake( $values[1] );

				// Deactivate Widgets of Deleted API Keys
				benchmarkemaillite_widget::cleanup_widgets( $values[1] );
			}

			// Sanitize Non Array Settings
			else { $values[$key] = esc_attr( $val ); }
		}
		add_settings_error(
			'benchmark-email-lite-notice',
			esc_attr( 'settings_updated' ),
			__( 'Settings saved.', 'benchmark-email-lite' ),
			'updated'
		);
		return $values;
	}
}

?>
