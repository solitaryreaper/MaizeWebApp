/*
	Postgresql 8.* doesn't have the concept of materialized views. This is a simple attempt
	to update the persistent tables based on views in maize database. It is triggered once
	every day via a cron job and checks for the latest incremental changes to the view and
	pushes it to the corresponding tables. It assumes that the all changes in maize database
	are only INSERTS and there are no UPDATES or DELETES.

	These materialized views are highly denormalized to enable easy reporting and keep the application
	layer really simple.
*/

-- Update raw_weights_spectra_report_tbl
DROP TABLE reporting.raw_weights_spectra_report_tbl;

SELECT * INTO reporting.raw_weights_spectra_report_tbl FROM reporting.raw_weights_spectra_report_vw;
CREATE INDEX kernel_id_weights_spectra_report_ix ON reporting.raw_weights_spectra_report_tbl(kernel_id);

-- Update avg_weights_spectra_report_tbl
DROP TABLE reporting.avg_weights_spectra_report_tbl;

SELECT * INTO reporting.avg_weights_spectra_report_tbl FROM reporting.avg_weights_spectra_report_vw;
CREATE INDEX kernel_id_weights_spectra_avg_ix ON reporting.avg_weights_spectra_report_tbl(kernel_id);

-- Update std_weights_spectra_report_tbl
DROP TABLE reporting.std_weights_spectra_report_tbl;

SELECT * INTO reporting.std_weights_spectra_report_tbl FROM reporting.std_weights_spectra_report_vw;
CREATE INDEX kernel_id_weights_spectra_std_ix ON reporting.std_weights_spectra_report_tbl(kernel_id);

-- Update kernel_dims_report_tbl
DROP TABLE reporting.kernel_dims_report_tbl;

SELECT * INTO reporting.kernel_dims_report_tbl FROM public.kernel_dims;
CREATE INDEX kernel_id_dims_ix ON reporting.kernel_dims_report_tbl(kernel_id);

-- Update predictions_report_tbl
DROP TABLE reporting.predictions_report_tbl;

SELECT * INTO reporting.predictions_report_tbl FROM public.predictions;
CREATE INDEX predictions_report_ix ON reporting.predictions_report_tbl(kernel_id);

-- Update kernel_3d_report_tbl
DROP TABLE reporting.kernel_3d_report_tbl;

SELECT * INTO reporting.kernel_3d_report_tbl FROM public.kernel_3d;
CREATE INDEX kernel_3d_report_ix ON reporting.kernel_3d_report_tbl(kernel_id);

-- Update root tip measurement crosstab table and the report table
DROP TABLE reporting.root_tip_measurements_crosstab;

SELECT * FROM generate_root_tip_crosstab('public.root_tip_measurements', 'reporting.root_tip_measurements_crosstab'); 
CREATE INDEX root_tip_ct_ix ON reporting.root_tip_measurements_crosstab(kernel_id);

DROP TABLE reporting.root_tip_measurements_report_tbl;

SELECT * INTO reporting.root_tip_measurements_report_tbl FROM reporting.root_tip_measurements_report_vw;
CREATE INDEX root_tip_report_ix ON reporting.root_tip_measurements_report_tbl(kernel_id);

-- Update root length crosstab table and the report table
DROP TABLE reporting.root_length_crosstab;

SELECT * FROM generate_root_length_crosstab('public.root_length', 'reporting.root_length_crosstab');
CREATE INDEX root_length_ct_ix ON root_length_crosstab(kernel_id);

DROP TABLE reporting.root_length_report_tbl;

SELECT INTO reporting.root_length_report_tbl FROM reporting.root_length_report_vw;
CREATE INDEX root_length_report_ix ON reporting.root_length_report_tbl(kernel_id);

-- Update root growth rate crosstab table and the report table
DROP TABLE reporting.root_growth_rate_crosstab;

SELECT * FROM generate_root_length_crosstab('public.root_growth_rate', 'reporting.root_growth_rate_crosstab');
CREATE INDEX root_growth_rate_ct_ix ON reporting.root_growth_rate_crosstab(kernel_id);

DROP TABLE reporting.root_growth_rate_report_tbl;

SELECT * INTO reporting.root_growth_rate_report_tbl FROM reporting.root_growth_rate_report_vw;
CREATE INDEX root_growth_rate_report_ix ON reporting.root_growth_rate_report_tbl(kernel_id);
