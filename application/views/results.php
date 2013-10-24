<html>
<head>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" ></script>
	<script type="text/javascript" src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
	<link type="text/css" rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" />
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">
	<link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo(CSS.'main.css'); ?>">

	<title>Maize Data Generator</title>
</head>
<body>
	<header class="well">
		<h1 align="center">
			<strong>MAIZE DATA GENERATION RESULTS SUMMARY </strong>
			<a class="pull-right" href="<?php echo(URL); ?>"><img src="<?php echo(IMG.'home_icon_small.png'); ?>"></a>
		</h1>
	</header>

	<div class="container">
		<table class="table table-bordered table-condensed">
			<thead class="span12">
				<th class="span2">Parameter</th>
				<th class="span10">Value</th>
			</thead>
			<tbody>
				<tr>
					<td>RECORDS</td>
					<td>
						<?php
						if ($count > 0) {
							echo $count;
						}
						else {
							echo '<span class="label label-danger">No records found !!</span>';
						}
						?>
					</td>					
				</tr>
				<tr>
					<!-- Show a download link only if count > 0-->
					<?php
					if ($count > 0) {
						echo "<td>DOWNLOAD LINK</td>";
						echo "<td>";
						echo '<a href="' . $csv_file_path . '">';
						echo "Download " . $report_type . " report.";
						echo "</a>";
						echo "</td>";
					}
					?>
				</tr>				
				<tr>
					<td>DB QUERY</td>
					<td><?php echo $query;?></td>				
				</tr>
			</tbody>
		</table>
	</div>
</body>