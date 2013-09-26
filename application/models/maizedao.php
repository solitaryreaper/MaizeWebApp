<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// DB wrapper to get relevant data from maize database.
class Maizedao extends CI_Model {
    
    function __construct() {
    	// call parent's constructor
    	parent::__construct();
    }

    // Generic function that takes a query and fetches results from database
    function get_query_results($query)
    {
        $query_output = $this->db->query($query);

        // Fetch the query column names/header first
        $query_results = array();
        $query_results_header = array();
        foreach($query_output->list_fields() as $field) {
            array_push($query_results_header, $field);
        }

        // Get the actual query results
        $query_results_values = $query_output->result();

        // Merge the column header and actual database data records
        array_push($query_results, $query_results_header);
        return array_merge($query_results, $query_results_values);
    }

}