<?php

// Plugin Display Class
class benchmarkemaillite_display {

	// Display The Shortcode Output
	static function shortcode( $atts ) {

		// Ensure Widget ID Is Specified
		if( ! isset( $atts['widget_id'] ) ) { return; }
		$atts = shortcode_atts(
			array(
				'widget_id' => '',
				'before_widget' => '',
				'after_widget' => '',
				'before_title' => '<h2 class="widgettitle">',
				'after_title' => '</h2>',
			), $atts
		);
		$widgets = get_option( 'widget_benchmarkemaillite_widget' );

		// Ensure Widget Id Is Found
		if( ! isset( $widgets[$atts['widget_id']] ) ) { return; }
		$instance = $widgets[$atts['widget_id']];
		$instance['widgetid'] = $atts['widget_id'];

		// Temporarily Disable Page Filtering And Return Widget Output
		benchmarkemaillite_widget::$is_shortcode = true;
		benchmarkemaillite_widget::$pagefilter = false;
		ob_start();
		the_widget( 'benchmarkemaillite_widget', $instance );
		$result = ob_get_contents();
		ob_end_clean();
		benchmarkemaillite_widget::$pagefilter = true;
		benchmarkemaillite_widget::$is_shortcode = false;
		return $result;
	}

	// Makes Drop Down Lists From API Keys
	static function print_lists( $keys, $selected='' ) {
		$lists = array();
		foreach( $keys as $key ) {
			if( ! $key ) { continue; }
			benchmarkemaillite_api::$token = $key;
			$response = benchmarkemaillite_api::lists();
			$lists[$key] = is_array( $response ) ? $response : '';
		}

		// Generate Output
		$output = '';
		$i = 0;
		foreach( $lists as $key => $list1 ) {
			if( ! $key ) { continue; }
			if( $i > 0 ) { $output .= "<option disabled='disabled' value=''></option>\n"; }
			$output .= "<option disabled='disabled' value=''>{$key}</option>\n";
			if( ! $list1 ) {
				$i++;
				$list1 = array();
				$output .= "<option value=''"
					. ( ( $i == 1 ) ? " selected='selected'" : '' )
					. " disabled='disabled'>↳ "
					. benchmarkemaillite_settings::badconnection_message()
					. "</option>\n";
				continue;
			}
			foreach( $list1 as $list ) {
				if( $list['listname'] == 'Master Unsubscribe List' ) { continue; }
				$val = "{$key}|{$list['listname']}|{$list['id']}";
				$i++;
				if( ! $selected && $i == 1 ) { $select = " selected='selected'"; }
				else {
					$select = ( $selected == $val )
						? " selected='selected'" : '';
				}
				$output .= "<option{$select} value='{$val}'>↳ {$list['listname']}</option>\n";
			}
		}
		return $output;
	}

	/*
	Formats Email Body Into Email Template
	This Can Be Customized EXTERNALLY Using This Approach:
	add_filter( 'benchmarkemaillite_compile_email_theme', 'my_custom_function', 10, 1 );
	*/
	static function compile_email_theme( $data ) {
		$options = get_option( 'benchmark-email-lite_group_template' );

		// Priority 1: Uses Child Plugin Customizations
		if( has_filter( 'benchmarkemaillite_compile_email_theme' ) ) {
			return apply_filters( 'benchmarkemaillite_compile_email_theme', $data );
		}

		// Priority 2: Uses Stored HTML
		if ( isset( $options['html'] ) && $output = $options['html'] ) {
			$admin_email = md5( strtolower( get_option( 'admin_email' ) ) );
			$output = str_replace( 'EMAIL_MD5_HERE', $admin_email, $output );
			$output = str_replace( 'TITLE_HERE', $data['title'], $output );
			$output = str_replace( 'BODY_HERE', $data['body'], $output );
			return self::normalize_html( $output );
		}

		// Priority 3: Uses Template File
		$themefile = dirname( __FILE__ ) . '/../templates/simple.html.php';
		ob_start();
		require( $themefile );
		$output = ob_get_contents();
		ob_end_clean();
		return self::normalize_html( $output );
	}

