<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// DB wrapper to get relevant data from maize database.
class Maizedao extends CI_Model {
    
    function __construct() {
    	// call parent's constructor
    	parent::__construct();

        $this->load->model('queryutils');
        $this->load->model('csvutils');
    }

    // Returns three header rows containing all the genomic information - marker name,
    // chromosome, map location.
    public function get_genomic_header_rows()
    {
        $start_time = microtime(true);
        
        $genomic_query = $this->queryutils->get_all_genomic_metadata_query();
        $marker_names_header = array();
        $marker_chromosomes_header = array();
        $marker_map_locations_header = array();

        $db_results = $this->db->query($genomic_query);
        foreach($db_results->result() as $row) {
            array_push($marker_names_header, $row->name);
            array_push($marker_chromosomes_header, $row->chromosome);
            array_push($marker_map_locations_header, $row->map_location);
        }

        $header_rows_section = array();
        $header_rows_section['NAME'] = $marker_names_header;
        $header_rows_section['CHROMOSOME'] = $marker_chromosomes_header;
        $header_rows_section['MAP_LOCATION'] = $marker_map_locations_header;

        $end_time = microtime(true);

        log_message("info", "Marker names generated in : "  . ($end_time - $start_time) . " seconds.");

        return $header_rows_section;
    }

    // Gets an in-memory crosstab representation of population id with its genomic metadata
    public function get_population_genomic_meta_crosstab($marker_names)
    {
        $start_time = microtime(true);

        $population_lines_values = array();
        $population_lines_genomic_data_query = $this->queryutils->get_population_lines_genomic_meta_query();
        $db_results = $this->db->query($population_lines_genomic_data_query);
        foreach($db_results->result() as $row) {
            $pid = $row->population_line_id;
            $marker_name = $row->marker_name;
            $marker_value = $row->marker_value;

            $pid_values = NULL;
            if(array_key_exists($pid, $population_lines_values)) {
                $pid_values = $population_lines_values[$pid];
            }
            else {
                $pid_values = array();
            }

            $pid_values[$row->marker_name] = $row->marker_value;
            $population_lines_values[$pid] = $pid_values;
        }
        $end_time = microtime(true);
        log_message("info", "Fetched vertical population data in " . ($end_time - $start_time) . " seconds");

        $start_time = microtime(true);
        $population_lines_crosstab = array();
        foreach($population_lines_values AS $pid => $values) {
            $pid_all_values = array();
            foreach($marker_names as $marker_name) {
                $marker_value = NULL;
                if(array_key_exists($marker_name, $values)) {
                    $marker_value = $values[$marker_name];
                }

                array_push($pid_all_values, $marker_value);
            }

            $population_lines_crosstab[$pid] = $pid_all_values;
        }

        $end_time = microtime(true);
        log_message("info", "Created population crosstab in-memory " . ($end_time - $start_time) . " seconds" );

        return $population_lines_crosstab;
    }

    // Fires a query, decorates it with genomic information and stages the data into a CSV file and
    // returns its download link.
    public function load_query_results_with_genomic_info_into_csv_file($query, $report_type)
    {
        log_message("info", "Decorating phenotype results with genomic info ..");

        // Get the genomic data
        $header_rows = $this->get_genomic_header_rows();
        $population_genomic_crosstab = $this->get_population_genomic_meta_crosstab($header_rows['NAME']);

        // Get the phenotype data
        $start_time = microtime(true);
        $db_results = $this->db->query($query);
        $db_header = array();
        foreach($db_results->list_fields() as $field) {
            array_push($db_header, $field);
        }        

        $joined_results = array();
        foreach($db_results->result() as $pid_row) {
            $pid = $pid_row->population_line_id;
            if(array_key_exists($pid, $population_genomic_crosstab)) {
                $pid_genomic_info = $population_genomic_crosstab[$pid];
                array_push($joined_results, array_merge($pid_row, $pid_genomic_info));    
                log_message('info', "## PID is present : " . $pid);            
            }
            else {
                log_message('info', "## PID not present : " . $pid);
            }
        }
        $end_time = microtime(true);
        log_message("info", "Time taken to fetch db results : " . ($end_time - $start_time) . " seconds => " . count($joined_results));

        log_message("info", "Marker results : " . var_dump($header_rows));
        $start_time = microtime(true);
        $empty_db_header_clone_array = array_fill(0,count($db_header), "");
        $final_op_header_row_1 = array_merge($db_header, $header_rows['NAME']);
        $final_op_header_row_2 = array_merge($empty_db_header_clone_array, $header_rows['CHROMOSOME']);
        $final_op_header_row_3 = array_merge($empty_db_header_clone_array, $header_rows['MAP_LOCATION']);

        $final_output = array_merge($final_op_header_row_1, $final_op_header_row_2, $final_op_header_row_3, $joined_results);
        $end_time = microtime(true);
        log_message("info", "Time taken to generate merge output array : " . ($end_time - $start_time) . " seconds");

        return $this->csvutils->generate_csv_file($final_output, $report_type);
    }

