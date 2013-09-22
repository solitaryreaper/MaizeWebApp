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
		<h1 align="center"><strong>MAIZE DATA GENERATOR </strong></h1>
	</header>

	<div class="container">
	<form class="form-horizontal" name="maize_data_form" action="http://barracuda.botany.wisc.edu/MaizeWebApp/index.php/main/load_maize_data" 
		  onsubmit="return validate_form()" method="post">

	<!-- Contains all the phenotypes which can be chosen -->
	<table class="table table-bordered table-hover table-condensed">
	<thead>
		<th colspan="5">Step 1 <i class="icon-arrow-right"></i> Choose phenotypes</th>
	</thead>
	<tbody>
		<tr>
			<td>
				<input type="checkbox" id="kernel_3d_cbox" name="kernel_3d_cbox"> Kernel 3D
			</td>
			<td>
				<input type="checkbox" id="predictions_cbox" name="predictions_cbox"> Predictions
			</td>
            <td>
                <input type="checkbox" id="raw_weight_spectra_cbox" name="raw_weight_spectra_cbox"> Raw Weight Spectra
            </td>
            <td>
                <input type="checkbox" id="avg_weight_spectra_cbox" name="avg_weight_spectra_cbox"> Average Weight Spectra
            </td>
            <td>
                <input type="checkbox" id="std_weight_spectra_cbox" name="std_weight_spectra_cbox"> Standard Deviation Weight Spectra
            </td>                        			
		</tr>
	</tbody>
	</table>

	<!-- Contains all the genotype metadata which can be chosen -->
	<table class="table table-bordered table-hover table-condensed">
	<thead>
		<th colspan="4">Step 2 <i class="icon-arrow-right"></i> Choose genotypes</th>
	</thead>
	<tbody>
		<tr>
			<td>
				<input type="checkbox" id="population_type_cbox" name="population_type_cbox"> Population Type
			</td>
			<td>
				<input type="checkbox" id="plate_name_cbox" name="plate_name_cbox"> Plate Name
			</td>
            <td>
                <input type="checkbox" id="packet_name_cbox" name="packet_name_cbox"> Packet Name
            </td>
            <td>
                <input type="checkbox" id="isolate_cbox" name="isolate_cbox"> Isolate
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
				<select id="filter_type_option" name="filter_type_option">
                    <option selected>EQUALS&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
				</select>
			</td>
            <td>
                <select id="filter_type_value" name="filter_type_value">
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
            </td>
		</tr>
		<tr>
			<td>Plate Name</td>
			<td>
				<select id="filter_plate_option" name="filter_plate_option">
					<option selected>EQUALS</option>
					<option>STARTS WITH</option>
					<option>ENDS WITH</option>
					<option>CONTAINS</option>
				</select>
			</td>
			<td><input type="text" id="filter_plate_value" name="filter_plate_value"></td>
		</tr>
		<tr>
			<td>Packet Name</td>
			<td>
				<select id="filter_packet_option" name="filter_packet_option">
					<option selected>EQUALS</option>
					<option>STARTS WITH</option>
					<option>ENDS WITH</option>
					<option>CONTAINS</option>
				</select>
			</td>
			<td><input type="text" id="filter_packet_value" name="filter_packet_value"></td>
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
              		<select id="aggregate_func" name="aggregate_func">  
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

	<div align="center">
		<button id="csv-generator" type="submit" class="btn btn-large btn-danger"><i class="icon-download-alt"></i> Generate CSV</button>  
    </div>

	</form> <!-- End of form -->

	</div> <!-- End of main div container -->

	<script>
	function validate_form()
	{
		// Atleast one phenotype must be selected
		var is_phenotype_selected = 
			$('#kernel_3d_cbox').is(':checked') || $('#predictions_cbox').is(':checked') || $('#raw_weight_spectra_cbox').is(':checked') || 
			$('#avg_weight_spectra_cbox').is(':checked') || $('#std_weight_spectra_cbox').is(':checked');
		if(is_phenotype_selected == false) {
			alert("Please select atleast one phenotype to proceed !!");
			return false;
		}

		// Atleast one genotype must be selected
		var is_genotype_selected = 
			$('#population_type_cbox').is(':checked') || $('#plate_name_cbox').is(':checked') || 
			$('#packet_name_cbox').is(':checked') || $('#isolate_cbox').is(':checked');
		if(is_genotype_selected == false) {
			alert("Please select atleast one genotype to proceed !!");
			return false;
		}

		// Filter value for plate name and packet name should be a valid alphanumeric string

		return true;
	}
	</script
</body>
</html>
