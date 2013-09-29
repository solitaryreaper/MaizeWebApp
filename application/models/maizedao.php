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