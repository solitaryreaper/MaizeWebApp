<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// DB wrapper to get relevant data from maize database.
class Maizedao extends CI_Model {
    
    function __construct() {
    	// call parent's constructor
    	parent::__construct();
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

    // Fires a query against the database and then loads the results into a CSV file.
    // Returns the location of CSV file that contains the data.
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