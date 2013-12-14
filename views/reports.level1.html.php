<br />
<table class="widefat" cellspacing="0">
	<thead>
		<tr>
			<th width="90"><?php echo __('API key', 'benchmark-email-lite'); ?></th>
			<th width="*"><?php echo __('Email name', 'benchmark-email-lite'); ?></th>
			<th width="90"><?php echo __('Date Modified', 'benchmark-email-lite'); ?></th>
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
					<?php echo __('List', 'benchmark-email-lite'); ?>:
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
<h4><?php echo __('No results found for the following API key(s)', 'benchmark-email-lite'); ?>:</h4>
<ul>
	<?php foreach( $errors as $key ) { ?>
	<li><?php echo $key; ?></li>
	<?php } ?>
</ul>

<?php } else if( ! isset( $key ) ) { ?>
<p><?php echo __('Data will start appearing only after your emails have been sent.', 'benchmark-email-lite'); ?></p>
<?php } ?>