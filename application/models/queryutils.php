<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Queryutils extends CI_Model
{
    
    // Define a mapping of the phenotype metadata to the actual <table>.<column name> in maize database
    public static $phenotype_metadata_map = array(
        "isolate" => "population.isolate", "population_type" => "population.type", 
        "plate_name" => "plates.plate_name", "culture" => "plates.culture", 
        "ear_number" => "plates.ear_number", "growing_season" => "plates.growing_season", 
        "field_year" => "plates.field_year", "male_parent" => "plates.male_parent", 
        "female_parent" => "plates.female_parent", "male_parent_name" => "plates.male_parent_name", 
        "female_parent_name" => "plates.female_parent_name", "family" => "plates.family", 
        "genotype" => "plates.genotype", "notes" => "plates.notes", 
        "crossing_instructions" => "plates.crossing_instructions", 
        "packet_name" => "plates.packet_name", "collaborator" => "plates.collaborator", 
        "plate_position" => "kernels.plate_position", "cob_position_x" => "kernels.cob_position_x", 
        "cob_position_y" => "kernels.cob_position_y"
    );
    
    // Defines a mapping of phenotype table non factual columns
    public static $phenotype_non_fact_columns_map = array(
        "weights_repetition" => "raw_weights_spectra_report_tbl.weights_repetition", 
        "weights_idx" => "raw_weights_spectra_report_tbl.weights_idx", 
        "spectra_repetition" => "raw_weights_spectra_report_tbl.spectra_repetition", 
        "spectra_idx" => "raw_weights_spectra_report_tbl.spectra_idx", 
        "spectra_light_tube" => "raw_weights_spectra_report_tbl.spectra_light_tube", 
        "spectra_operator" => "raw_weights_spectra_report_tbl.spectra_operator",
        "file_location" => "file_location"
    );

    // Defines a mapping of form phenotype table identifier to actual db table name
    public static $form_key_to_table_map = array(
        "kernel_3d" => KERNEL_3D_TABLE, "predictions" => PREDICTIONS_TABLE, 
        "root_tip_measurements" => ROOT_TIP_MEASUREMENTS_TABLE, "raw_weight_spectra" => RAW_WEIGHT_SPECTRA_TABLE, 
        "avg_weight_spectra" => AVG_WEIGHT_SPECTRA_TABLE, "std_weight_spectra" => STD_WEIGHT_SPECTRA_TABLE, 
        "kernel_dims" => KERNEL_DIMENSIONS_TABLE, "root_length" => ROOT_LENGTH_TABLE,
        "root_growth_rate" => ROOT_GROWTH_RATE_TABLE
    );

    function __construct()
    {
        // call parent's constructor
        parent::__construct();
    }
    
    // Dynamically generates the query form the form parameters.
    public function get_query_from_form_vars($form_vars)
    {
        $start_time                    = microtime(true);
        $query                         = "";

        // HACK : handle correlation queries differently because they don't follow the structure
        // that was designed for dynamnic query generation
        $report_type = $form_vars['report_type'];
        if($report_type == "Phenotype Line Correlation" || $report_type == "Phenotype Correlation") {
            log_message('info', 'Inside correlation query ..');
            $query = $this->get_phenotype_correlation_query($report_type, $form_vars);
            log_message('info', 'Correlation query : ' . $query);
        }
        else {
            $phenotype_facts_subquery      = $this->get_phenotype_subquery($form_vars);
            $phenotype_meta_subquery       = $this->get_phenotype_meta_subquery($form_vars);
            $phenotype_query_select_clause = $this->get_phenotype_query_select_clause($phenotype_meta_subquery, $phenotype_facts_subquery, $form_vars);
            
            $phenotype_query_group_by_clause = $this->get_phenotype_query_group_by_clause($phenotype_meta_subquery, $form_vars);
            
            $query .= "SELECT " . $phenotype_query_select_clause . " FROM (" . $phenotype_facts_subquery . " ) pfacts JOIN (" . $phenotype_meta_subquery . ") pmeta ON pfacts.kernel_id1 = pmeta.kernel_id " . $phenotype_query_group_by_clause;
        }
     
        log_message('info', "Final query : " . $query);
        $end_time = microtime(true);
        log_message("info", "Dynamic final query generation took : " . ($end_time - $start_time) . " seconds.");
        
        return $query;
    }

    // Generates the dynamic query for correlation reports
    private function get_phenotype_correlation_query($report_type, $form_vars)
    {
        $query_where_clause = "";

        // Find which phenotype tables to include
        $tables_to_include = "";
        foreach(Queryutils::$form_key_to_table_map as $form_key => $table_name) {
            log_message("info", "Key : " . $form_key . ", Table : " . $table_name);
            if (!array_key_exists($form_key, $form_vars)) {
                continue;
            }

            $tables_to_include .= "'" . $table_name . "' , ";
        }
        $tables_to_include = trim($tables_to_include);
        $tables_to_include = rtrim($tables_to_include, ",");

        $query_where_clause .= " WHERE phen_pairs.p1table IN (" . $tables_to_include . 
            ") AND phen_pairs.p2table IN (" . $tables_to_include . ")";        

        // Handle population type filter
        if (array_key_exists('filter_type_value', $form_vars)) {
            $query_where_clause .= " AND population.type = '" . $form_vars['filter_type_value'] . "' ";
        }

        $phenotype_correlation_query = "";
        // Correlation at population id and phenotype pair id level
        if($report_type == "Phenotype Line Correlation") {
            $phenotype_correlation_query = "SELECT 
                population.isolate,
                population.type,
                phen_pairs.p1table,
                phen_pairs.p1field,
                phen_pairs.p2table,
                phen_pairs.p2field,
                corr_tbl.corr_coeff,
                corr_tbl.stddev1,
                corr_tbl.stddev2,
                corr_tbl.variance1,
                corr_tbl.variance2
            FROM reporting.correlation_pid_phenotype_pairs_tbl corr_tbl
            JOIN population_lines population
                ON (corr_tbl.population_line_id = population.id)
            JOIN phenotype_pairs phen_pairs
                ON (corr_tbl.pheno1_id = phen_pairs.p1id AND corr_tbl.pheno2_id = phen_pairs.p2id)";     

            $phenotype_correlation_query .= $query_where_clause . " ORDER BY population.isolate, population.type";
        }
        // Correlation at population type and phenotype pair id level
        else {
            $phenotype_correlation_query = "SELECT 
                population.type,
                phen_pairs.p1table,
                phen_pairs.p1field,
                phen_pairs.p2table,
                phen_pairs.p2field,
                corr_tbl.corr_coeff,
                corr_tbl.stddev1,
                corr_tbl.stddev2,
                corr_tbl.variance1,
                corr_tbl.variance2
            FROM reporting.correlation_ptype_phenotype_pairs_tbl corr_tbl
            JOIN population_lines population
                ON (corr_tbl.population_type = population.type::text)
            JOIN phenotype_pairs phen_pairs
                ON (corr_tbl.pheno1_id = phen_pairs.p1id AND corr_tbl.pheno2_id = phen_pairs.p2id)";     

            $phenotype_correlation_query .= $query_where_clause . " ORDER BY population.type";
        }

        return $phenotype_correlation_query;
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
        foreach ($tokens as $token) {
            $token = trim($token);
            // contains AS alias, so take the last string
            // table.col_name AS col1 ==> col1
            $field = "";
            if (strpos(strtolower($token), 'as') !== false) {
                $temp  = explode(" ", $token);
                $field = end($temp);
            }
            // Remove prefix. and take the suffix of the string
            // table.col_name ==> col_name , col_name => col_name
            else {
                $temp  = explode(".", $token);
                $field = end($temp);
            }
            
            if (!in_array($field, $excluded_columns)) {
                array_push($fields, trim($field));
            }
            
        }
        
        $query_display_columns_string = "";
        foreach ($fields as $field) {
            if ($query_part_type == 'SELECT') {
                $query_display_columns_string .= $field_prefix . $field . $field_suffix . " AS " . $field . " , ";
            } else {
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
        if (!$this->is_aggregate_function_report($form_vars)) {
            $excluded_columns                    = array(
                "kernel_id",
                "kernel_id1",
                "population_line_id"
            );
            $phenotype_metadata_select_string    = $this->get_query_display_columns($phenotype_metadata_query, 'pmeta.', '', $excluded_columns, 'SELECT');
            $phenotype_measurement_select_string = $this->get_query_display_columns($phenotype_measurement_query, 'pfacts.', '', $excluded_columns, 'SELECT');
            
            $phenotype_query_select_clause = $phenotype_metadata_select_string . " , " . $phenotype_measurement_select_string;
        } else {
            $excluded_columns   = array(
                "kernel_id",
                "kernel_id1"
            );
            $aggregate_function = $this->get_aggregate_function($form_vars);
            
            $phenotype_metadata_select_string    = $this->get_query_display_columns($phenotype_metadata_query, 'pmeta.', '', $excluded_columns, 'SELECT');
            $phenotype_measurement_select_string = $this->get_query_display_columns($phenotype_measurement_query, $aggregate_function . '(pfacts.', ')', $excluded_columns, 'SELECT');
            $phenotype_query_select_clause       = $phenotype_metadata_select_string . " , " . $phenotype_measurement_select_string;
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
        if ($this->is_aggregate_function_report($form_vars)) {
            $phenotype_query_group_by_clause .= " GROUP BY ";
            $excluded_columns = array(
                "kernel_id",
                "kernel_id1"
            );
            $phenotype_query_group_by_clause .= $this->get_query_display_columns($phenotype_metadata_query, 'pmeta.', '', $excluded_columns, 'GROUP');
        }
        
        log_message('info', "Group By clause : " . $phenotype_query_group_by_clause);
        return $phenotype_query_group_by_clause;
    }
    
    // Determines if the chosen report involves data aggregation or not
    private function is_aggregate_function_report($form_vars)
    {
        if (in_array('Raw Weight/Spectra', $form_vars) || in_array('Raw Phenotypes', $form_vars)) {
            return false;
        } else {
            return true;
        }
    }
    
    // Determines the aggregate function to used for the query
    private function get_aggregate_function($form_vars)
    {
        $aggregate_function = "";
        if (in_array('Average Phenotypes', $form_vars)) {
            $aggregate_function = 'avg';
        } else if (in_array('Standard Deviation Phenotypes', $form_vars)) {
            $aggregate_function = 'stddev';
        }
        
        return $aggregate_function;
    }
    
    // Dynamically generates the subquery that generates the phenotype metadata information
    // to be shown like population type, plate name etc. for kernels.
    private function get_phenotype_meta_subquery($form_vars)
    {
        $start_time             = microtime(true);
        // 1) Dynamically generate the SELECT clause based on the chosen genotype metadata
        $subquery_select_clause = "SELECT kernels.id AS kernel_id, population.id AS population_line_id ";
        foreach ($form_vars as $form_key => $form_value) {
            if (array_key_exists($form_key, Queryutils::$phenotype_metadata_map)) {
                $subquery_select_clause .= " , " . Queryutils::$phenotype_metadata_map[$form_value];
            }
        }
        
        // 2) Set of tables to be joined for getting the metadata
        $subquery_body = " FROM public.kernels kernels " . " JOIN public.kernel_plates plates " . 
            " ON (kernels.plate_id = plates.id) " . " JOIN population_lines population " . 
            " ON (plates.population_line_id = population.id) ";
        
        // 3) Dynamically generate the WHERE clause for the query based on the filters chosen
        $subquery_where_clause = " WHERE 1=1 ";
        
        // handle population type filter
        if (array_key_exists('filter_type_value', $form_vars)) {
            $subquery_where_clause .= " AND population.type = '" . $form_vars['filter_type_value'] . "' ";
        }
        
        // handle plate name filter
        if (array_key_exists('filter_plate_option', $form_vars)) {
            $subquery_where_clause .= " AND plates.plate_name " . $this->get_regex_value($form_vars['filter_plate_option'], $form_vars['filter_plate_value']);
        }
        
        // handle packet name filter
        if (array_key_exists('filter_packet_option', $form_vars)) {
            $subquery_where_clause .= " AND plates.packet_name " . $this->get_regex_value($form_vars['filter_packet_option'], $form_vars['filter_packet_value']);
        }
        
        $subquery = $subquery_select_clause . $subquery_body . $subquery_where_clause;
        log_message('info', "Phenotype metadata subquery generated : " . $subquery);
        
        $end_time = microtime(true);
        log_message('info', "Time for METADATA subquery : " . ($end_time - $start_time) . " seconds");
        
        return $subquery;
    }
    
    // Returns the regex search pattern for filter
    private function get_regex_value($operator, $value)
    {
        $regex_value = "";
        if ($operator == "EQUALS") {
            $regex_value = " = '" . $value . "'";
        } else if ($operator == "CONTAINS") {
            $regex_value = " LIKE '%" . $value . "%'";
        } else if ($operator == "STARTS WITH") {
            $regex_value = " LIKE '" . $value . "%'";
        } else if ($operator == "ENDS WITH") {
            $regex_value = " LIKE '%" . $value . "'";
        }
        
        return $regex_value;
    }
    
    // Dynamically generate the subquery that collects all the phenotype measurement data for
    // kernels.
    private function get_phenotype_subquery($form_vars)
    {
        $start_time           = microtime(true);
        $last_phenotype_alias = null;
        
        $subquery_body       = "";
        $included_tables_map = array();

        $table_alias_counter = 1;
        foreach(Queryutils::$form_key_to_table_map as $form_key => $table_name) {
            log_message("info", "Key : " . $form_key . ", Table : " . $table_name);
            if (!array_key_exists($form_key, $form_vars)) {
                continue;
            }

            if (isset($last_phenotype_alias)) {
                $subquery_body .= " FULL OUTER JOIN ";
            }

            $table_alias_name = "k" . $table_alias_counter;
            $subquery_body .= $table_name . "  " . $table_alias_name;
            if (isset($last_phenotype_alias)) {
                $subquery_body .= " ON (" . $last_phenotype_alias . ".kernel_id = " . $table_alias_name . ".kernel_id) ";
            }
            $last_phenotype_alias = $table_alias_name;
            $included_tables_map[$table_name] = $table_alias_name;
            $table_alias_counter = $table_alias_counter + 1;
        }

        $subquery_select_clause = " SELECT ";
        foreach ($included_tables_map as $table_name => $table_alias) {
            $subquery_select_clause .= $this->get_fact_columns_for_phenotype($table_name, $table_alias, $form_vars) . " , ";
        }

        log_message('info', "Phenotype subquery body : " . $subquery_body);
        
        $included_tables_aliases = array_values($included_tables_map);
        $subquery_select_clause .= $this->get_phenotype_query_id_column($included_tables_aliases);
        
        $subquery = $subquery_select_clause . " FROM " . $subquery_body . " ";
        log_message('info', "Phenotype subquery generated : " . $subquery);
        
        log_message('info', "Phenotype FACT query time : " . (microtime(true) - $start_time) . " seconds");
        return $subquery;
    }
    
    // When multiple phenotype tables have to be full joined to each other, then the identifier
    // column should be the first non-null value.
    private function get_phenotype_query_id_column($included_tables_aliases)
    {
        $id_column_select_snippet = "COALESCE(";
        foreach($included_tables_aliases as $table_alias) {
            $id_column_select_snippet .= " " . $table_alias . ".kernel_id , ";
        }
        $id_column_select_snippet .= " null) AS kernel_id1";

        return $id_column_select_snippet;
    }

    // Get all the measurement data columns for this phenotype
    private function get_fact_columns_for_phenotype($phenotype_table, $phenotype_query_prefix, $form_vars)
    {
        $table_name = $phenotype_table;
        $schema_name = "public"; // Default schema
        $index = strpos($phenotype_table, ".");

        // extract the schema and table name from the composite name
        if($index) {
            $table_name = substr($phenotype_table, $index+1);
            $schema_name = substr($phenotype_table, 0, $index);
        }
        $phenotype_select_query = " SELECT column_name  AS col_name " . " FROM information_schema.columns " . 
            " WHERE table_catalog='maize' AND table_name = '" . $table_name . 
            "' AND table_schema = '". $schema_name . "' AND column_name NOT IN ('id', 'kernel_id', 'repetitions') " . 
            " ORDER BY ordinal_position ";
        log_message('info', "Query fired to get measurement data " . $phenotype_select_query);
        
        // get a comma separated list of field values
        $fields_as_select_clause = "";
        $query_output            = $this->db->query($phenotype_select_query);
        $table_abbrev_prefix     = $this->get_table_abbreviation($phenotype_table);
        foreach ($query_output->result() as $row) {
            $col_name = $row->col_name;

            // Phenotype tables often have some pseudo non-factual meta columns. Those should be
            // included in the output only if they have been checked on the web page. Rest all
            // fact columns should always be included as they contain the actual measurement data.
            if( array_key_exists($col_name, Queryutils::$phenotype_non_fact_columns_map) && 
                !array_key_exists($col_name, $form_vars)) 
            {
                log_message("info", "Skipped phenotype non-fact column " . $col_name . " for table " . $phenotype_table);
                continue;
            }

            $fields_as_select_clause .= $phenotype_query_prefix . "." . $col_name . " AS " . $table_abbrev_prefix . "_" . $col_name . " , ";
        }
        
        $fields_as_select_clause = trim($fields_as_select_clause);
        $fields_as_select_clause = rtrim($fields_as_select_clause, ',');
        
        log_message('info', "Comma separated phenotype measurement data list : " . $fields_as_select_clause);
        
        return $fields_as_select_clause;
    }
    
    // Returns the abbreviation of a table name. This is prefixed with the column name to 
    // distinguish similar columns across multiple phenotype tables. For examples, wl_* columns
    // are present in both standard deviation and average weight spectra views. To avoid name
    // collisions, this is required.
    private function get_table_abbreviation($table_name)
    {
        $tokens       = explode('_', $table_name);
        $table_abbrev = "";
        
        # If a simple table name, just return first three characters
        if (count($tokens) == 1) {
            $table_abbrev = substr($tokens[0], 0, 3);
        }
        # Else take the first letter of each token and form a table abbreviation using that
        else {
            foreach ($tokens as $token) {
                $table_abbrev .= substr($token, 0, 1);
            }
        }
        
        return $table_abbrev;
    }
    
    // ---------------- Dynamic query generation for genotype information -------------------------
    
    // Query that returns all the genomic information
    public function get_all_genomic_metadata_query()
    {
        return "SELECT name, chromosone AS chromosome, map_location FROM marker_types WHERE map_location != 0 ORDER BY chromosone, map_location";
    }
    
    public function get_population_lines_genomic_meta_query()
    {
        $query = " SELECT m.population_line_id, mt.name AS marker_name, CAST(m.value AS text) AS marker_value  ";
        $query .= " FROM public.marker_types mt ";
        $query .= " JOIN public.markers m ";
        $query .= " ON (m.marker_type_id = mt.id) ";
        $query .= " WHERE mt.map_location != 0";
        $query .= " ORDER BY 1,2";
        
        return $query;
    }
}
