/*
	Postgresql 8.* doesn't have the concept of materialized views. This is a simple attempt
	to update the persistent tables based on views in maize database. It is triggered once
	every day via a cron job and checks for the latest incremental changes to the view and
	pushes it to the corresponding tables. It assumes that the all changes in maize database
	are only INSERTS and there are no UPDATES or DELETES.
*/

-- Update raw_weights_spectra_tbl

DROP TABLE raw_weights_spectra_tbl;

SELECT * INTO raw_weights_spectra_tbl FROM raw_weights_spectra_vw;
CREATE INDEX kernel_id_raw_ix ON raw_weights_spectra_tbl(kernel_id);

-- Update avg_weights_spectra_tbl
DROP TABLE avg_weights_spectra_tbl;

SELECT * INTO avg_weights_spectra_tbl FROM averageweightspectra_vw;
CREATE INDEX kernel_id_avg_ix ON avg_weights_spectra_tbl(kernel_id);

-- Update std_weights_spectra_tbl
DROP TABLE std_weights_spectra_tbl;

SELECT * INTO std_weights_spectra_tbl FROM standarddeviationweightspectra_vw;
CREATE INDEX kernel_id_std_ix ON std_weights_spectra_tbl(kernel_id);

-- Update kernel_dims_tbl
DROP TABLE kernel_dims_tbl;

SELECT * INTO kernel_dims_tbl FROM kernel_dims;
CREATE INDEX kernel_id_dims_ix ON kernel_dims_tbl(kernel_id);

-- Update root tip measurement crosstab table
DROP TABLE root_tip_measurements_crosstab;

SELECT * FROM generate_root_tip_crosstab('root_tip_measurements', 'root_tip_measurements_crosstab'); 
CREATE INDEX root_tip_ct_ix ON root_tip_measurements_crosstab(kernel_id);


