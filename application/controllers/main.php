<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Main controller class that takes the user input and passes the parameters to the db models
 * to fetch the phenotype data to be shown in CSV files.
 */
class Main extends CI_Controller {

    function __construct() {
        parent::__construct();

   		$this->load->helper('form');
   		$this->load->helper('url');

        $this->load->model('maizedao');
        $this->load->model('queryutils');
        $this->load->model('csvutils');
    }

	public function index()
	{
		$this->load->view('main');
	}

	// Parse the form parameters, use it to generate the dynamic query, fetch relevant rows from
	// database and then upload it as a CSV file for downloading.
	public function load_maize_data()
	{
		// 1) Extract the form parameters here. These would be used to dynamically generate the query
		$form_vars = $this->get_form_parameters();

		// 2) Get the dynamic query based on the form parameters
		$query = $this->queryutils->get_query_from_form_vars($form_vars);

		// 3) Fetch the results from maize database
		//$maize_results = $this->maizedao->get_query_results($query);
		//log_message('info', "Found " . count($maize_results) . " maize db results for query " . $query);

		// 4) Convert data to CSV format for download
		//$this->csvutils->generate_csv_file($maize_results);
	}

	// Extracts the form parameters from
	private function get_form_parameters()
	{
		log_message('info', "Extracting form variables ..");

		$form_vars = array();

		// get the phenotypes selected and map them to their database tables
		if($this->input->post('kernel_3d_cbox') == "on") {
			$form_vars['kernel_3d'] = KERNEL_3D_TABLE;
		}
		if($this->input->post('predictions_cbox') == "on") {
			$form_vars['predictions'] = PREDICTIONS_TABLE;
		}
		if($this->input->post('raw_weight_spectra_cbox') == "on") {
			$form_vars['raw_weight_spectra'] = WEIGHT_SPECTRA_TABLE;
		}
		if($this->input->post('avg_weight_spectra_cbox') == "on") {
			$form_vars['avg_weight_spectra'] = WEIGHT_SPECTRA_TABLE;
		}
		if($this->input->post('std_weight_spectra_cbox') == "on") {
			$form_vars['std_weight_spectra'] = WEIGHT_SPECTRA_TABLE;
		}

		// get the genotypes to show and map to their actual column names 
		if($this->input->post('population_type_cbox') == "on") {
			$form_vars['population_type'] = 'type';
		}
		if($this->input->post('plate_name_cbox') == "on") {
			$form_vars['plate_name'] = 'plate_name';
		}
		if($this->input->post('packet_name_cbox') == "on") {
			$form_vars['packet_name'] = 'packet_name';
		}
		if($this->input->post('isolate_cbox') == "on") {
			$form_vars['isolate'] = 'isolate';
		}

		// get the population type filter
		if($this->input->post('filter_type_value') != "ALL") {
			$form_vars['filter_type_value'] = $this->input->post('filter_type_value');			
		}

		// get the plate name filter
		$filter_plate_value = trim($this->input->post('filter_plate_value'));
		if(!(empty($filter_plate_value))) {
			$form_vars['filter_plate_option'] = $this->input->post('filter_plate_option');
			$form_vars['filter_plate_value'] = $filter_plate_value;
		}

		// get the packet name filter
		$filter_packet_value = trim($this->input->post('filter_packet_value'));
		if(!(empty($filter_packet_value))) {
			$form_vars['filter_packet_option'] = $this->input->post('filter_packet_option');			
			$form_vars['filter_packet_value'] = $filter_packet_value;
		}		

		// get the data aggregation mode chosen
		$form_vars['aggregation_mode'] = $this->input->post('aggregate_func');

		log_message('info', "Extracted form variables : " . print_r($form_vars));

		return $form_vars;
	}

}
