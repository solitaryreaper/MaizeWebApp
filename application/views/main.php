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
		<h1 align="center"> MAIZE DATA GENERATOR </h1>
	</header>

	<div class="container">
	<form class="form-horizontal" name="maize_data_form" action="http://localhost/maize/index.php/main/load_maize_data" method="post">

	<!-- Contains all the phenotypes and related columns which can be chosen -->
	<table class="table table-bordered table-hover table-condensed">
	<thead>
		<th>Step 1 <i class="icon-arrow-right"></i> Choose phenotypes</th>
		<th>Step 2 <i class="icon-arrow-right"></i> Choose phenotype metadata</th>
	</thead>
	<tbody>
		<tr>
			<td>
				<label class="checkbox">
					<input type="checkbox" id="kernel_3d_cbox"> kernel_3d
				</label>
			</td>
			<td>
						
			</td>
		</tr>
		<tr>
			<td>
				<label class="checkbox">
					<input type="checkbox" id="spectra_cbox"> spectra
				</label>
			</td>
			<td>
						
			</td>
		</tr>
		<tr>
			<td>
				<label class="checkbox">
					<input type="checkbox" id="weights_cbox"> weights
				</label>
			</td>
			<td>
						
			</td>
		</tr>
		<tr>
			<td>
				<label class="checkbox">
					<input type="checkbox" id="predictions_cbox"> predictions
				</label>
			</td>
			<td>
						
			</td>
		</tr>						
	</tbody>
	</table>

	<!--  Contains the various filters and constraints to be applied the key data elements -->
	<table class="table table-bordered table-hover table-condensed">
	<thead>
		<th colspan="3">Step 3 <i class="icon-arrow-right"></i> Choose constraints/filters</th>
	</thead>
	<tbody>
		<tr>
			<td>Type</td>
			<td>
				<select id="filter_type_options" name="filter_type_options">
					<option>EQUALS</option>
					<option>STARTS WITH</option>
					<option>ENDS WITH</option>
					<option>CONTAINS</option>
				</select>
			</td>
			<td><input type="text" id="filter_type" name="filter_type"></td>
		</tr>
		<tr>
			<td>Plate Name</td>
			<td>
				<select id="filter_plate_name_options" name="filter_plate_name_options">
					<option>EQUALS</option>
					<option>STARTS WITH</option>
					<option>ENDS WITH</option>
					<option>CONTAINS</option>
				</select>
			</td>
			<td><input type="text" id="filter_plate_name"></td>
		</tr>
		<tr>
			<td>Packet Name</td>
			<td>
				<select id="filter_packet_name_options" name="filter_packet_name_options">
					<option>EQUALS</option>
					<option>STARTS WITH</option>
					<option>ENDS WITH</option>
					<option>CONTAINS</option>
				</select>
			</td>
			<td><input type="text" id="filter_packet_name"></td>
		</tr>
	</tbody>
	</table>

	<!-- Aggregate function to be chosen for the entire data -->
	<table class="table table-bordered table-condensed">
		<thead>
				<th><label class="control-label" for="aggregate_func">Step 4 <i class="icon-arrow-right"></i> Choose aggregate function</label></th>
				<th>
					<div class="control-group">  
            		<div class="controls">  
              		<select id="aggregate_func">  
                	<option>COUNT</option>  
                	<option>AVERAGE</option>  
                	<option>STANDARD DEVIATION</option>  
              		</select>  
            		</div>  
          			</div>
				</th>
        </thead>
    </table>

	<!-- Generate the CSV file -->

	<div class="form-actions">
		<button type="submit" class="btn btn-large btn-danger">Submit</button>  
    </div>

	</form>

	</div>
</body>
</html>