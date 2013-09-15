<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// DB wrapper to get relevant data from maize database.
class Maizedao extends CI_Model {
    
    function __construct() {
    	// call parent's constructor
    	parent::__construct();
    }

    // Generic function that takes a query and fetches results from database
    function execute_query($query)
    {
        $output = $this->db->query($query);
        print "\nFound " . $output->num_rows() . " results for query " . $query;
        return $output->result();
    }

}