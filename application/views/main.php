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

	<div id="results_loading" style='display: none;'>
		<img src="<?php echo(IMG.'loading.gif'); ?>">
	</div>

	<div id="form_container" name="form_container">
	<form class="form-horizontal" id="maize_data_form" name="maize_data_form" action="http://barracuda.botany.wisc.edu/MaizeWebApp/index.php/main/load_maize_data" 
			method="post" onsubmit="return validate_form()">

	<!-- Report type chosen -->
	<table class="table table-bordered table-condensed">
		<thead>
				<th><label class="control-label" for="report_type">Step 1 <i class="icon-arrow-right"></i> Choose report type</label></th>
				<th>
					<div class="control-group">  
            		<div class="controls">  
              		<select id="report_type" name="report_type">
	                	<option selected>Average Phenotypes</option> 
	                	<option>Standard Deviation Phenotypes</option> 	               		
	                	<option>Raw Weight/Spectra</option>  
	                	<option>Raw Phenotypes</option>
	                	<option>Phenotype Line Correlation</option>
	                	<option>Phenotype Correlation</option>
              		</select>  
            		</div>  
          			</div>
				</th>
        </thead>
    </table>

	<!-- Contains all the phenotypes which can be chosen -->
	<table class="table table-bordered table-hover table-condensed">
	<thead>
		<th colspan="7">Step 2 <i class="icon-arrow-right"></i> Choose phenotypes</th>
	</thead>
	<tbody>
		<tr>
			<td>
				<input type="checkbox" id="kernel_3d_phenotype_cbox" name="kernel_3d_phenotype_cbox"> Kernel 3D
			</td>
			<td>
				<input type="checkbox" id="kernel_dims_phenotype_cbox" name="kernel_dims_phenotype_cbox"> Kernel Dimensions
			</td>			
			<td>
				<input type="checkbox" id="predictions_phenotype_cbox" name="predictions_phenotype_cbox"> Predictions
			</td>
			<td>
				<input type="checkbox" id="root_tip_phenotype_cbox" name="root_tip_phenotype_cbox"> Root Tip Measurements
			</td>			
            <td>
                <input type="checkbox" id="raw_weight_spectra_phenotype_cbox" name="raw_weight_spectra_phenotype_cbox"> Raw Weight Spectra
            </td>
            <td>
                <input type="checkbox" id="avg_weight_spectra_phenotype_cbox" name="avg_weight_spectra_phenotype_cbox"> Average Weight Spectra
            </td>
            <td>
                <input type="checkbox" id="std_weight_spectra_phenotype_cbox" name="std_weight_spectra_phenotype_cbox"> Standard Deviation Weight Spectra
            </td>                        			
		</tr>
		<tr>
			<td>
				<input type="checkbox" id="root_length_phenotype_cbox" name="root_length_phenotype_cbox"> Root Length
			</td>
			<td>
				<input type="checkbox" id="root_growth_rate_phenotype_cbox" name="root_growth_rate_phenotype_cbox"> Root Growth Rate
			</td>
		</tr>		
	</tbody>
	</table>

	<!-- Contains all the genotype metadata which can be chosen -->
	<table class="table table-bordered table-hover table-condensed">
	<thead>
		<th colspan="7">Step 3 <i class="icon-arrow-right"></i> Choose metadata</th>
	</thead>
	<tbody>
		<tr>
			<td>
				<input type="checkbox" id="population_type_meta_cbox" name="population_type_meta_cbox"> Population Type
			</td>
            <td>
                <input type="checkbox" id="isolate_meta_cbox" name="isolate_meta_cbox"> Isolate
            </td>			
			<td>
				<input type="checkbox" id="plate_name_meta_cbox" name="plate_name_meta_cbox"> Plate Name
			</td>
            <td>
                <input type="checkbox" id="packet_name_meta_cbox" name="packet_name_meta_cbox"> Packet Name
            </td>
            <td>
                <input type="checkbox" id="culture_meta_cbox" name="culture_meta_cbox"> Culture
            </td>
            <td>
                <input type="checkbox" id="ear_number_meta_cbox" name="ear_number_meta_cbox"> Ear Number
            </td>
            <td>
                <input type="checkbox" id="growing_season_meta_cbox" name="growing_season_meta_cbox"> Growing Season
            </td>            
		</tr>
		<tr>
			<td>
				<input type="checkbox" id="field_year_meta_cbox" name="field_year_meta_cbox"> Field Year
			</td>
            <td>
                <input type="checkbox" id="male_parent_meta_cbox" name="male_parent_meta_cbox"> Male Parent
            </td>			
			<td>
				<input type="checkbox" id="female_parent_meta_cbox" name="female_parent_meta_cbox"> Female Parent
			</td>
            <td>
                <input type="checkbox" id="male_parent_name_meta_cbox" name="male_parent_name_meta_cbox"> Male Parent Name
            </td>
            <td>
                <input type="checkbox" id="female_parent_name_meta_cbox" name="female_parent_name_meta_cbox"> Female Parent Name
            </td>
            <td>
                <input type="checkbox" id="family_meta_cbox" name="family_meta_cbox"> Family
            </td>
            <td>
                <input type="checkbox" id="genotype_meta_cbox" name="genotype_meta_cbox"> Genotype
            </td>            
		</tr>
		<tr>
			<td>
				<input type="checkbox" id="notes_meta_cbox" name="notes_meta_cbox"> Notes
			</td>
            <td>
                <input type="checkbox" id="crossing_instructions_meta_cbox" name="crossing_instructions_meta_cbox"> Crossing Instructions
            </td>			
			<td>
				<input type="checkbox" id="collaborator_meta_cbox" name="collaborator_meta_cbox"> Collaborator
			</td>
            <td>
                <input type="checkbox" id="plate_position_meta_cbox" name="plate_position_meta_cbox"> Plate Position
            </td>
            <td>
                <input type="checkbox" id="cob_position_x_meta_cbox" name="cob_position_x_meta_cbox"> COB Position X
            </td>
            <td>
                <input type="checkbox" id="cob_position_y_meta_cbox" name="cob_position_y_meta_cbox"> COB Position Y
            </td>
            <td>
                <input type="checkbox" id="file_location_meta_cbox" name="file_location_meta_cbox"> File Location
            </td>                 
		</tr>
		<tr>
			<td>
				<input type="checkbox" id="weights_repetition_meta_cbox" name="weights_repetition_meta_cbox"> Weights Repetiton
			</td>    
            <td>
                <input type="checkbox" id="weights_idx_meta_cbox" name="weights_idx_meta_cbox"> Weights IDX
            </td>			
			<td>
				<input type="checkbox" id="spectra_repetition_meta_cbox" name="spectra_repetition_meta_cbox"> Spectra Repetition
			</td>
            <td>
                <input type="checkbox" id="spectra_idx_meta_cbox" name="spectra_idx_meta_cbox"> Spectra IDX
            </td>
            <td>
                <input type="checkbox" id="spectra_light_tube_meta_cbox" name="spectra_light_tube_meta_cbox"> Light Tube
            </td>
            <td>
                <input type="checkbox" id="spectra_operator_meta_cbox" name="spectra_operator_meta_cbox"> Operator
            </td>
		</tr>						
	</tbody>
	</table>

	<!-- Contains all the phenotypes which can be chosen -->
	<table class="table table-bordered table-hover table-condensed">
	<thead>
		<th colspan="3">Step 3.1 <i class="icon-arrow-right"></i> 
			<input type="checkbox" id="marker_cbox" name="marker_cbox"> Show genomic information
		</th>
	</thead>
	</tbody>
	</table>

	<!--  Contains the various filters and constraints to be applied the key data elements -->
	<table class="table table-bordered table-hover table-condensed">
	<thead>
		<th colspan="3">Step 4 <i class="icon-arrow-right"></i> Choose constraints/filters</th>
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
					<option selected>ALL</option>
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

	<!-- Generate the CSV file -->

	<div align="center">
		<button id="csv-generator" type="submit" class="btn btn-large btn-danger"><i class="icon-download-alt"></i> Generate CSV</button>  
    </div>

	</form> <!-- End of form -->

	</div> <!-- End of main div container -->

	<script>

	// Apply the default page display when the page loads based on the default report type
	$(document).ready(function() {
		var default_report_type = $("#report_type").find('option:selected').text();
		change_display(default_report_type);

		// To ensure that the loading gif is hidden when the page is initially loaded
		$('#results_loading').hide();
	});

	// Dynamically change form based on the select report type value chosen in dropdown
	$("#report_type").on('change', function() {
		var report_type = $(this).val();
		change_display(report_type);
	});

	// Changes the display of the web page based on the report chosen
	function change_display(report_type)
	{
		change_phenotype_display(report_type);
		change_phenotype_metadata_display(report_type);
		change_phenotype_genomic_metadata_display(report_type);

		// change the filter value to a default value
		$("#filter_type_value").val("ALL");
	}

	// Modifies the display behaviour of phenotype checkboxes based on report type.
	function change_phenotype_display(report_type)
	{
		var phenotype_tags = $("[id$='_phenotype_cbox']");
		phenotype_tags.removeAttr('checked');
		if(report_type == "") {
			phenotype_tags.parent().show();
		}
		else if(report_type == "Raw Weight/Spectra") {
			phenotype_tags.parent().hide(); // Hide all the tags first
			$("#raw_weight_spectra_phenotype_cbox").parent().show(); // Show relevant tags now
		}
		else {
			phenotype_tags.parent().show(); // Show all the tags first
			$("#raw_weight_spectra_phenotype_cbox").parent().hide(); // Hide relevant tags now
		}
	}

	// Modifies the display behaviour of phenotype metadata checkboxes based on report type.
	function change_phenotype_metadata_display(report_type)
	{
		var phenotype_metadata_tags = $("[id$='_meta_cbox']");
		phenotype_metadata_tags.removeAttr('checked');		
		if(report_type == "Raw Weight/Spectra") {
			phenotype_metadata_tags.parent().show(); // Show all the tags
		}
		else if(report_type == "Raw Phenotypes") {
			phenotype_metadata_tags.parent().show(); // Show all the tags

			// Hide all weight/spectra related metadata attributes
			$("#weights_repetition_meta_cbox").parent().hide();
			$("#weights_idx_meta_cbox").parent().hide();
			$("#spectra_repetition_meta_cbox").parent().hide();
			$("#spectra_idx_meta_cbox").parent().hide();
			$("#spectra_light_tube_meta_cbox").parent().hide();
			$("#spectra_operator_meta_cbox").parent().hide();						
		}
		else {
			phenotype_metadata_tags.parent().hide(); // Hide all the tags first

			// Show relevant tags now
			$("#population_type_meta_cbox").parent().show(); 
			$("#isolate_meta_cbox").parent().show(); 
		}
	}

	// Modifies the display behaviour of phenotype genomic metadata checkboxes based on report type.
	function change_phenotype_genomic_metadata_display(report_type)
	{
		var phenotype_genomic_metadata_tags = $("[id$='marker_cbox']");
		phenotype_genomic_metadata_tags.removeAttr('checked');
		if(report_type == "Raw Weight/Spectra" || report_type == "Raw Phenotypes" || report_type == "Phenotype Correlation") {
			phenotype_genomic_metadata_tags.parent().closest("table").hide();
		}
		else {
			phenotype_genomic_metadata_tags.parent().closest("table").show();
		}

	}

	// Validates the form
	function validate_form()
	{
		// Check if a report type is chosen
		var report_type = $("#report_type").val();
		if(report_type == "") {
			alert("Please choose a report type before submitting the form !!");
			return false;
		}

		// Atleast one phenotype must be selected
		var is_atleast_one_phenotype_selected = false;
		$("[id$='_phenotype_cbox']").each(function() {
			if($(this).is(':checked') == true) {
				is_atleast_one_phenotype_selected =  true;
			}
		});
		if(is_atleast_one_phenotype_selected == false) {
			alert("Please select atleast one phenotype to proceed !!");
			return false;
		}

		// Atleast one genotype must be selected
		var is_atleast_one_phenotype_metadata_selected = false;
		$("[id$='_meta_cbox']").each(function() {
			if($(this).is(':checked') == true) {
				is_atleast_one_phenotype_metadata_selected = true;
			}
		});
		if(is_atleast_one_phenotype_metadata_selected == false) {
			alert("Please select atleast one phenotype_metadata to proceed !!");
			return false;
		}

		// make the background translucent and show a loading gif to make
		// the web application interactive and let the user know that some
		// processing is going on in the background.
		$("#form_container").css({ opacity: 0.25 });
		$('#results_loading').show();

		return true;
	}

	</script
</body>

</html>
