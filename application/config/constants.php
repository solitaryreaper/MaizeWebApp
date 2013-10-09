<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

define('URL','http://barracuda.botany.wisc.edu/MaizeWebApp/');
define('IMG',URL.'assets/img/');
define('CSS',URL.'assets/css/');
define('JS',URL.'assets/js/');
define('TEMP_CSV_FILES_DIRECTORY', URL.'data/temp_csv_files/');

// Application specific constants
// Phenotype measurement data tables
define('KERNEL_3D_TABLE', 'kernel_3d');
define('KERNEL_DIMENSIONS_TABLE', 'kernel_dims_tbl');
define('PREDICTIONS_TABLE', 'predictions');
define('ROOT_TIP_MEASUREMENTS_TABLE', 'root_tip_measurements');
define('RAW_WEIGHT_SPECTRA_TABLE', 'raw_weights_spectra_tbl');
define('AVG_WEIGHT_SPECTRA_TABLE', 'avg_weights_spectra_tbl');
define('STD_WEIGHT_SPECTRA_TABLE', 'std_weights_spectra_tbl');

// Phenotype metadata tables
define('KERNEL_TABLE', 'kernels');
define('PLATE_TABLE', 'kernel_plates');
define('POPULATION_LINES', 'population_lines');

/* End of file constants.php */
/* Location: ./application/config/constants.php */
