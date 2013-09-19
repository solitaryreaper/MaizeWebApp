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
	<form class="form-horizontal" name="maize_data_form" action="http://barracuda.botany.wisc.edu/MaizeWebApp/index.php/main/load_maize_data" method="post">

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
					<input type="checkbox" id="kernel_3d_cbox"> Kernel 3D
				</label>
			</td>
			<td>
						
			</td>
		</tr>
		<tr>
			<td>
				<label class="checkbox">
					<input type="checkbox" id="predictions_cbox"> Predictions
				</label>
			</td>
			<td>
						
			</td>
		</tr>
                <tr>
                        <td>
                                <label class="checkbox">
                                        <input type="checkbox" id="raw_weight_spectra_cbox"> Raw Weight Spectra
                                </label>
                        </td>
                        <td>

                        </td>
                </tr>
                <tr>
                        <td>
                                <label class="checkbox">
                                        <input type="checkbox" id="avg_weight_spectra_cbox"> Average Weight Spectra
                                </label>
                        </td>
                        <td>

                        </td>
                </tr>
                <tr>
                        <td>
                                <label class="checkbox">
                                        <input type="checkbox" id="std_weight_spectra_cbox"> Standard Deviation Weight Spectra
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
                                        <option selected>EQUALS&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				</select>
			</td>
                        <td>
                                <select id="filter_type_values" name="filter_type_values">
					<option>NONE</option>
					<option>ALL</option>
					<option>test</option>
					<option>calibration</option>
					<option>cleaning</option>
					<option>collaborator</option>
					<option>composition_mutants</option>
					<option>dek</option>
					<option>dosage_effect_screen</option>
					<option>IBM_NILs</option>
					<option>IBM_RILs</option>
					<option>maintenance</option>
					<option>NAM_parents</option>
					<option>NC-350_RILs</option>
					<option>seedling_phenotyping_widiv</option>
					<option>Settles_lab</option>
					<option>settles_lab</option>
				</select>
				<!--
                                <div>
                                        <input type="checkbox">NONE</input>
                                        <input type="checkbox">ALL</input>
                                        <input type="checkbox">>test</input>
                                        <input type="checkbox">calibration</input>
                                        <input type="checkbox">cleaning></input>
                                        <input type="checkbox">collaborator</input>
                                        <input type="checkbox">composition_mutants</input>
                                        <input type="checkbox">dek</input>
                                        <input type="checkbox">dosage_effect_screen</input>
                                        <input type="checkbox">IBM_NILs</input>
                                        <input type="checkbox">IBM_RILs</input>
                                        <input type="checkbox">maintenance</input>
                                        <input type="checkbox">NAM_parents</input>
                                        <input type="checkbox">NC-350_RILs</input>
                                        <input type="checkbox">seedling_phenotyping_widiv</input>
                                        <input type="checkbox">Settles_lab</input>
                                        <input type="checkbox">settles_lab</input>
                                </div>-->
                        </td>
		</tr>
		<tr>
			<td>Plate Name</td>
			<td>
				<select id="filter_plate_name_options" name="filter_plate_name_options">
					<option selected>EQUALS</option>
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
					<option selected>EQUALS</option>
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
                	<option selected>NONE</option>  
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
