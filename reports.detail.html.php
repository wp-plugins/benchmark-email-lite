<h3><?php echo __('Email Summary Reports', 'benchmark-email-lite'); ?></h3>
<p>
	<strong><?php echo __('Email name', 'benchmark-email-lite'); ?>:</strong> <?php echo $response['emailName']; ?>
	<?php if (isset($response['communityurl'])) { ?>
	(<a href="<?php echo $response['communityurl']; ?>">View Campaign</a>)<br />
	<?php } ?>
	<strong><?php echo __('Subject', 'benchmark-email-lite'); ?>:</strong> <?php echo $response['subject']; ?>
</p>
<!-- See http://code.google.com/apis/chart/interactive/docs/gallery/piechart.html -->
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load('visualization', '1.0', {'packages':['corechart']});
google.setOnLoadCallback(drawChart);
function drawChart() {
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Statistic');
	data.addColumn('number', 'Number');
	data.addRows([
		['<?php echo __('Opened', 'benchmark-email-lite'); ?>', <?php echo $response['opens'];?>],
		['<?php echo __('Unopened', 'benchmark-email-lite'); ?>', <?php echo $response['unopens'];?>],
		['<?php echo __('Bounced', 'benchmark-email-lite'); ?>', <?php echo $response['bounces'];?>],
	]);
	var options = {
		chartArea:{width:500,height:350},
		width:500,
		height:400,
		is3D:true,
		legend:{position:'bottom'},
		colors:['green','blue','orange'],
	};
	var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
	chart.draw(data, options);
}
</script>
<div style="float:left;width:500px;">
	<div id="chart_div"></div>
	<p style="text-align:center;">
		<strong><?php echo __('Click rate', 'benchmark-email-lite'); ?>:</strong>
		<?php echo number_format(100 * $response['clicks'] / $response['opens'], 1); ?>%
	</p>
</div>
<div style="float:left;">
	<h3><?php echo __('Email Statistics', 'benchmark-email-lite'); ?></h3>
	<table class="widefat" cellspacing="0">
		<tbody>
			<tr>
				<th><?php echo __('Total Emails Sent', 'benchmark-email-lite'); ?></th>
				<td><?php echo $response['mailSent']; ?></td>
			</tr>
			<tr>
				<th><?php echo __('Opened Emails', 'benchmark-email-lite'); ?></th>
				<td>
					<?php echo ($response['opens']) ? "<a href='{$url}opens' title='" . __('Click to view report', 'benchmark-email-lite') . "'>{$response['opens']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['opens']/$response['mailSent'], 1); ?>%)
				</td>
			</tr>
			<tr>
				<th><?php echo __('Links Clicked', 'benchmark-email-lite'); ?></th>
				<td>
					<?php echo ($response['clicks']) ? "<a href='{$url}clicks' title='" . __('Click to view report', 'benchmark-email-lite') . "'>{$response['clicks']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['clicks']/$response['opens'], 1); ?>%)
				</td>
			</tr>
			<tr>
				<th><?php echo __('Emails Forwarded', 'benchmark-email-lite'); ?></th>
				<td>
					<?php echo ($response['forwards']) ? "<a href='{$url}forwards' title='" . __('Click to view report', 'benchmark-email-lite') . "'>{$response['forwards']}</a>" : 0; ?>
				</td>
			</tr>
			<tr>
				<th><?php echo __('Emails Bounced', 'benchmark-email-lite'); ?></th>
				<td>
					<?php echo ($response['bounces']) ? "<a href='{$url}bounces' title='" . __('Click to view report', 'benchmark-email-lite') . "'>{$response['bounces']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['bounces']/$response['mailSent'], 1); ?>%)
				</td>
			</tr>
			<tr>
				<th><?php echo __('Unsubscribes', 'benchmark-email-lite'); ?></th>
				<td>
					<?php echo ($response['unsubscribes']) ? "<a href='{$url}unsubscribes' title='" . __('Click to view report', 'benchmark-email-lite') . "'>{$response['unsubscribes']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['unsubscribes']/$response['mailSent'], 1); ?>%)
				</td>
			</tr>
			<tr>
				<th><?php echo __('Unopened', 'benchmark-email-lite'); ?></th>
				<td>
					<?php echo ($response['unopens']) ? "<a href='{$url}unopens' title='" . __('Click to view report', 'benchmark-email-lite') . "'>{$response['unopens']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['unopens']/($response['mailSent']-$response['bounces']), 1); ?>%)
				</td>
			</tr>
			<tr>
				<th><?php echo __('Abuse Reports', 'benchmark-email-lite'); ?></th>
				<td><?php echo $response['abuseReports']; ?></td>
			</tr>
		</tbody>
	</table>
	<?php self::showLocations(); ?>
</div>
<div style="clear:both;"> </div>