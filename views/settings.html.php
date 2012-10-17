<div class="wrap">
	<?php echo get_screen_icon('plugins'); ?>
	<h2>Benchmark Email Lite</h2>
	<h2 class="nav-tab-wrapper">&nbsp;
	<?php
	foreach( $tabs as $tab => $name ) {
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab{$class}' href='admin.php?page={$tab}'>{$name}</a>";
	}
	?>
	</h2>
	<?php
	switch( $current ) {
		case 'benchmark-email-lite':
			benchmarkemaillite_reports::show();
			break;
		case 'benchmark-email-lite-settings':
			benchmarkemaillite_settings::print_settings();
			break;
	}
	?>
	<br />
	<hr />
	<p><?php echo __('Need help? Please call Benchmark Email at 800.430.4095.', 'benchmark-email-lite'); ?></p>
</div>