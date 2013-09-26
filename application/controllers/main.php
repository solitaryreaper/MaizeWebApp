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
		$maize_results = $this->maizedao->get_query_results($query);
		log_message('info', "Found " . count($maize_results) . " maize db results for query " . $query);

		// 4) Convert data to CSV format for download
		$this->csvutils->generate_csv_file($maize_results);
	}

	// Extracts the form parameters from
	private function get_form_parameters()
	{
		log_message('info', "Extracting form variables ..");

		$form_vars = array();


		// get the report type chosen
		$form_vars['report_type'] = $this->input->post('report_type');

		// get the phenotypes selected and map them to their database tables
		if($this->input->post('kernel_3d_cbox') == "on") {
			$form_vars['kernel_3d'] = KERNEL_3D_TABLE;
		}
		if($this->input->post('predictions_cbox') == "on") {
			$form_vars['predictions'] = PREDICTIONS_TABLE;
		}
		if($this->input->post('root_tip_measurements_cbox') == "on") {
			$form_vars['root_tip_measurements'] = ROOT_TIP_MEASUREMENTS_TABLE;
		}		
		if($this->input->post('raw_weight_spectra_cbox') == "on") {
			$form_vars['raw_weight_spectra'] = RAW_WEIGHT_SPECTRA_TABLE;
		}
		if($this->input->post('avg_weight_spectra_cbox') == "on") {
			$form_vars['avg_weight_spectra'] = AVG_WEIGHT_SPECTRA_TABLE;
		}
		if($this->input->post('std_weight_spectra_cbox') == "on") {
			$form_vars['std_weight_spectra'] = STD_WEIGHT_SPECTRA_TABLE;
		}

		// get the phenotypes metadata to show
		// HACK : pheontype metadata has been encoded as <metadata_name>_meta_cbox to easily 
		// identify the metadata variables in the the form array.
		foreach($this->input->post() as $form_key=>$form_value) {
			if(strpos($form_key, "_meta_cbox") > 0) {
				if($this->input->post($form_key) == "on") {
					// strip off the marker to indicate phenotype metadata
					$new_key = str_replace("_meta_cbox", "", $form_key);
					$form_vars[$new_key] = $new_key;
				}
			}
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

		//log_message('info', "Extracted form variables : " . print_r($form_vars));

		return $form_vars;
	}

}
