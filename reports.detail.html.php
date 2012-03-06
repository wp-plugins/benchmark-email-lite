<h3>Email Summary Reports</h3>
<p>
	<strong>Email name:</strong> <?php echo $response['emailName']; ?>
	<?php if (isset($response['communityurl'])) { ?>
	(<a href="<?php echo $response['communityurl']; ?>">View Campaign</a>)<br />
	<?php } ?>
	<strong>Subject:</strong> <?php echo $response['subject']; ?>
</p>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load('visualization', '1.0', {'packages':['corechart']});
google.setOnLoadCallback(drawChart);
function drawChart() {
	var data = new google.visualization.DataTable();
	data.addColumn('string', 'Statistic');
	data.addColumn('number', 'Number');
	data.addRows([
		['Opened', <?php echo $response['opens'];?>],
		['Unopened', <?php echo $response['unopens'];?>],
		['Bounced', <?php echo $response['bounces'];?>],
	]);
	var options = {
		width:500,
		height:400,
		is3D:true,
		title:"<?php echo $response['mailSent']; ?> emails sent. Click rate <?php echo number_format(100*$response['clicks']/$response['opens'], 1); ?>%",
		colors:['green','blue','orange']
	};
	var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
	chart.draw(data, options);
}
</script>
<div id="chart_div" style="float:left;width:500px;"></div>
<div style="float:left;">
	<h3>Email Statistics</h3>
	<table>
		<tbody>
			<tr>
				<th style="text-align:right;">Total Emails Sent</th>
				<td><?php echo $response['mailSent']; ?></td>
			</tr>
			<tr>
				<th style="text-align:right;">Opened Emails</th>
				<td>
					<?php echo ($response['opens']) ? "<a href='{$url}opens' title='View Report: Opened Emails'>{$response['opens']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['opens']/$response['mailSent'], 1); ?>%)
				</td>
			</tr>
			<tr>
				<th style="text-align:right;">Links Clicked</th>
				<td>
					<?php echo ($response['clicks']) ? "<a href='{$url}clicks' title='View Report: Links Clicked'>{$response['clicks']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['clicks']/$response['opens'], 1); ?>%)
				</td>
			</tr>
			<tr>
				<th style="text-align:right;">Emails Forwarded</th>
				<td>
					<?php echo ($response['forwards']) ? "<a href='{$url}forwards' title='View Report: Emails Forwarded'>{$response['forwards']}</a>" : 0; ?>
				</td>
			</tr>
			<tr>
				<th style="text-align:right;">Emails Bounced</th>
				<td>
					<?php echo ($response['bounces']) ? "<a href='{$url}bounces' title='View Report: Emails Bounced'>{$response['bounces']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['bounces']/$response['mailSent'], 1); ?>%)
				</td>
			</tr>
			<tr>
				<th style="text-align:right;">Unsubscribes</th>
				<td>
					<?php echo ($response['unsubscribes']) ? "<a href='{$url}unsubscribes' title='View Report: Unsubscribes'>{$response['unsubscribes']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['unsubscribes']/$response['mailSent'], 1); ?>%)
				</td>
			</tr>
			<tr>
				<th style="text-align:right;">Unopened</th>
				<td>
					<?php echo ($response['unopens']) ? "<a href='{$url}unopens' title='View Report: Unopened'>{$response['unopens']}</a>" : 0; ?>
					(<?php echo number_format(100*$response['unopens']/($response['mailSent']-$response['bounces']), 1); ?>%)
				</td>
			</tr>
			<tr>
				<th style="text-align:right;">Abuse Reports</th>
				<td><?php echo $response['abuseReports']; ?></td>
			</tr>
		</tbody>
	</table>
	<?php self::showLocations(); ?>
</div>
<div style="clear:both;"> </div>