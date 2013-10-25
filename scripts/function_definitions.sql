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

