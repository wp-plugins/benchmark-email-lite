<div style="font-size:1.4em;line-height:normal;">
	<h3>Email Reports</h3>
	<table>
		<thead>
			<tr>
				<th>Email Name</th>
				<th>Status</th>
				<th>Date Modified</th>
				<th>Date Scheduled</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($emails as $email) { ?>
			<tr>
				<td>
					<a href="<?php echo self::$url . "&amp;campaign={$email['id']}&amp;tokenindex={$tokenindex}"; ?>">
					<?php echo $email['emailName']; ?></a><br />
					<small>List: <?php echo $email['toListName'] ?></small>
				</td>
				<td><?php echo $email['status']; ?></td>
				<td><?php echo $email['modifiedDate']; ?></td>
				<td><?php echo $email['scheduleDate']; ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>