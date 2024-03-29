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

		$this->load->library('sqlformatter');
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
		$report_type = $form_vars['report_type'];

		// 3) Loads the query results into a temporary CSV file
		$csv_file_download_link = "";

		// For genomic information, we need to really complex dynamic SQL processing
		$is_genomic_info_reqd = array_key_exists(IS_GENOMIC_INFO_REQD, $form_vars) ? True : False;
		log_message("info", "IS genome info ? " . $is_genomic_info_reqd);

		$csv_file_download_link = $this->maizedao->load_query_results($query, $report_type, $is_genomic_info_reqd);
		$num_results = 0;
		log_message("info", "CSV download link : " . $csv_file_download_link);
		if(!is_null($csv_file_download_link)) {
			$num_results = $this->maizedao->get_results_count($csv_file_download_link);
		}
		else {
			log_message("error", "CSV file not generated. Please look into the issue !!");
		}

		// 4) Convert data to CSV format for download, only if number of db rows generated is non-zero
		if($num_results > 0) {
			// Genomic information has three header rows in CSV file.
			if($is_genomic_info_reqd) {
				$num_results = $num_results - 3;
			}
			// Normal report has one header row in CSV file.
			else {
				$num_results = $num_results - 1;
			}
		}

		// 5) Drop any temporary tables created for intermediate processing
		$this->maizedao->drop_temporary_tables();

		// 6) Render the results page
		$results_data = array("count" => ($num_results), "csv_file_path" => $csv_file_download_link, 
			"query" => $this->sqlformatter->format($query), "report_type" => $report_type);

		$this->load->view('results', $results_data);
	}

	// Extracts the form parameters from
	private function get_form_parameters()
	{
		log_message('info', "Extracting form variables ..");

		$form_vars = array();

		// get the report type chosen
		$form_vars['report_type'] = trim($this->input->post('report_type'));

		// get the phenotypes selected and map them to their database tables
		if($this->input->post('kernel_3d_phenotype_cbox') == "on") {
			$form_vars['kernel_3d'] = KERNEL_3D_TABLE;
		}
		if($this->input->post('kernel_dims_phenotype_cbox') == "on") {
			$form_vars['kernel_dims'] = KERNEL_DIMENSIONS_TABLE;
		}		
		if($this->input->post('predictions_phenotype_cbox') == "on") {
			$form_vars['predictions'] = PREDICTIONS_TABLE;
		}
		if($this->input->post('root_tip_phenotype_cbox') == "on") {
			$form_vars['root_tip_measurements'] = ROOT_TIP_MEASUREMENTS_TABLE;
		}		
		if($this->input->post('raw_weight_spectra_phenotype_cbox') == "on") {
			$form_vars['raw_weight_spectra'] = RAW_WEIGHT_SPECTRA_TABLE;
		}
		if($this->input->post('avg_weight_spectra_phenotype_cbox') == "on") {
			$form_vars['avg_weight_spectra'] = AVG_WEIGHT_SPECTRA_TABLE;
		}
		if($this->input->post('std_weight_spectra_phenotype_cbox') == "on") {
			$form_vars['std_weight_spectra'] = STD_WEIGHT_SPECTRA_TABLE;
		}
		if($this->input->post('root_length_phenotype_cbox') == "on") {
			$form_vars['root_length'] = ROOT_LENGTH_TABLE;
		}
		if($this->input->post('root_growth_rate_phenotype_cbox') == "on") {
			$form_vars['root_growth_rate'] = ROOT_GROWTH_RATE_TABLE;
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

		// get the phenotype genomic information
		if($this->input->post(IS_GENOMIC_INFO_REQD) == "on") {
			$form_vars[IS_GENOMIC_INFO_REQD] = IS_GENOMIC_INFO_REQD;
			log_message("info", "Genomic info metadata present !!");
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

		return $form_vars;
	}

}
