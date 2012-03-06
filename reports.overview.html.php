<h3><?php echo __('Email Reports', 'benchmark-email-lite'); ?></h3>
<table class="widefat" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo __('Email Name', 'benchmark-email-lite'); ?></th>
			<th><?php echo __('Status', 'benchmark-email-lite'); ?></th>
			<th><?php echo __('Date Modified', 'benchmark-email-lite'); ?></th>
			<th><?php echo __('Date Scheduled', 'benchmark-email-lite'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($emails as $email) { ?>
		<tr>
			<td>
				<a href="<?php echo self::$url . "&amp;campaign={$email['id']}&amp;tokenindex={$tokenindex}"; ?>">
				<?php echo $email['emailName']; ?></a><br />
				<small><?php echo __('List', 'benchmark-email-lite'); ?>: <?php echo $email['toListName'] ?></small>
			</td>
			<td><?php echo $email['status']; ?></td>
			<td><?php echo $email['modifiedDate']; ?></td>
			<td><?php echo $email['scheduleDate']; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>