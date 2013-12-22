-- Crosstab generating function for root_length table
-- Invoke as SELECT * FROM generate_root_length_crosstab('root_length', 'root_length_crosstab');
CREATE OR REPLACE FUNCTION public.generate_root_length_crosstab(src_tbl_name text, ct_tbl_name text)
RETURNS text
LANGUAGE plpgsql
AS $function$
DECLARE
    cols_query VARCHAR;
    cols VARCHAR;
    sql_query VARCHAR;
BEGIN  
        cols_query:= 'SELECT '
|| quote_literal('')
|| '

||
array_to_string(

(array
(SELECT DISTINCT time_point FROM '
|| src_tbl_name
|| '
ORDER BY 1)
), '
|| quote_literal('" DOUBLE PRECISION , "timepoint_')


|| ' )
|| '
||
quote_literal('" DOUBLE PRECISION ')
|| ' ;

';
    EXECUTE cols_query INTO cols;
cols:= '"' || 'timepoint_' || cols;
    cols:=  'kernel_id INTEGER, ' || cols;

    sql_query := 'CREATE TABLE ' || ct_tbl_name || ' AS SELECT * FROM crosstab
(' ||
    quote_literal('SELECT kernel_id, time_point, length FROM ' || src_tbl_name
|| ' ORDER BY 1,2') || ', ' ||
    quote_literal('SELECT DISTINCT time_point FROM ' || src_tbl_name || '
ORDER BY 1') || ') '  || '
    AS x (' || cols || ')' ;

    EXECUTE sql_query;
    RETURN ct_tbl_name;
END;
$function$

-- Crosstab generating function for root_growth_rate table
-- Invoke as SELECT * FROM generate_root_growth_rate_crosstab('root_growth_rate', 'root_growth_rate_crosstab');
CREATE OR REPLACE FUNCTION public.generate_root_growth_rate_crosstab(src_tbl_name text, ct_tbl_name text)
RETURNS text
LANGUAGE plpgsql
AS $function$
DECLARE
    cols_query VARCHAR;
    cols VARCHAR;
    sql_query VARCHAR;
BEGIN  
        cols_query:= 'SELECT '
|| quote_literal('')
|| '

||
array_to_string(

(array
(SELECT DISTINCT time_point FROM '
|| src_tbl_name
|| '
ORDER BY 1)
), '
|| quote_literal('" DOUBLE PRECISION , "timepoint_')


|| ' )
|| '
||
quote_literal('" DOUBLE PRECISION ')
|| ' ;

';
    EXECUTE cols_query INTO cols;
cols:= '"' || 'timepoint_' || cols;
    cols:=  'kernel_id INTEGER, ' || cols;

    sql_query := 'CREATE TABLE ' || ct_tbl_name || ' AS SELECT * FROM crosstab
(' ||
    quote_literal('SELECT kernel_id, time_point, growth_rate FROM ' || src_tbl_name
|| ' ORDER BY 1,2') || ', ' ||
    quote_literal('SELECT DISTINCT time_point FROM ' || src_tbl_name || '
ORDER BY 1') || ') '  || '
    AS x (' || cols || ')' ;

    EXECUTE sql_query;
    RETURN ct_tbl_name;
END;
$function$

-- Crosstab generating function for root_tip_measurements table
-- Invoke as SELECT * FROM generate_root_growth_rate_crosstab('root_tip_measurements', 'root_tip_measurements_crosstab');
CREATE OR REPLACE FUNCTION public.generate_root_tip_crosstab(src_tbl_name text, ct_tbl_name text)
RETURNS text
LANGUAGE plpgsql
AS $function$
DECLARE
    cols_query VARCHAR;
    cols VARCHAR;
    sql_query VARCHAR;
BEGIN  
        cols_query:= 'SELECT '
|| quote_literal('')
|| '

||
array_to_string(

(array
(SELECT DISTINCT time_point FROM '
|| src_tbl_name
|| '
ORDER BY 1)
), '
|| quote_literal('" DOUBLE PRECISION , "timepoint_')


|| ' )
|| '
||
quote_literal('" DOUBLE PRECISION ')
|| ' ;

';
    EXECUTE cols_query INTO cols;
cols:= '"' || 'timepoint_' || cols;
    cols:=  'kernel_id INTEGER, ' || cols;

    sql_query := 'CREATE TABLE ' || ct_tbl_name || ' AS SELECT * FROM crosstab
(' ||
    quote_literal('SELECT kernel_id, time_point, angle FROM ' || src_tbl_name
|| ' ORDER BY 1,2') || ', ' ||
    quote_literal('SELECT DISTINCT time_point FROM ' || src_tbl_name || '
ORDER BY 1') || ') '  || '
    AS x (' || cols || ')' ;

    EXECUTE sql_query;
    RETURN ct_tbl_name;
END;
$function$


/*
    Creates a table containing correlation values betwen every phenotype pairs at population id level
*/
CREATE OR REPLACE FUNCTION correlation_pid_phenotype_pairs_func(stage_table text)
  RETURNS text AS
$BODY$
DECLARE
    pairs_query VARCHAR;    
    corr_coeff_sel_query VARCHAR;
    corr_coeff_ins_query VARCHAR;

    row_data record;
    row_data2 record;
    
    pheno1_id VARCHAR;
    pheno2_id VARCHAR;

    corr_coeff DOUBLE PRECISION;
    stddev1 DOUBLE PRECISION;
    stddev2 DOUBLE PRECISION;
    variance1 DOUBLE PRECISION;
    variance2 DOUBLE PRECISION;
BEGIN
    -- Open a cursor over all the set of phenotype pairs which have correlation enabled.
    pairs_query := 'SELECT p1id, p2id, p1table, p1field, p2table, p2field FROM phenotype_pairs_correlate ORDER BY p1id, p2id';
    --RAISE NOTICE 'Pairs query, %', pairs_query;
    FOR row_data IN EXECUTE pairs_query  LOOP
        corr_coeff_ins_query = '';
        pheno1_id = row_data.p1id;
        pheno2_id = row_data.p2id;
        
        corr_coeff_sel_query := 'SELECT population_lines.id AS population_line_id, ' ||    ' corr('
        || row_data.p1table || '.' || row_data.p1field || ','
        || row_data.p2table || '.' || row_data.p2field
        || ') AS corr_coeff ,' ;
        
        corr_coeff_sel_query := corr_coeff_sel_query || ' (SELECT  stddev( ' ||  row_data.p1table || '.' || row_data.p1field  || ' )) as std1 ,';
        corr_coeff_sel_query := corr_coeff_sel_query || ' (SELECT  stddev( ' ||  row_data.p2table || '.' || row_data.p2field  || ' )) as std2 ,';
        corr_coeff_sel_query := corr_coeff_sel_query || ' (SELECT  variance( ' || row_data.p1table || '.' ||  row_data.p1field  || ' )) as var1 ,';
        corr_coeff_sel_query := corr_coeff_sel_query || ' (SELECT  variance( ' ||  row_data.p2table || '.' || row_data.p2field  || ' )) as var2 ';
        corr_coeff_sel_query := corr_coeff_sel_query || ' FROM ';
        
        -- Optimization : If both table same, don't join them.
        IF ( row_data.p1table = row_data.p2table ) THEN
            corr_coeff_sel_query := corr_coeff_sel_query || row_data.p1table|| ',';
        ELSE
            corr_coeff_sel_query := corr_coeff_sel_query || row_data.p1table || ',' || row_data.p2table|| ',';
        END IF;

        corr_coeff_sel_query := corr_coeff_sel_query || ' population_lines, kernels, kernel_plates WHERE kernels.plate_id = kernel_plates.id AND kernel_plates.population_line_id = population_lines.id ';

        IF ( row_data.p1table = row_data.p2table ) THEN
            corr_coeff_sel_query := corr_coeff_sel_query || 'AND '|| row_data.p1table || '.kernel_id = kernels.id';
        ELSE
            corr_coeff_sel_query := corr_coeff_sel_query || ' AND ' || row_data.p1table || '.kernel_id = ' || row_data.p2table || '.kernel_id AND '
            || row_data.p1table || '.kernel_id = kernels.id' ;
        END IF;

    corr_coeff_sel_query := corr_coeff_sel_query || ' GROUP BY population_lines.id ';
     --RAISE NOTICE 'Correlation selection query, %', corr_coeff_sel_query;
         FOR row_data2 IN EXECUTE corr_coeff_sel_query  LOOP

        corr_coeff = row_data2.corr_coeff;
        stddev1 = row_data2.std1;
        stddev2 = row_data2.std2;
        variance1 = row_data2.var1;
        variance2 = row_data2.var2;
        
        -- Handle exception cases
        IF row_data2.corr_coeff = 'NaN' THEN
            corr_coeff = -999;
        END IF;

        IF row_data2.std1 = 'NaN' THEN
            stddev1 = -999;
        END IF;

        IF row_data2.std2 = 'NaN' THEN
            stddev2 = -999;
        END IF;

        IF row_data2.var1 = 'NaN' THEN
            variance1 = -999;
        END IF;

        IF row_data2.var2 = 'NaN' THEN
            variance2 = -999;
        END IF; 
                
         corr_coeff_ins_query := 'INSERT INTO ' || stage_table || ' (population_line_id, pheno1_id, pheno2_id, corr_coeff, stddev1, stddev2, variance1, variance2) VALUES (';
         corr_coeff_ins_query := corr_coeff_ins_query || row_data2.population_line_id || ', ' || pheno1_id || ', ' || pheno2_id || ' , ' ;
         corr_coeff_ins_query := corr_coeff_ins_query || corr_coeff || ', ' || stddev1 || ' , ' || stddev2 || ' , ' || variance1 || ' , ' || variance2;
         corr_coeff_ins_query := corr_coeff_ins_query || ')'; 
         
         --RAISE NOTICE 'Correlation insertion query, %', corr_coeff_ins_query;
            
         IF corr_coeff_ins_query IS NULL THEN
            --RAISE NOTICE 'Skipped correlation insertion query, %', corr_coeff_ins_query;
            continue;

        END IF;

                    
        
         EXECUTE corr_coeff_ins_query;
        END LOOP; -- end of insertion loop
     END LOOP; -- end of selection loop
RETURN stage_table;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION correlation_pid_phenotype_pairs_func(text)
  OWNER TO maizeuser;


/*
    Creates a table containing correlation values betwen every phenotype pairs at population type level
*/
CREATE OR REPLACE FUNCTION correlation_ptype_phenotype_pairs_func(stage_table text)
  RETURNS text AS
$BODY$
DECLARE
    pairs_query VARCHAR;    
    corr_coeff_sel_query VARCHAR;
    corr_coeff_ins_query VARCHAR;

    row_data record;
    row_data2 record;
    
    pheno1_id VARCHAR;
    pheno2_id VARCHAR;

    pop_type VARCHAR;
    
    corr_coeff DOUBLE PRECISION;
    stddev1 DOUBLE PRECISION;
    stddev2 DOUBLE PRECISION;
    variance1 DOUBLE PRECISION;
    variance2 DOUBLE PRECISION;
BEGIN
    -- Open a cursor over all the set of phenotype pairs which have correlation enabled.
    pairs_query := 'SELECT p1id, p2id, p1table, p1field, p2table, p2field FROM phenotype_pairs_correlate ORDER BY p1id, p2id';
    --RAISE NOTICE 'Pairs query, %', pairs_query;
    FOR row_data IN EXECUTE pairs_query  LOOP
        corr_coeff_ins_query = '';
        pheno1_id = row_data.p1id;
        pheno2_id = row_data.p2id;
        
        corr_coeff_sel_query := 'SELECT population_lines.type AS population_type, ' ||    ' corr('
        || row_data.p1table || '.' || row_data.p1field || ','
        || row_data.p2table || '.' || row_data.p2field
        || ') AS corr_coeff ,' ;
        
        corr_coeff_sel_query := corr_coeff_sel_query || ' (SELECT  stddev( ' ||  row_data.p1table || '.' || row_data.p1field  || ' )) as std1 ,';
        corr_coeff_sel_query := corr_coeff_sel_query || ' (SELECT  stddev( ' ||  row_data.p2table || '.' || row_data.p2field  || ' )) as std2 ,';
        corr_coeff_sel_query := corr_coeff_sel_query || ' (SELECT  variance( ' || row_data.p1table || '.' ||  row_data.p1field  || ' )) as var1 ,';
        corr_coeff_sel_query := corr_coeff_sel_query || ' (SELECT  variance( ' ||  row_data.p2table || '.' || row_data.p2field  || ' )) as var2 ';
        corr_coeff_sel_query := corr_coeff_sel_query || ' FROM ';
        
        -- Optimization : If both table same, don't join them.
        IF ( row_data.p1table = row_data.p2table ) THEN
            corr_coeff_sel_query := corr_coeff_sel_query || row_data.p1table|| ',';
        ELSE
            corr_coeff_sel_query := corr_coeff_sel_query || row_data.p1table || ',' || row_data.p2table|| ',';
        END IF;

        corr_coeff_sel_query := corr_coeff_sel_query || ' population_lines, kernels, kernel_plates WHERE kernels.plate_id = kernel_plates.id AND kernel_plates.population_line_id = population_lines.id ';

        IF ( row_data.p1table = row_data.p2table ) THEN
            corr_coeff_sel_query := corr_coeff_sel_query || 'AND '|| row_data.p1table || '.kernel_id = kernels.id';
        ELSE
            corr_coeff_sel_query := corr_coeff_sel_query || ' AND ' || row_data.p1table || '.kernel_id = ' || row_data.p2table || '.kernel_id AND '
            || row_data.p1table || '.kernel_id = kernels.id' ;
        END IF;

    corr_coeff_sel_query := corr_coeff_sel_query || ' GROUP BY population_lines.type ';
     --RAISE NOTICE 'Correlation selection query, %', corr_coeff_sel_query;
         FOR row_data2 IN EXECUTE corr_coeff_sel_query  LOOP

    pop_type = (row_data2.population_type::text);
    pop_type = '''' || pop_type || '''';
    --RAISE NOTICE 'Population type, %', pop_type ;
    
        corr_coeff = row_data2.corr_coeff;
        stddev1 = row_data2.std1;
        stddev2 = row_data2.std2;
        variance1 = row_data2.var1;
        variance2 = row_data2.var2;
        
        -- Handle exception cases
        IF row_data2.corr_coeff = 'NaN' THEN
            corr_coeff = -999;
        END IF;

        IF row_data2.std1 = 'NaN' THEN
            stddev1 = -999;
        END IF;

        IF row_data2.std2 = 'NaN' THEN
            stddev2 = -999;
        END IF;

        IF row_data2.var1 = 'NaN' THEN
            variance1 = -999;
        END IF;

        IF row_data2.var2 = 'NaN' THEN
            variance2 = -999;
        END IF; 
                
         corr_coeff_ins_query := 'INSERT INTO ' || stage_table || ' (population_type, pheno1_id, pheno2_id, corr_coeff, stddev1, stddev2, variance1, variance2) VALUES (';
         corr_coeff_ins_query := corr_coeff_ins_query || pop_type || ', ' || pheno1_id || ', ' || pheno2_id || ' , ' ;
         corr_coeff_ins_query := corr_coeff_ins_query || corr_coeff || ', ' || stddev1 || ' , ' || stddev2 || ' , ' || variance1 || ' , ' || variance2;
         corr_coeff_ins_query := corr_coeff_ins_query || ')'; 
         
         --RAISE NOTICE 'Correlation insertion query Final, %', corr_coeff_ins_query;
            
         IF corr_coeff_ins_query IS NULL THEN
            --RAISE NOTICE 'Skipped correlation insertion query, %', corr_coeff_ins_query;
            continue;

        END IF;

                    
        
         EXECUTE corr_coeff_ins_query;
        END LOOP; -- end of insertion loop
     END LOOP; -- end of selection loop
RETURN stage_table;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION correlation_ptype_phenotype_pairs_func(text)
  OWNER TO maizeuser;