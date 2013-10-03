<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// DB wrapper to get relevant data from maize database.
class Csvutils extends CI_Model {
    
    function __construct() {
    	// call parent's constructor
    	parent::__construct();
    }

    // Generic function that generates a CSV file out of database results
    function generate_csv_file($db_results, $report_type)
    {
        // remove all whitespaces from string
        $report_type_suffix = str_replace(' ', '_', $report_type);
        // add a constant at the end
        $report_type_suffix .= "_" . rand(0, 10000);

        $file_name = "maize_results_" . strtolower($report_type_suffix) . ".csv";
        $file_dir = getcwd() . "/data/temp_csv_files/";

        log_message('info', "Writing file to path .. " . $file_dir . $file_name);
        $output = fopen($file_dir . $file_name, "w");
        foreach ($db_results as $result) {
            $row = array();
            foreach($result as $result_col) {
                $row[] = $result_col;
            }
            fputcsv($output, $row);
        }

        fclose($output);

        return TEMP_CSV_FILES_DIRECTORY . $file_name;
    }

}