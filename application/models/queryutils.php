<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Queryutils extends CI_Model {
    
    // Define a mapping of the phenotype metadata to the actual <table>.<column name> in maize database
	public static $phenotype_metadata_map = 
		array(
			"isolate" => "population.isolate", "population_type" => "population.type",
			"plate_name" => "plates.plate_name", "culture" => "plates.culture",
			"ear_number" => "plates.ear_number", "growing_season" => "plates.growing_season",
			"field_year" => "plates.field_year", "male_parent" => "plates.male_parent",
			"female_parent" => "plates.female_parent", "male_parent_name" => "plates.male_parent_name",
			"female_parent_name" => "plates.female_parent_name", "family" => "plates.family",
			"genotype" => "plates.genotype", "notes" => "plates.notes",
			"crossing_instructions" => "plates.crossing_instructions", "packet_name" => "plates.packet_name",
			"collaborator" => "plates.collaborator", "plate_position" => "kernels.plate_position",
			"cob_position_x" => "kernels.cob_position_x", "cob_position_y" => "kernels.cob_position_y",
			"weights_repetition" => "raw_weights_spectra_vw.weights_repetition", "weights_idx" => "raw_weights_spectra_vw.weights_idx",
			"spectra_repetition" => "raw_weights_spectra_vw.spectra_repetition", "raw_weights_spectra_vw.spectra_idx" => "spectra_idx",
			"spectra_light_tube" => "raw_weights_spectra_vw.spectra_light_tube", "spectra_operator" => "raw_weights_spectra_vw.spectra_operator"				
	);

    function __construct() {
    	// call parent's constructor
    	parent::__construct();
    }

    // Dynamically generates the query form the form parameters.
    public function get_query_from_form_vars($form_vars)
    {
    	$start_time = microtime(true);
        $query = "";
    	$phenotype_facts_subquery = $this->get_phenotype_subquery($form_vars);
    	$phenotype_meta_subquery = $this->get_phenotype_meta_subquery($form_vars);
    	$phenotype_query_select_clause = 
    		$this->get_phenotype_query_select_clause($phenotype_meta_subquery, $phenotype_facts_subquery, $form_vars);

    	$phenotype_query_group_by_clause = $this->get_phenotype_query_group_by_clause($phenotype_meta_subquery, $form_vars);

    	$query .= "SELECT " . $phenotype_query_select_clause . " FROM (" . 
    		       $phenotype_facts_subquery . 
    		       " ) pfacts JOIN (" . 
				   $phenotype_meta_subquery . 
				   ") pmeta ON pfacts.kernel_id1 = pmeta.kernel_id " .
				   $phenotype_query_group_by_clause;

		log_message('info', "Final query : " . $query);
        $end_time = microtime(true);
        log_message("info", "Dynamic final query generation took : " . ($end_time - $start_time) . " seconds." );

    	return $query;
    }

    // Generates the list of columns to show in SELECT or GROUP BY clause in the final phenotype query.
    // This depends on a number of factors like the type of report chosen
    private function get_query_display_columns($query, $field_prefix, $field_suffix, $excluded_columns, $query_part_type)
    {
        $start_time = microtime(true);

        // get the list of column headers that would be generated by this subquery
        // Optimization : Instead of firing the query and getting the list of column headers, 
        // use simple string manipulation to get the query column headers

        $sql = strtolower($query);
        preg_match('/select(.*?)from/', $sql, $matches); # extract only the select clause
        $only_select_clause = trim($matches[1]);
        
        $tokens = explode(",", $only_select_clause);
        $fields = array();
        foreach($tokens as $token) {
            $token = trim($token);
            // contains AS alias, so take the last string
            // table.col_name AS col1 ==> col1
            $field = "";
            if (strpos(strtolower($token),'as') !== false) {
              $temp = explode(" ", $token);
              $field = end($temp);
            }
            // Remove prefix. and take the suffix of the string
            // table.col_name ==> col_name , col_name => col_name
            else {
                $temp = explode(".", $token);
                $field = end($temp);
            }
        
            if(!in_array($field, $excluded_columns)) {
                array_push($fields, trim($field));  
            }        
  
        }

        $query_display_columns_string = "";
        foreach($fields as $field) {
        	if($query_part_type == 'SELECT') {
        		$query_display_columns_string .= $field_prefix . $field . $field_suffix . " AS " . $field ." , ";
        	}
        	else {
        		$query_display_columns_string .= $field_prefix . $field . $field_suffix . " , ";
        	}

        }

	    $query_display_columns_string = trim($query_display_columns_string);
	    $query_display_columns_string = rtrim($query_display_columns_string, ',');        

        log_message("info", "Time taken to generated display columns : " . (microtime(true) - $start_time) . " seconds ");
        return $query_display_columns_string;
    }

     // Returns the select clause to be used for the phenotype query
    private function get_phenotype_query_select_clause($phenotype_metadata_query, $phenotype_measurement_query, $form_vars)
    {
    	// If no aggregate function report is chosen, show all data at the granularity of kernel id. 
    	// Else, just group at population level attributes and aggregate all the facts.

    	$phenotype_query_select_clause = "";
    	if(!$this->is_aggregate_function_report($form_vars)) {
    		$excluded_columns = array("kernel_id1");
    		$phenotype_metadata_select_string = 
    			$this->get_query_display_columns($phenotype_metadata_query, 'pmeta.', '', $excluded_columns, 'SELECT');
    		$phenotype_measurement_select_string = 
    			$this->get_query_display_columns($phenotype_measurement_query, 'pfacts.', '', $excluded_columns, 'SELECT');

    		$phenotype_query_select_clause = $phenotype_metadata_select_string . " , " . $phenotype_measurement_select_string;
    	}
    	else {
   			$excluded_columns = array("kernel_id", "kernel_id1");
   			$aggregate_function = $this->get_aggregate_function($form_vars);

    		$phenotype_metadata_select_string = 
    			$this->get_query_display_columns($phenotype_metadata_query, 'pmeta.', '', $excluded_columns, 'SELECT');
    		$phenotype_measurement_select_string = 
				$this->get_query_display_columns($phenotype_measurement_query, $aggregate_function . '(pfacts.', ')', 
					$excluded_columns, 'SELECT');
    		$phenotype_query_select_clause = $phenotype_metadata_select_string . " , " . $phenotype_measurement_select_string;
    	}

    	log_message('info', " Select clause : " . $phenotype_query_select_clause);
    	return $phenotype_query_select_clause;
    }

    // Returns the group by clause to be used for the phenotype query
    private function get_phenotype_query_group_by_clause($phenotype_metadata_query, $form_vars)
    {
    	// If no aggregate function report is chosen, don't group any data. If aggregate function
    	// is chosen, use the population metadata for grouping.

    	$phenotype_query_group_by_clause = " ";
    	if($this->is_aggregate_function_report($form_vars)) {
    		$phenotype_query_group_by_clause .= " GROUP BY ";
    		$excluded_columns = array("kernel_id", "kernel_id1");
    		$phenotype_query_group_by_clause .= 
    			$this->get_query_display_columns($phenotype_metadata_query, 'pmeta.', '', $excluded_columns, 'GROUP');
    	}

    	log_message('info', "Group By clause : " . $phenotype_query_group_by_clause);
    	return $phenotype_query_group_by_clause;
    }

    // Determines if the chosen report involves data aggregation or not
    private function is_aggregate_function_report($form_vars)
    {
    	if(in_array('Raw Weight/Spectra', $form_vars) || in_array('Raw Phenotypes', $form_vars)) {
    		return false;
    	}
    	else {
    		return true;
    	}
    }

    // Determines the aggregate function to used for the query
    private function get_aggregate_function($form_vars)
    {
    	$aggregate_function = "";
    	if(in_array('Average Phenotypes', $form_vars)) {
			$aggregate_function = 'avg';
    	}
    	else if(in_array('Standard Deviation Phenotypes', $form_vars)) {
    		$aggregate_function = 'stddev';
    	}

    	return $aggregate_function;
    }

    // Dynamically generates the subquery that generates the phenotype metadata information
    // to be shown like population type, plate name etc. for kernels.
    private function get_phenotype_meta_subquery($form_vars)
    {
    	$start_time = microtime(true);
        // 1) Dynamically generate the SELECT clause based on the chosen genotype metadata
    	$subquery_select_clause = "SELECT kernels.id AS kernel_id ";
    	foreach($form_vars as $form_key => $form_value) {
    		if(array_key_exists($form_key, Queryutils::$phenotype_metadata_map)) {
    			$subquery_select_clause .= " , " . Queryutils::$phenotype_metadata_map[$form_value];
    		}
    	}

    	// 2) Set of tables to be joined for getting the metadata
    	$subquery_body =  
			" FROM kernels kernels " .
			" JOIN kernel_plates plates " .
			" ON (kernels.plate_id = plates.id) " .
			" JOIN population_lines population " .
			" ON (plates.population_line_id = population.id) ";

		// 3) Dynamically generate the WHERE clause for the query based on the filters chosen
     	$subquery_where_clause = " WHERE 1=1 ";

     	// handle population type filter
     	if(array_key_exists('filter_type_value', $form_vars)) {
     		$subquery_where_clause .=  " AND population.type = '" . $form_vars['filter_type_value'] ."' ";
     	}

     	// handle plate name filter
     	if(array_key_exists('filter_plate_option', $form_vars)) {
     		$subquery_where_clause .= " AND plates.plate_name " . 
     			$this->get_regex_value($form_vars['filter_plate_option'], $form_vars['filter_plate_value']) ;
     	}

     	// handle packet name filter
     	if(array_key_exists('filter_packet_option', $form_vars)) {
     		$subquery_where_clause .= " AND plates.packet_name " . 
     			$this->get_regex_value($form_vars['filter_packet_option'], $form_vars['filter_packet_value']) ;     		
     	}     	

     	$subquery = $subquery_select_clause . $subquery_body.  $subquery_where_clause;
     	log_message('info', "Phenotype metadata subquery generated : " . $subquery);

        $end_time = microtime(true);
        log_message('info', "Time for METADATA subquery : " . ($end_time - $start_time) . " seconds");

     	return $subquery;
    }

    // Returns the regex search pattern for filter
    private function get_regex_value($operator, $value)
    {
    	$regex_value = "";
    	if($operator == "EQUALS") {
    		$regex_value = " = " . $value;
    	}
    	else if($operator == "CONTAINS") {
    		$regex_value = " LIKE '%" . $value . "%'";
    	}
    	else if($operator == "STARTS WITH") {
			$regex_value = " LIKE '" . $value . "%'";
    	}
    	else if($operator == "ENDS WITH"){
			$regex_value = " LIKE '%" . $value . "'";
    	}

    	return $regex_value;
    }

    // Dynamically generate the subquery that collects all the phenotype measurement data for
    // kernels.
    private function get_phenotype_subquery($form_vars)
    {
    	$start_time = microtime(true);
        $last_phenotype_alias = null;

    	// TODO : Too much code duplication. This can be condensed.
		$subquery_body = "";
		$included_tables_map = array();
    	if(array_key_exists('kernel_3d', $form_vars)) {
			$subquery_body .= $this->get_phenotype_table($form_vars['kernel_3d']) . " k1 ";
			$last_phenotype_alias = "k1";
			$included_tables_map[KERNEL_3D_TABLE] = "k1";
    	}
 		if(array_key_exists('predictions', $form_vars)) {
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " FULL OUTER JOIN ";
			}    		
			$subquery_body .= $this->get_phenotype_table($form_vars['predictions']) . " k2 ";
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " ON (" . $last_phenotype_alias . ".kernel_id = k2.kernel_id) ";
			}
			$last_phenotype_alias = "k2";		
			$included_tables_map[PREDICTIONS_TABLE] = "k2";
    	}
 		if(array_key_exists('root_tip_measurements', $form_vars)) {
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " FULL OUTER JOIN ";
			}    		
			$subquery_body .= $this->get_phenotype_table($form_vars['root_tip_measurements']) . " k3 ";
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " ON (" . $last_phenotype_alias . ".kernel_id = k3.kernel_id) ";
			}
			$last_phenotype_alias = "k3";		
			$included_tables_map[ROOT_TIP_MEASUREMENTS_TABLE] = "k3";
    	}    	
    	if(array_key_exists('raw_weight_spectra', $form_vars)) {
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " FULL OUTER JOIN ";
			}    		
			$subquery_body .= $this->get_phenotype_table($form_vars['raw_weight_spectra']) . " k4 "; 
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " ON (" . $last_phenotype_alias . ".kernel_id = k4.kernel_id) ";
			}
			$last_phenotype_alias = "k4";		
			$included_tables_map[RAW_WEIGHT_SPECTRA_TABLE] = "k4";			
    	}
    	if(array_key_exists('avg_weight_spectra', $form_vars)) {
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " FULL OUTER JOIN ";
			}    		
			$subquery_body .= $this->get_phenotype_table($form_vars['avg_weight_spectra']) . " k5 "; 
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " ON (" . $last_phenotype_alias . ".kernel_id = k5.kernel_id) ";
			}
			$last_phenotype_alias = "k5";
			$included_tables_map[AVG_WEIGHT_SPECTRA_TABLE] = "k5";					
    	}
    	if(array_key_exists('std_weight_spectra', $form_vars)) {
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " FULL OUTER JOIN ";
			}    		
			$subquery_body .= $this->get_phenotype_table($form_vars['std_weight_spectra']) . " k6 "; 
			if(isset($last_phenotype_alias)) {
				$subquery_body .= " ON (" . $last_phenotype_alias . ".kernel_id = k6.kernel_id) ";
			}
			$last_phenotype_alias = "k6";
            $included_tables_map[STD_WEIGHT_SPECTRA_TABLE] = "k6";					
    	}
        if(array_key_exists('kernel_dims', $form_vars)) {
            if(isset($last_phenotype_alias)) {
                $subquery_body .= " FULL OUTER JOIN ";
            }           
            $subquery_body .= $this->get_phenotype_table($form_vars['kernel_dims']) . " k7 "; 
            if(isset($last_phenotype_alias)) {
                $subquery_body .= " ON (" . $last_phenotype_alias . ".kernel_id = k7.kernel_id) ";
            }
            $last_phenotype_alias = "k7";
            $included_tables_map[KERNEL_DIMENSIONS_TABLE] = "k7";                  
        }        

		$subquery_select_clause = " SELECT ";
		foreach($included_tables_map as $table_name=>$table_prefix) {
			$subquery_select_clause .= $this->get_fact_columns_for_phenotype($table_name, $table_prefix) . " , ";
        }
        log_message('info', "Phenotype subquery body : " . $subquery_body);

        $included_tables_aliases = array_values($included_tables_map);
        log_message('info', "Aliases : " . $included_tables_aliases[0]);
		$subquery_select_clause .= $included_tables_aliases[0] . ".kernel_id AS kernel_id1";

        $subquery = $subquery_select_clause . " FROM " . $subquery_body . " ";
     	log_message('info', "Phenotype subquery generated : " . $subquery);

        log_message('info', "Phenotype FACT query time : " . (microtime(true) - $start_time) . " seconds");
    	return $subquery;
    }

    // Get all the measurement data columns for this phenotype
    private function get_fact_columns_for_phenotype($phenotype_table, $phenotype_query_prefix)
    {
		$phenotype_select_query = 
			" SELECT ('" . $phenotype_query_prefix . ".'". " ||  column_name ) as col_name " . 
			" FROM information_schema.columns " .
			" WHERE table_catalog='maize' AND table_name = '" . $phenotype_table  ."' AND ".
	      		" data_type IN ('integer', 'double precision') AND ". 
	      		" column_name NOT IN ('id', 'kernel_id') ".
			" ORDER BY ordinal_position ";
		log_message('info', "Query fired to get measurement data " . $phenotype_select_query);

	    // get a comma separated list of field values
		$fields_as_select_clause = "";
		$query_output = $this->db->query($phenotype_select_query);
		foreach($query_output->result() as $row) {
	        $fields_as_select_clause .= $row->col_name . " , "; 
	    }	

	    $fields_as_select_clause = trim($fields_as_select_clause);
	    $fields_as_select_clause = rtrim($fields_as_select_clause, ',');

	    log_message('info', "Comma separated phenotype measurement data list : " . $fields_as_select_clause);

		return $fields_as_select_clause;
    }
 
 	// If the phenotype data resides in a view, stages it in a temporary table and returns
 	// its identifier. Using temporary tables instead of on-the-fly view computation leads
 	// to faster turnaround times for the queries.
 	private function get_phenotype_table($phenotype_db_object_name)
 	{
		$phenotype_table = $phenotype_db_object_name;
        /*
		if(substr($phenotype_db_object_name, -strlen("_vw")) === "_vw") {
			log_message('info', "Found a phenotype view : " . $phenotype_db_object_name);

			// Append a random number to end of the table, to avoid any name collision with any temporary
			// tablss created in future runs. On the safer side, make sure that the temporary tables are
			// deleted once their work is finished.
			$temp_table_name = $phenotype_db_object_name . "_temp_tbl_" . rand(0, 10000);
			$temp_stage_query = "SELECT * INTO " . $temp_table_name . " FROM " . $phenotype_db_object_name . " ";

			$this->db->query($temp_stage_query);
			$phenotype_table = $temp_table_name;
		}
        */

		return $phenotype_table;
 	}
 }
