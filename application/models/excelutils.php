<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// DB wrapper to get relevant data from maize database.
class Excelutils extends CI_Model {
    
    function __construct() {
    	// call parent's constructor
    	parent::__construct();
        $this->load->library('excel');
    }

    // Generic function that takes a query and fetches results from database
    function generate_excel_file($results)
    {
        print "Generating excel file for " . count($results) . " records";
        $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle('Maize Phenotype Data');

        $filename='maize_run_output.xls'; //save our workbook as this file name
        header('Content-Type: application/vnd.ms-excel'); //mime type
        header('Content-Disposition: attachment;filename="'.$filename.'"'); //tell browser what's the file name
        header('Cache-Control: max-age=0'); //no cache

        // Iterate over the db data here
        foreach($results as $i => $result) {
            $j=0;
            foreach($result as $result_col) {
                var_dump($result_col);
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow($j, $i, $result_col);
                $j++;
            }
        }

        //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the 
        // filename extension, also the header mime type) if you want to save it as .XLSX Excel 2007 format
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');  
        //force user to download the Excel file without writing it to server's HD
        $objWriter->save('php://output');

        print "Finished generating excel file ..";
    }

}