	// Convert WP Core CSS To Embedded
	static function normalize_html( $html ) {

		// Proceed Only When Possible
		if( ! class_exists( 'DOMDocument' ) ) { return $html; }

		// Rules To Apply
		$rules = array(
			'alignnone' => 'margin: 5px 20px 20px 0; ',
			'aligncenter' => 'display: block; margin: 5px auto 5px auto; ',
			'alignright' => 'float: right; margin: 5px 0 20px 20px; ',
			'alignleft' => 'float: left; margin: 5px 20px 20px 0; ',
			'wp-caption' => 'background: #fff; border: 1px solid #f0f0f0; max-width: 96%; padding: 5px 3px 10px; text-align: center; ',
			'wp-caption-text' => 'font-size: 11px; line-height: 17px; margin:0; padding: 0 4px 5px; ',
			//'wp-caption img' => 'border: 0 none; height: auto; margin: 0; max-width: 98.5%; padding: 0; width: auto;',
		);

		// Tags To Process
		$searchtags = array( 'p', 'span', 'img', 'div', 'h1', 'h2', 'h3', 'h4' );

		// Suppress PHP Warnings
		libxml_use_internal_errors( true );

		// Open HTML
		$doc = @DOMDocument::loadHTML( $html );

		// Loop Tags
		foreach( $searchtags as $tag ) {

			// Search For Matches
			$foundtags = $doc->getElementsByTagName( $tag );
			if( ! $foundtags ) { continue; }

			// Loop Matching Tags
			foreach( $foundtags as $para ) {

				// Search For Classes
				$classes = array();
				if( $para->hasAttribute( 'class' ) ) {
					$classes = $para->getAttribute( 'class' );
					$para->removeAttribute( 'class' );
					$classes = explode( ' ', $classes );
				}

				// Preserve Any Existing Styles
				$style = '';
				if( $para->hasAttribute( 'style' ) ) {
					$style = trim( $para->getAttribute( 'style' ) );
					if( ! strchr( $style, ';' ) ) { $style .= ';'; }
					$style .= ' ';
				}

				// Loop Classes
				foreach( $classes as $class ) {

					// Skip Non Conversion Classes
					if( ! in_array( $class, array_keys( $rules ) ) ) { continue; }

					// Accumulate Styling Rules To Apply
					$style .= $rules[$class];
				}

				// Store Rules Into Tag
				if( $style ) { $para->setAttribute( 'style', $style ); }
			}
		}

		// Assemble HTML
		$newdoc = $doc->saveHTML();

		// Handle Errors
		$errors = libxml_get_errors();
		//if( $errors ) { print_r( $errors ); }

		// Output
		return $newdoc;
	}

	// HTML Table Generator
	static function maketable( $data ) {

		// Table Column Widths By Title
		$widths = array(
			__( 'URL', 'benchmark-email-lite' ) => '*',
			__( 'Country', 'benchmark-email-lite' ) => '*',
			__( 'Opens', 'benchmark-email-lite' ) => 50,
			__( 'Clicks', 'benchmark-email-lite' ) => 50,
			__( 'Percent', 'benchmark-email-lite' ) => 50,
			__( 'Name', 'benchmark-email-lite' ) => 200,
			__( 'Email', 'benchmark-email-lite' ) => '*',
			__( 'Date', 'benchmark-email-lite' ) => 150,
			__( 'Bounce Type', 'benchmark-email-lite' ) => 100,
		);
	?>
	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th width="5">#</th>
				<?php foreach ( $data[0] as $i => $val ) { ?>
				<th width="<?php echo $widths[$i]; ?>"><?php echo $i; ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $data as $i => $val ) { ?>
			<tr>
				<td><?php echo ( $i + 1 ); ?></td>
				<?php foreach ( $val as $i2 => $val2 ) { ?>
				<td><?php echo $val2; ?></td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php
	}
}

?>