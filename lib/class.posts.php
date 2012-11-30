<?php

class benchmarkemaillite_posts {

	// Create Pages+Posts Metaboxes
	function admin_init() {
		add_meta_box(
			'benchmark-email-lite',
			'Benchmark Email Lite',
			array( 'benchmarkemaillite_posts', 'metabox' ),
			'post',
			'side',
			'default'
		);
		add_meta_box(
			'benchmark-email-lite',
			'Benchmark Email Lite',
			array( 'benchmarkemaillite_posts', 'metabox' ),
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
		$email = isset( $user->user_email ) ? $user->user_email : get_bloginfo( 'admin_email' );
		$bmelist = ( $val = get_transient( 'bmelist' ) ) ? esc_attr( $val ) : '';
		$title = ( $val = get_transient( 'bmetitle' ) ) ? esc_attr( $val ) : '';
		$from = ( $val = get_transient( 'bmefrom' ) ) ? esc_attr( $val ) : '';
		$subject = ( $val = get_transient( 'bmesubject' ) ) ? esc_attr( $val ) : '';
		$email = ( $val = get_transient( 'bmetestto' ) ) ? implode( ', ', $val ) : $email;

		// Open Benchmark Email Connection and Locate List
		$options = get_option('benchmark-email-lite_group');
		if ( ! isset( $options[1][0] ) || ! $options[1][0] ) {
			$val = benchmarkemaillite_settings::badconfig_message();
			echo "<strong style='color:red;'>{$val}</strong>";
		} else {
			$dropdown = benchmarkemaillite::print_lists( $options[1], $bmelist );
		}

		// Round Time To Nearest Quarter Hours
		$localtime = current_time('timestamp');
		$minutes = date( 'i', $localtime );
		$newminutes = ceil( $minutes / 15 ) * 15;
		$localtime_quarterhour = $localtime + 60 * ( $newminutes - $minutes );

		// Get Timezone String
		$tz = ( $val = get_option( 'timezone_string' ) ) ? $val : 'UTC';
		$dateTime = new DateTime();
		$dateTime->setTimeZone( new DateTimeZone( $tz ) );
		$localtime_zone = $dateTime->format( 'T' );

		// Output Form
		require( dirname( __FILE__ ) . '/../views/metabox.html.php' );
	}

	// Called when Adding, Creating or Updating any Page+Post
	function save_post( $postID ) {
		$options = get_option( 'benchmark-email-lite_group' );

		// Set Variables
		$bmelist = isset( $_POST['bmelist'] ) ? esc_attr( $_POST['bmelist'] ) : false;
		if ( $bmelist ) {
			list( benchmarkemaillite_api::$token, $listname, benchmarkemaillite_api::$listid )
				= explode( '|', $bmelist );
		}
		$bmetitle = isset( $_POST['bmetitle'] )
			? stripslashes( strip_tags( $_POST['bmetitle'] ) ) : false;
		$bmefrom = isset( $_POST['bmefrom'] )
			? stripslashes( strip_tags( $_POST['bmefrom'] ) ) : false;
		$bmesubject = isset( $_POST['bmesubject'] )
			? stripslashes( strip_tags( $_POST['bmesubject'] ) ) : false;
		$bmeaction = isset( $_POST['bmeaction'] )
			? esc_attr( $_POST['bmeaction'] ) : false;
		$bmetestto = isset( $_POST['bmetestto'] )
			? explode( ',', $_POST['bmetestto'] ) : array();

		// Handle Prepopulation Loading
		set_transient( 'bmelist', $bmelist, 15 );
		set_transient( 'bmeaction', $bmeaction, 15 );
		set_transient( 'bmetitle', $bmetitle, 15 );
		set_transient( 'bmefrom', $bmefrom, 15 );
		set_transient( 'bmesubject', $bmesubject, 15 );
		set_transient( 'bmetestto', $bmetestto, 15 );

		// Don't Work With Post Revisions Or Other Post Actions
		if (
			wp_is_post_revision($postID)
			|| !isset($_POST['bmesubmit'])
			|| $_POST['bmesubmit'] != 'yes'
		) { return; }

		// Get User Info
		if ( ! $user = wp_get_current_user() ) { return; }
		$user = get_userdata( $user->ID );
		$name = isset( $user->first_name ) ? $user->first_name : '';
		$name .= isset( $user->last_name ) ? ' ' . $user->last_name : '';
		$name = trim( $name );

		// Get Post Info
		if ( ! $post = get_post( $postID ) ) { return; }

		// Prepare Campaign Data
		$data = array(
			'title' => $post->post_title,
			'body' => apply_filters( 'the_content', $post->post_content ),
		);
		$content = self::compile_email_theme( $data );
		$webpageVersion = ( $options[2] == 'yes' ) ? true : false;
		$permissionMessage = isset( $options[4] ) ? $options[4] : '';

		// Create Campaign
		$result = benchmarkemaillite_api::campaign(
			$bmetitle, $bmefrom, $bmesubject, $content, $webpageVersion, $permissionMessage
		);

		// Handle Error Condition: Preexists
		if ( $result == __( 'preexists', 'benchmark-email-lite' ) ) {
			set_transient(
				'benchmark-email-lite_error',
				__( 'An email campaign by this name was previously sent and cannot be updated or sent again. Please choose another email name.', 'benchmark-email-lite' )
			);
			return;

		// Handle Error Condition: Other
		} else if ( ! is_numeric( benchmarkemaillite_api::$campaignid ) ) {
			set_transient(
				'benchmark-email-lite_error',
				__( 'There was a problem creating or updating your email campaign. Please try again later.', 'benchmark-email-lite' )
				. (
					isset( benchmarkemaillite_api::$campaignid['faultString'] )
						? ' ' . __( 'Benchmark Email response code: ', 'benchmark-email-lite' )
							. benchmarkemaillite_api::$campaignid['faultCode']
						: ''
					)
			);
			return;
		}

		// Clear Fields After Successful Send
		if ( in_array( $bmeaction, array( 2, 3 ) ) ) {
			delete_transient( 'bmelist' );
			delete_transient( 'bmeaction' );
			delete_transient( 'bmetitle' );
			delete_transient( 'bmefrom' );
			delete_transient( 'bmesubject' );
			delete_transient( 'bmetestto' );
		}

		// Schedule Campaign
		switch ( $bmeaction ) {
			case '1':

				// Send Test Emails
				foreach ( $bmetestto as $i => $bmetest ) {

					// Limit To 5 Recipients
					$overage = ( $i >= 5 ) ? true : false;
					if( $i >= 5 ) { continue; }

					// Send
					$bmetest = sanitize_email( trim( $bmetest ) );
					benchmarkemaillite_api::campaign_test( $bmetest );
				}

				// Report
				$overage = ( $overage )
					? __( 'Sending was capped at the first 5 test addresses.', 'benchmark-email-lite' )
					: '';
				set_transient(
					'benchmark-email-lite_updated',
					sprintf(
						__( 'A test of your campaign %s was successfully sent.', 'benchmark-email-lite' ),
						"<em>{$bmetitle}</em>"
					) . $overage
				);
				break;

			case '2':

				// Send Campaign
				benchmarkemaillite_api::campaign_now();

				// Report
				set_transient(
					'benchmark-email-lite_updated',
					sprintf(
						__( 'Your campaign %s was successfully sent.', 'benchmark-email-lite' ),
						"<em>{$bmetitle}</em>"
					)
				);
				break;

			case '3':

				// Schedule Campaign For Sending
				$bmedate = isset( $_POST['bmedate'] )
					? esc_attr( $_POST['bmedate'] ) : date( 'd M Y', current_time( 'timestamp' ) );
				$bmetime = isset( $_POST['bmetime'] )
					? esc_attr( $_POST['bmetime'] ) : date( 'H:i', current_time( 'timestamp' ) );
				$when = "$bmedate $bmetime";
				benchmarkemaillite_api::campaign_later( $when );

				// Report
				set_transient(
					'benchmark-email-lite_updated',
					sprintf(
						__( 'Your campaign %s was successfully scheduled for %s.', 'benchmark-email-lite' ),
						"<em>{$bmetitle}</em>",
						"<em>{$when}</em>"
					)
				);
				break;
		}
	}

	/*
	Formats Email Body Into Email Template
	This Can Be Customized EXTERNALLY Using This Approach:
		add_filter( 'benchmarkemaillite_compile_email_theme', 'my_custom_function', 10, 1 );
		function my_custom_function( $args ) {
			return "
				<html>
					<head>
						<title>{$args['title']}</title>
					</head>
					<body>
						<div style='background-color: #eee; padding: 15px; margin: 15px; border: 1px double #ddd;'>
							<h1>{$args['title']}</h1>
							{$args['body']}
						</div>
					</body>
				</html>
			";
		}
	*/
	function compile_email_theme( $data ) {
		$options = get_option( 'benchmark-email-lite_group' );

		// Apply User Customizations
		if( has_filter( 'benchmarkemaillite_compile_email_theme' ) ) {
			return apply_filters( 'benchmarkemaillite_compile_email_theme', $data );
		}

		// Not Customized
		switch ( $options[3] ) {

			// Use Site Theme As Email Template
			case 'theme': $theme = get_permalink( $postID ); break;

			// Use Included Sample Email Template
			default: $theme = dirname( __FILE__ ) . '/../templates/simple.html.php';
		}
		return benchmarkemaillite::require_to_var( $data, $theme, true );
	}
}

?>