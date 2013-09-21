<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// DB wrapper to get relevant data from maize database.
class Csvutils extends CI_Model {
    
    function __construct() {
    	// call parent's constructor
    	parent::__construct();
    }

    // Generic function that generates a CSV file out of database results
    function generate_csv_file($db_results)
    {
        header("Content-type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=maize_results.csv");

        // This ensures that the csv file is offered for download at browser client
        $output = fopen("php://output", "w");
        foreach ($db_results as $result) {
            $row = array();
            foreach($result as $result_col) {
                $row[] = $result_col;
            }
            fputcsv($output, $row);
        }
        fclose($output);
    }

}