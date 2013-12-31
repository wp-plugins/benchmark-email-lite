<p>
	<?php _e( 'This information is cached for 5 minutes to improve performance.', 'benchmark-email-lite' ); ?>
	<a href="<?php echo $url; ?>&amp;flush=1">
	<?php _e( 'Click to refresh with realtime data.', 'benchmark-email-lite' ); ?>
	</a>
</p>

<table class="widefat" cellspacing="0">
	<thead>
		<tr>
			<th width="90"><?php _e( 'API key', 'benchmark-email-lite' ); ?></th>
			<th width="*"><?php _e( 'Email name', 'benchmark-email-lite' ); ?></th>
			<th width="90"><?php _e( 'Date Modified', 'benchmark-email-lite' ); ?></th>
		</tr>
	</thead>
	<tbody>

		<?php
		$errors = array();
		foreach( $data as $key => $emails ) {
			if( ! $emails ) { $errors[] = $key; continue; }
		?>

		<tr>
			<th colspan="3"><small><?php echo $key; ?></small></th>
		</tr>

		<?php
		foreach( $emails as $id ) {
			$email = get_transient( "benchmarkemaillite_{$id}" );
		?>

		<tr>
			<td></td>
			<td>
				<a href="<?php echo $email['report_url']; ?>">
				<?php echo $email['emailName']; ?></a><br />
				<small>
					<?php _e( 'List', 'benchmark-email-lite' ); ?>:
					<?php echo $email['toListName'] ?>
				</small>
			</td>
			<td><?php echo $email['scheduleDate']; ?></td>
		</tr>

		<?php } ?>

		<?php } ?>

	</tbody>
</table>

<?php if( $errors ) { ?>
<h4><?php _e( 'No results found for the following API key(s)', 'benchmark-email-lite' ); ?>:</h4>
<ul>
	<?php foreach( $errors as $key ) { ?>
	<li><?php echo $key; ?></li>
	<?php } ?>
</ul>

<?php } else if( ! isset( $key ) ) { ?>
<p><?php _e( 'Data will start appearing only after your emails have been sent.', 'benchmark-email-lite' ); ?></p>
<?php } ?>