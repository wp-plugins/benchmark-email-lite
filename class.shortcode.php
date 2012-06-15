<?php

class benchmarkemaillite_shortcode {

	// Display The Shortcode Output
	function shortcode($atts) {
		if (!isset($atts['widget_id'])) { return; }
		$atts = shortcode_atts(
			array(
				'widget_id' => '',
				'before_widget' => '',
				'after_widget' => '',
				'before_title' => '<h2 class="widgettitle">',
				'after_title' => '</h2>',
			), $atts
		);
		$widgets = get_option('widget_benchmarkemaillite_widget');
		if (!isset($widgets[$atts['widget_id']])) { return; }
		benchmarkemaillite_widget::widget($atts, $widgets[$atts['widget_id']]);
	}
}

?>