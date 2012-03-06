<div style="font-size:1.4em;line-height:normal;">
	<h3>View Email Statistics</h3>
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
			title:"<?php echo $response['mailSent']; ?> emails sent",
			colors:['green','blue','orange']
		};
		var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
		chart.draw(data, options);
	}
	</script>
	<div id="chart_div" style="float:left;width:500px;"></div>
	<div style="float:left;">
		<table>
			<tbody>
				<tr>
					<th style="text-align:right;">Sent</th>
					<td><?php echo $response['mailSent']; ?></td>
				</tr>
				<tr>
					<th style="text-align:right;">Opens</th>
					<td>
						<?php echo "<a href='{$url}opens'>{$response['opens']}</a>"; ?>
						(<?php echo number_format(100*$response['opens']/$response['mailSent'], 1); ?>%)
					</td>
				</tr>
				<tr>
					<th style="text-align:right;">Clicks</th>
					<td>
						<?php echo "<a href='{$url}clicks'>{$response['clicks']}</a>"; ?>
						(<?php echo number_format(100*$response['clicks']/$response['opens'], 1); ?>%)
					</td>
				</tr>
				<tr>
					<th style="text-align:right;">Forwards</th>
					<td><?php echo "<a href='{$url}forwards'>{$response['forwards']}</a>"; ?></td>
				</tr>
				<tr>
					<th style="text-align:right;">Bounces</th>
					<td>
						<?php echo "<a href='{$url}bounces'>{$response['bounces']}</a>"; ?>
						(<?php echo number_format(100*$response['bounces']/$response['mailSent'], 1); ?>%)
					</td>
				</tr>
				<tr>
					<th style="text-align:right;">Unsubscribes</th>
					<td>
						<?php echo "<a href='{$url}unsubscribes'>{$response['unsubscribes']}</a>"; ?>
						(<?php echo number_format(100*$response['unsubscribes']/$response['mailSent'], 1); ?>%)
					</td>
				</tr>
				<tr>
					<th style="text-align:right;">Unopens</th>
					<td>
						<?php echo $response['unopens']; ?>
						(<?php echo number_format(100*$response['unopens']/($response['mailSent']-$response['bounces']), 1); ?>%)
					</td>
				</tr>
				<tr>
					<th style="text-align:right;">Abuse Reports</th>
					<td><?php echo $response['abuseReports']; ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div style="clear:both;"> </div>
</div>