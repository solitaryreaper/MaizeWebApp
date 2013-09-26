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
		<h1 align="center"><strong>MAIZE DATA GENERATION RESULTS SUMMARY </strong></h1>
	</header>

	<div class="container">
		<table class="table table-bordered table-condensed">
			<thead>
				<th>Parameter</th>
				<th>Value</th>
			</thead>
			<tbody>
				<tr>
					<td>ROWS</td>
					<td><?php echo $count;?></td>
				</tr>
				<tr>
					<td>QUERY</td>
					<td><?php echo $query;?></td>				
				</tr>
			</tbody>
		</table>
	</div>
</body>