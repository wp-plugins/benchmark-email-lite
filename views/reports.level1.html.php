<h3><?php echo __('Email Reports', 'benchmark-email-lite'); ?></h3>
<?php
$errors = array();
foreach( $data as $key => $emails ) {
	if( ! $emails ) { $errors[] = $key; continue; }
?>
<p>
	<?php echo __('Please select a campaign to view the report for API key', 'benchmark-email-lite'); ?>:
	<?php echo $key; ?>
</p>
<table class="widefat" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo __('Email Name', 'benchmark-email-lite'); ?></th>
			<th><?php echo __('Date Sent', 'benchmark-email-lite'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach( $emails as $id ) {
			$email = get_transient( "benchmarkemaillite_{$id}" );
		?>
		<tr>
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
	</tbody>
</table>
<?php } ?>

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