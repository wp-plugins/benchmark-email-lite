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
		$options = get_option( 'benchmark-email-lite_group' );

		// Apply User Customizations
		if( has_filter( 'benchmarkemaillite_compile_email_theme' ) ) {
			return apply_filters( 'benchmarkemaillite_compile_email_theme', $data );
		}

		// Not Customized
		switch ( $options[3] ) {

			// Use Site Theme As Email Template
			case 'theme':
				$theme = get_permalink( $postID );
				break;

			// Use Included Sample Email Template
			default:
				$theme = dirname( __FILE__ ) . '/../templates/simple.html.php';
		}

		// Uses PHP Output Buffering
		ob_start();
		require( $theme );
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
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