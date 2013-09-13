<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Main controller class that takes the user input and passes the parameters to the db models
 * to fetch the phenotype data to be shown in CSV files.
 */
class Main extends CI_Controller {

	public function index()
	{
		$this->load->view('main');
	}
}
