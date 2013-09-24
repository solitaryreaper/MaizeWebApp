<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Queryutils extends CI_Model {
    
    function __construct() {
    	// call parent's constructor
    	parent::__construct();
    }

    // Dynamically generates the query form the form parameters
    public function get_query_from_form_vars($form_vars)
    {
    	$query = "";
    	$phenotype_query = $this->get_phenotype_subquery($form_vars);
    	$genotype_meta_subquery = $this->get_genotype_meta_subquery($form_vars);
    	$query .= "SELECT * FROM (" . $genotype_meta_subquery . ") genotypes LEFT OUTER JOIN (" . 
    		$phenotype_query . ") phenotypes ON genotypes.kernel_id = phenotypes.kernel_id";

		log_message('info', "Final query : " . $query);

    	return $query;
    }

    // Generate the subquery that generates the genotype metadata information
    // to be shown like population type, plate name etc. for kernels.
    private function get_genotype_meta_subquery($form_vars)
    {
    	// 1) Dynamically generate the SELECT clause based on the chosen genotype metadata
    	$subquery_select_clause = "SELECT kernels.id AS kernel_id ";
    	if(array_key_exists('population_type', $form_vars)) {
    		$subquery_select_clause .= " , population.type ";
    	}
    	if(array_key_exists('plate_name', $form_vars)) {
			$subquery_select_clause .= " , plates.plate_name ";
    	}
    	if(array_key_exists('packet_name', $form_vars)) {
			$subquery_select_clause .= " , plates.packet_name ";
    	}
    	if(array_key_exists('isolate', $form_vars)) {
			$subquery_select_clause .= " , population.isolate ";
    	}

    	// 2) Set of tables to be joined for getting the metadata
    	$subquery_body =  
			"FROM kernels kernels " .
			"LEFT OUTER JOIN kernel_plates plates " .
			"ON (kernels.plate_id = plates.id) " .
			"LEFT OUTER JOIN population_lines population " .
			"ON (plates.population_line_id = population.id) ";

		// 3) Dynamically generate the WHERE clause for the query based on the filters chosen
     	$subquery_where_clause = "WHERE 1=1 ";

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
     	log_message('info', "Genotype subquery generated : " . $subquery);

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

    // Generate the subquery that collects all the phenotype measurement data for
    // kernels.
    private function get_phenotype_subquery($form_vars)
    {
    	$subquery = "SELECT * FROM ";
    	$last_phenotype_alias = null;

    	if(array_key_exists('kernel_3d', $form_vars)) {
			$subquery .= $form_vars['kernel_3d'] . " k1 ";
			$last_phenotype_alias = "k1";
    	}
	   	if(array_key_exists('predictions', $form_vars)) {
			if(isset($last_phenotype_alias)) {
				$subquery .= " FULL OUTER JOIN ";
			}    		
			$subquery .= $form_vars['predictions'] . " k2 ";
			if(isset($last_phenotype_alias)) {
				$subquery .= " ON " . $last_phenotype_alias . ".kernel_id = k2.kernel_id ";
			}
			$last_phenotype_alias = "k2";		
    	}
    	if(array_key_exists('raw_weight_spectra', $form_vars)) {
			if(isset($last_phenotype_alias)) {
				$subquery .= " FULL OUTER JOIN ";
			}    		
			$subquery .= $form_vars['raw_weight_spectra'] . " k3 "; 
			if(isset($last_phenotype_alias)) {
				$subquery .= " ON " . $last_phenotype_alias . ".kernel_id = k3.kernel_id ";
			}
			$last_phenotype_alias = "k3";					
    	}
    	if(array_key_exists('avg_weight_spectra', $form_vars)) {
			if(isset($last_phenotype_alias)) {
				$subquery .= " FULL OUTER JOIN ";
			}    		
			$subquery .= $form_vars['avg_weight_spectra'] . " k4 "; 
			if(isset($last_phenotype_alias)) {
				$subquery .= " ON (" . $last_phenotype_alias . ".kernel_id = k4.kernel_id ";
			}
			$last_phenotype_alias = "k4";					
    	}
    	if(array_key_exists('std_weight_spectra', $form_vars)) {
			if(isset($last_phenotype_alias)) {
				$subquery .= " FULL OUTER JOIN ";
			}    		
			$subquery .= $form_vars['std_weight_spectra'] . " k5 "; 
			if(isset($last_phenotype_alias)) {
				$subquery .= " ON " . $last_phenotype_alias . ".kernel_id = k5.kernel_id ";
			}
			$last_phenotype_alias = "k5";					
    	}

     	log_message('info', "Phenotype subquery generated : " . $subquery);
    	return $subquery;
    }
 
 }