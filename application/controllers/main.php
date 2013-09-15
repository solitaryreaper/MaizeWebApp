<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Main controller class that takes the user input and passes the parameters to the db models
 * to fetch the phenotype data to be shown in CSV files.
 */
class Main extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->model('maizedao');
        $this->load->model('queryutils');
        $this->load->model('excelutils');
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

		// 2) Get the dynamic query based on the form parameters
		$query = "select * from kernel_3d limit 10";
		print "Generating data for query : " . $query;

		// 3) Fetch the results from maize database
		$maize_results = $this->maizedao->execute_query($query);

		// 4) Convert data to CSV format for download
		$this->excelutils->generate_excel_file($maize_results);
	}
}
