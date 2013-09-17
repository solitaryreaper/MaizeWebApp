<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// DB wrapper to get relevant data from maize database.
class Excelutils extends CI_Model {
    
    function __construct() {
    	// call parent's constructor
    	parent::__construct();
        $this->load->library('excel');
    }

    // Generic function that takes a query and fetches results from database
    function generate_excel_file($result_header, $db_results)
    {
        $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle('Maize Phenotype Data');

        $filename='maize_run_output.xls'; //save our workbook as this file name
        header('Content-type: application/ms-excel');
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header('Cache-Control: max-age=0'); //no cache

        // Set the header for the excel sheet here
        // TODO

        
        // Iterate over the db data here
        $row=1;
        foreach($db_results as $result) {
            $col=0;
            foreach($result as $result_col) {
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $result_col);
                $col++;
            }
            $row++;
        }

        // Choose appropriate file format for excel sheet
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5'); 
        $objWriter->save('php://output'); // makes sure file is downloaded at client
    }

}