    // Fires a query against the database and then loads the results into a CSV file.
    // Returns the location of CSV file that contains the data.
    public function load_query_results($query, $report_type, $is_genomic_info_reqd)
    {
        if(!$is_genomic_info_reqd) {
            return $this->load_query_results_into_csv_file($query, $report_type);
        }
        else {
            return $this->load_query_results_with_genomic_info_into_csv_file($query, $report_type);
        }
    }

    // Uses PSQL internal copy function to stage query results into CSV file and return download link.
    function load_query_results_into_csv_file($query, $report_type)
    {
        $csv_file_name = $this->get_temp_csv_file_name($report_type);
        $csv_file_path = getcwd() . "/data/temp_csv_files/" . $csv_file_name;
        log_message('info', "Temp CSV file path : " . $csv_file_path);

        $dump_data_to_csv_sql = "\COPY ( " .  $query . " ) TO '" . $csv_file_path . "'  CSV HEADER";
        $cmd = "psql -U maizeuser -d maize -c \"" . $dump_data_to_csv_sql . "\"";

        log_message('info', "Bulk CSV load SQL : " . $cmd);

        $start_time = microtime(true);
        $output_value = shell_exec($cmd);
        log_message('info', "Time taken to load data into CSV file is : " . (microtime(true) - $start_time) . " seconds");

        return TEMP_CSV_FILES_DIRECTORY . $csv_file_name;
    }

    // Returns the number of rows in the CSV file which has the dumped data.
    function get_results_count($csv_file_url)
    {
        // Generate the file system path for the file
        $tokens = explode("/", $csv_file_url);
        $csv_file_name = end($tokens) ;
        $csv_file_path = getcwd() . "/data/temp_csv_files/" . $csv_file_name;
        log_message('info', "Extracted CSV file path : " . $csv_file_path);

        $count = 0;
        $cmd = "cat " . $csv_file_path . " | wc -l";
        $count = shell_exec($cmd);

        return $count;
    }

    // Returns the absolute path of the csv file to be used for loading data.
    function get_temp_csv_file_name($report_type)
    {
        // remove all whitespaces from string
        $report_type_suffix = str_replace(" ", '_', $report_type);
        $report_type_suffix = str_replace('/', '_', $report_type_suffix);
        $report_type_suffix = trim($report_type_suffix);

        // add a constant at the end
        $report_type_suffix .= "_" . rand(0, 10000);

        $file_name = "maize_results_" . strtolower($report_type_suffix) . ".csv";

        return $file_name;
    }

    // Drops any temporary tables created during the processing phase
    function drop_temporary_tables()
    {
        $get_temp_tables_query = "SELECT table_name FROM information_schema.tables WHERE table_name LIKE '%_temp_tbl_%' ";
        $temp_tables_query_output = $this->db->query($get_temp_tables_query);

        $temp_tables = array();
        foreach($temp_tables_query_output->result() as $row) {
            log_message('info', "Temporary table : " . $row->table_name);
            array_push($temp_tables, $row->table_name);  
        }

        foreach($temp_tables as $temp_table) {
            $temp_table_drop_query = "DROP TABLE " . $temp_table;
            $this->db->query($temp_table_drop_query);
            log_message('info', "Dropped temporary table " . $temp_table);
        } 
    }
}